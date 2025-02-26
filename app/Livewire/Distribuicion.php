<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;
use App\Models\Event;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\PDF;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class Distribuicion extends Component
{
    public $codigo;
    public $paquetes = [];

    public function mount()
    {
        // Cargar solo los paquetes asignados al usuario y con estado "ASIGNADO"
        $this->paquetes = Paquete::where('user', auth()->user()->name)
            ->where('accion', 'ASIGNADO')
            ->get();
    }

    public function buscar()
    {
        $this->validate([
            'codigo' => 'required|string',
        ]);

        // Busca si ya existe un paquete con este código y estado INTENTO
        $paqueteExistente = Paquete::where('codigo', $this->codigo)
            ->where('user', auth()->user()->name)
            ->first();

        // Si el paquete existe y el intento es 3, no se permite registrar más
        if ($paqueteExistente && $paqueteExistente->intento >= 3) {
            session()->flash('error', 'El paquete ha alcanzado el máximo de intentos. Mover a REZAGO');
            return;
        }

        // Configuración del cliente Guzzle
        $client = new Client(['verify' => false]);
        $data = null;

        // Lista de APIs a consultar con sus alias
        $apis = [
            [
                'url' => "http://172.65.10.52:8011/api/admisiones/buscar-por-codigo/{$this->codigo}",
                'alias' => 'EMS',
                'options' => [],
            ],
            [
                'url' => "http://172.65.10.52:8450/api/solicitudes/buscar-por-codigo/{$this->codigo}",
                'alias' => 'GESCON',
                'options' => [],
            ],
            [
                'url' => "https://correos.gob.bo:8000/api/prueba/{$this->codigo}",
                'alias' => 'TRACKINGBO',
                'options' => [
                    'headers' => [
                        'Authorization' => 'Bearer eZMlItx6mQMNZjxoijEvf7K3pYvGGXMvEHmQcqvtlAPOEAPgyKDVOpyF7JP0ilbK',
                    ],
                ],
            ],
        ];

        $sistema_origen = null;

        // Recorre cada API hasta encontrar datos válidos
        foreach ($apis as $api) {
            try {
                $response = $client->get($api['url'], $api['options']);
                $data = json_decode($response->getBody(), true);

                // Si se encuentra el paquete válido, registra de qué sistema proviene
                if ($data && (isset($data['CODIGO']) || isset($data['codigo']))) {
                    $sistema_origen = $api['alias'];
                    break;
                }
            } catch (\Exception $e) {
                continue; // Si falla, pasa a la siguiente API
            }
        }

        // Si no se encontró el paquete en ninguna API
        if (!$data || (!$data['CODIGO'] && !$data['codigo'])) {
            session()->flash('error', 'No se encontró el paquete en los servicios externos.');
            return;
        }

        // Normaliza los campos
        $codigo       = $data['CODIGO']       ?? $data['codigo']       ?? null;
        $destinatario = $data['DESTINATARIO'] ?? $data['destinatario'] ?? null;
        $estado       = $data['ESTADO']       ?? $data['estado']       ?? null;
        $ciudad       = $data['CUIDAD']       ?? $data['ciudad']       ?? null;
        $peso         = $data['PESO']         ?? $data['peso']         ?? null;

        // Valida que la ciudad del paquete coincida con la del usuario autenticado
        if ($ciudad !== auth()->user()->city) {
            session()->flash('error', 'El paquete no se puede registrar porque la ciudad del paquete no coincide con la ciudad del usuario.');
            return;
        }

        // Si el paquete ya existe, actualiza el intento y cambia el estado a ASIGNADO
        if ($paqueteExistente) {
            $paqueteExistente->intento += 1;
            $paqueteExistente->accion = 'ASIGNADO';
            $paqueteExistente->save();
        } else {
            // Si no existe, crea el paquete con intento = 1 y estado ASIGNADO
            Paquete::create([
                'codigo'       => $codigo,
                'destinatario' => $destinatario,
                'estado'       => 'ASIGNADO',
                'cuidad'       => $ciudad,
                'peso'         => isset($peso) ? floatval($peso) : null,
                'accion'       => 'ASIGNADO',
                'user'         => auth()->user()->name,
                'sys'          => $sistema_origen,
                'intento'      => 1, // Primer intento
            ]);
        }

        session()->flash('message', "Paquete encontrado y registrado correctamente desde el sistema: {$sistema_origen}.");

        // Registrar el evento en la tabla Eventos
        Event::create([
            'action' => 'BUSCAR',
            'descripcion' => 'Paquete Identificado por el Cartero',
            'codigo' => $codigo,
            'user_id' => auth()->id(), // Usa el ID del usuario autenticado
        ]);

        // Actualiza la lista de paquetes con estado "ASIGNADO"
        $this->paquetes = Paquete::where('user', auth()->user()->name)
            ->where('accion', 'ASIGNADO')
            ->get();
    }

    public function eliminar($codigo)
    {
        $paquete = Paquete::where('codigo', $codigo)
            ->where('user', auth()->user()->name)
            ->first();

        if (!$paquete) {
            session()->flash('error', 'No se encontró el paquete o no tienes permiso para eliminarlo.');
            return;
        }

        // Verificar si el paquete fue registrado antes (intento > 1)
        if ($paquete->intento > 1) {
            // En lugar de eliminar, cambiar el estado a INTENTO y restar en el campo intento
            $paquete->accion = 'INTENTO';
            $paquete->intento -= 1;
            $paquete->save();

            session()->flash('message', 'El estado del paquete ha sido cambiado a INTENTO y el intento se ha reducido en 1.');
        } else {
            // Si es el primer intento, eliminar definitivamente
            $paquete->forceDelete();
            session()->flash('message', 'Paquete eliminado correctamente.');
        }

        // Registrar el evento en la tabla Eventos
        Event::create([
            'action' => 'RETIRO',
            'descripcion' => 'Paquete Apartado por el Cartero',
            'codigo' => $codigo,
            'user_id' => auth()->id(), // Usa el ID del usuario autenticado
        ]);

        // Solo mostrar paquetes con estado "ASIGNADO"
        $this->paquetes = Paquete::where('user', auth()->user()->name)
            ->where('accion', 'ASIGNADO')
            ->get();
    }

    public function asignarACartero()
    {
        // Obtener paquetes con estado "ASIGNADO" antes de actualizarlos
        $paquetes = Paquete::where('user', auth()->user()->name)
            ->where('accion', 'ASIGNADO')
            ->get();

        // Si no hay paquetes asignados, generar un PDF en blanco
        if ($paquetes->isEmpty()) {
            $pdf = PDF::loadView('cartero.pdf.asignar', ['packages' => []]);
        } else {
            // Generar el PDF con los paquetes antes de actualizarlos
            $pdf = PDF::loadView('cartero.pdf.asignar', ['packages' => $paquetes]);

            foreach ($paquetes as $paquete) {
                // Registrar el evento en la tabla Eventos
                Event::create([
                    'action' => 'ASIGNADO',
                    'descripcion' => 'Paquete asignado por el Cartero para distribuicion',
                    'codigo' => $paquete->codigo,
                    'user_id' => auth()->id(), // Usa el ID del usuario autenticado
                ]);
                
                // Definir URLs según el origen del paquete
                $api_urls = [
                    'TRACKINGBO' => "http://172.65.10.52/api/updatePackage/{$paquete->codigo}",
                    'EMS' => "http://172.65.10.52:8011/api/admisiones/cambiar-estado-ems",
                    'GESCON' => "http://172.65.10.52:8450/api/solicitudes/cambiar-estado"
                ];

                // Definir datos según el origen
                $api_data = [
                    'TRACKINGBO' => [
                        "ESTADO" => "CARTERO",
                        "action" => "EN TRASCURSO",
                        "user_id" => 86, // ID del usuario, puede ser dinámico si necesario
                        "descripcion" => "Paquete Destinado por envío con Cartero de estado",
                        "usercartero" => auth()->user()->name,
                    ],
                    'EMS' => [
                        "codigo" => $paquete->codigo,
                        "estado" => 4, // Estado correspondiente en la API
                        "observacion_entrega" => "",
                        "usercartero" => auth()->user()->name,
                        "action" => "Asignar Cartero",
                    ],
                    'GESCON' => [
                        "guia" => $paquete->codigo,
                        "estado" => 2, // Estado correspondiente en la API
                        "entrega_observacion" => "",
                        "usercartero" => auth()->user()->name,
                        "action" => "Envio en Camino",
                    ]
                ];

                // Encabezados comunes
                $headers = [
                    'Authorization' => 'Bearer eZMlItx6mQMNZjxoijEvf7K3pYvGGXMvEHmQcqvtlAPOEAPgyKDVOpyF7JP0ilbK',
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ];

                try {
                    $origen = $paquete->sys; // Obtener el sistema desde el campo sys

                    // Si el origen es válido, realiza la solicitud directamente
                    if (isset($api_urls[$origen]) && isset($api_data[$origen])) {
                        $response = Http::withHeaders($headers)->put($api_urls[$origen], $api_data[$origen]);

                        // Verificar la respuesta de la API
                        if ($response->successful()) {
                            Log::info("Paquete {$paquete->codigo} actualizado exitosamente en la API: {$origen}.");
                        } else {
                            Log::error("Error al actualizar el paquete {$paquete->codigo} en la API {$origen}: " . $response->body());
                        }
                    } else {
                        Log::error("Origen no válido para el paquete {$paquete->codigo}: {$origen}");
                    }
                } catch (\Exception $e) {
                    Log::error("Error al conectar con la API {$origen} para el paquete {$paquete->codigo}: " . $e->getMessage());
                }
            }

            // Actualizar estado a "CARTERO" en la base de datos
            Paquete::where('user', auth()->user()->name)
                ->where('accion', 'ASIGNADO')
                ->update(['accion' => 'CARTERO']);

            session()->flash('message', "Se actualizaron {$paquetes->count()} paquetes a 'CARTERO'.");
        }

        // Emitir un evento en el navegador para recargar la página
        $this->dispatch('pdf-descargado');

        // Descargar el PDF generado
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'ReporteEntrega.pdf');
    }

    public function render()
    {
        return view('livewire.distribuicion');
    }
}
