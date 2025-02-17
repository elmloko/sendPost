<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;

class Inventario extends Component
{
    public $paquetes;

    public function mount()
    {
        // Obtener solo los paquetes que han sido soft deleted (trashed) y tienen estado ENTREGADO
        $this->paquetes = Paquete::onlyTrashed()
                                ->where('accion', 'ENTREGADO')
                                ->get();
    }

    public function render()
    {
        return view('livewire.inventario', ['paquetes' => $this->paquetes]);
    }
}
