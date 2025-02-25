<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class Entregas extends Component
{
    public $codigo = '';  // Campo para la búsqueda
    public $paquetes = [];

    // Variables para el modal
    public $showModal = false;
    public $selectedPaquete;
    public $estado;
    public $observacion;

    public function mount()
    {
        // Solo mostrar paquetes con estado "CARTERO"
        $this->paquetes = Paquete::where('accion', 'CARTERO')->get();
    }

    public function buscar()
    {
        $this->validate([
            'codigo' => 'nullable|string',
        ]);

        // Filtrar los paquetes con estado "CARTERO" y opcionalmente por código
        $this->paquetes = Paquete::where('accion', 'CARTERO')
            ->when($this->codigo, function ($query) {
                $query->where('codigo', 'like', "%{$this->codigo}%");
            })
            ->get();
    }

    // Método para abrir el modal y asignar el paquete seleccionado
    public function openModal($id)
    {
        $this->selectedPaquete = $id;
        $this->estado = '';
        $this->observacion = '';
        $this->showModal = true;
    }

    // Método para cerrar el modal
    public function closeModal()
    {
        $this->showModal = false;
    }

    // Método que procesa la acción de dar de baja
    public function darDeBaja()
    {
        // Validar datos de entrada
        $validatedData = $this->validate([
            'estado' => 'required|in:ENTREGADO,RETORNO',
            'observacion' => 'required_if:estado,RETORNO',
        ]);

        $paquete = Paquete::find($this->selectedPaquete);

        if ($paquete) {
            // Definir las URLs de las APIs según el origen del paquete
            $api_urls = [
                'TRACKINGBO' => "http://172.65.10.52/api/updatePackage/{$paquete->codigo}",
                'EMS' => "http://172.65.10.52:8011/api/admisiones/cambiar-estado-ems",
                'GESCON' => "http://172.65.10.52:8450/api/solicitudes/cambiar-estado"
            ];

            // Definir datos específicos según el origen
            $api_data = [
                'TRACKINGBO' => [
                    "ESTADO" => $validatedData['estado'] === 'ENTREGADO' ? 'REPARTIDO' : $validatedData['estado'],
                    "action" => $validatedData['estado'],
                    "user_id" => 86, // Cambiar dinámicamente si es necesario
                    "descripcion" => $validatedData['estado'] === 'ENTREGADO' ? 'Entrega de paquete con Cartero' : 'El Cartero Intento de Entrega por Cartero',
                    "OBSERVACIONES" => $validatedData['estado'] === 'RETORNO' ? $validatedData['observacion'] : '',
                    "usercartero" => Auth::user()->name,
                ],
                'EMS' => [
                    "codigo" => $paquete->codigo,
                    "estado" => $validatedData['estado'] === 'ENTREGADO' ? 5 : 10, // Estado correspondiente en la API
                    "observacion_entrega" => $validatedData['estado'] === 'RETORNO' ? $validatedData['observacion'] : '',
                    "usercartero" => Auth::user()->name,
                    "action" => $validatedData['estado'] === 'ENTREGADO' ? "Entregar Envio" : "Return",
                ],
                'GESCON' => [
                    "guia" => $paquete->codigo,
                    "estado" => $validatedData['estado'] === 'ENTREGADO' ? 3 : 7, // Estado correspondiente en la API
                    "entrega_observacion" => $validatedData['estado'] === 'RETORNO' ? $validatedData['observacion'] : '',
                    "usercartero" => Auth::user()->name,
                    "action" => $validatedData['estado'] === 'ENTREGADO' ? "Entregado" : "Rechazado",
                ]
            ];

            // Cabeceras de autorización para la API
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

                    // Verificar si la solicitud fue exitosa
                    if ($response->successful()) {
                        Log::info("Paquete {$paquete->codigo} actualizado en la API {$origen} con éxito.");

                        // Actualizar el estado local solo si la API responde correctamente
                        if ($validatedData['estado'] === 'ENTREGADO') {
                            $paquete->update(['accion' => 'ENTREGADO']); // Guardar como ENTREGADO en la DB
                            $paquete->delete(); // Soft delete
                        } elseif ($validatedData['estado'] === 'RETORNO') {
                            $paquete->update([
                                'accion' => 'RETORNO',
                                'observacion' => $validatedData['observacion'],
                            ]);
                        }

                        session()->flash('message', 'El paquete se ha dado de baja correctamente.');
                    } else {
                        Log::error("Error al actualizar paquete {$paquete->codigo} en la API {$origen}: " . $response->body());
                        session()->flash('error', 'Error al actualizar el paquete en la API.');
                    }
                } else {
                    Log::error("Origen no válido para el paquete {$paquete->codigo}: {$origen}");
                    session()->flash('error', 'El sistema de origen no es válido.');
                }
            } catch (\Exception $e) {
                Log::error("Excepción al conectar con la API {$origen} para el paquete {$paquete->codigo}: " . $e->getMessage());
                session()->flash('error', 'Error de conexión con la API.');
            }
        } else {
            session()->flash('error', 'Paquete no encontrado.');
        }

        $this->closeModal();
        $this->dispatch('reloadPage');
    }

    public function render()
    {
        return view('livewire.entregas');
    }
}
