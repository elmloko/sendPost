<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;

class Entregas extends Component
{
    public $codigo = '';  // Campo para la búsqueda
    public $paquetes = [];

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

    public function render()
    {
        return view('livewire.entregas');
    }
}
