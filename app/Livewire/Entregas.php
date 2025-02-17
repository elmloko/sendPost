<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;

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
        $validatedData = $this->validate([
            'estado' => 'required|in:ENTREGADO,RETORNO',
            'observacion' => 'required_if:estado,RETORNO',
        ]);

        $paquete = Paquete::find($this->selectedPaquete);

        if ($paquete) {
            if ($validatedData['estado'] === 'ENTREGADO') {
                // Actualiza la columna 'accion' a 'ENTREGADO'
                $paquete->update(['accion' => 'ENTREGADO']);
                // Aplica el soft delete
                $paquete->delete();
            } elseif ($validatedData['estado'] === 'RETORNO') {
                // Actualiza el estado a RETORNO y guarda la observación
                $paquete->update([
                    'accion' => 'RETORNO',
                    'observacion' => $validatedData['observacion']
                ]);
            }

            session()->flash('message', 'El paquete se ha dado de baja correctamente.');
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
