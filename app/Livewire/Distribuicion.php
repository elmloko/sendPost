<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\PDF;

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
    
        // Verifica si el paquete ya existe para este usuario
        $existe = Paquete::where('codigo', $this->codigo)
            ->where('user', auth()->user()->name)
            ->first();
        if ($existe) {
            session()->flash('message', 'El paquete con este código ya ha sido registrado.');
            return;
        }
    
        // Configuración del cliente Guzzle (con verificación SSL desactivada)
        $client = new Client(['verify' => false]);
        $data = null;
    
        // Lista de APIs a consultar con sus respectivas opciones
        $apis = [
            [
                'url'     => "http://172.65.10.52:8011/api/admisiones/buscar-por-codigo/{$this->codigo}",
                'options' => [],
            ],
            [
                'url'     => "http://172.65.10.52:8450/api/solicitudes/buscar-por-codigo/{$this->codigo}",
                'options' => [],
            ],
            [
                'url'     => "https://correos.gob.bo:8000/api/prueba/{$this->codigo}",
                'options' => [
                    'headers' => [
                        'Authorization' => 'Bearer eZMlItx6mQMNZjxoijEvf7K3pYvGGXMvEHmQcqvtlAPOEAPgyKDVOpyF7JP0ilbK',
                    ],
                ],
            ],
        ];
    
        // Recorre cada API hasta encontrar datos válidos
        foreach ($apis as $api) {
            try {
                $response = $client->get($api['url'], $api['options']);
                $data = json_decode($response->getBody(), true);
    
                // Se asume que si se recibe el campo 'CODIGO' (o 'codigo') es un paquete válido.
                if ($data && (isset($data['CODIGO']) || isset($data['codigo']))) {
                    break;
                }
            } catch (\Exception $e) {
                // Si falla en una API, continúa con la siguiente
                continue;
            }
        }
    
        // Si después de recorrer las APIs no se encontró el paquete
        if (!$data || (!isset($data['CODIGO']) && !isset($data['codigo']))) {
            session()->flash('error', 'No se encontró el paquete en los servicios externos.');
            return;
        }
    
        // Normaliza los campos, teniendo en cuenta que pueden venir con nombres distintos
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
    
        // Aquí podrías agregar validaciones adicionales, por ejemplo, validar el estado
    
        // Crea el paquete con estado "ASIGNADO"
        Paquete::create([
            'codigo'       => $codigo,
            'destinatario' => $destinatario,
            'estado'       => $estado,
            'cuidad'       => $ciudad,
            'peso'         => isset($peso) ? floatval($peso) : null,
            'accion'       => 'ASIGNADO',
            'user'         => auth()->user()->name,
        ]);
    
        session()->flash('message', 'Paquete encontrado y guardado correctamente.');
    
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

        if ($paquete) {
            $paquete->forceDelete();
            session()->flash('message', 'Paquete eliminado correctamente.');
        } else {
            session()->flash('error', 'No se encontró el paquete o no tienes permiso para eliminarlo.');
        }

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
    
            // Actualizar estado a "CARTERO"
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
