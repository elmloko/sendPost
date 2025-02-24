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
            // Construcción de la URL específica para cada paquete
            $url = "http://172.65.10.52/api/updatePackage/{$paquete->codigo}";
    
            // Definir la descripción dependiendo del estado seleccionado
            $descripcion = $validatedData['estado'] === 'ENTREGADO'
                ? 'Entrega de paquete con Cartero'
                : 'El Cartero Intento de Entrega por Cartero';
    
            // Definir la observación si aplica
            $observacion = $validatedData['estado'] === 'RETORNO'
                ? $validatedData['observacion']
                : '';
    
            // Convertir el estado para la API (ENTREGADO en DB -> REPARTIDO en API)
            $estadoApi = $validatedData['estado'] === 'ENTREGADO' ? 'REPARTIDO' : $validatedData['estado'];
    
            // Datos a enviar a la API
            $data = [
                "ESTADO" => $estadoApi, // Enviar REPARTIDO a la API si es ENTREGADO localmente
                "action" => $validatedData['estado'], // Ahora mantiene exactamente el estado seleccionado (ENTREGADO o RETORNO)
                "user_id" => 86, // Cambiar dinámicamente si es necesario
                "descripcion" => $descripcion,
                "OBSERVACIONES" => $observacion,
                "usercartero" => Auth::user()->name,
            ];
    
            // Cabeceras de autorización para la API
            $headers = [
                'Authorization' => 'Bearer eZMlItx6mQMNZjxoijEvf7K3pYvGGXMvEHmQcqvtlAPOEAPgyKDVOpyF7JP0ilbK',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ];
    
            try {
                // Realizar la solicitud PUT a la API
                $response = Http::withHeaders($headers)->put($url, $data);
    
                // Verificar si la solicitud fue exitosa
                if ($response->successful()) {
                    Log::info("Paquete {$paquete->codigo} actualizado en la API con éxito.");
    
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
                    Log::error("Error al actualizar paquete {$paquete->codigo} en API: " . $response->body());
                    session()->flash('error', 'Error al actualizar el paquete en la API.');
                }
            } catch (\Exception $e) {
                Log::error("Excepción al conectar con la API para el paquete {$paquete->codigo}: " . $e->getMessage());
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
