<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;

class Despacho extends Component
{
    public $paquetes;

    public function mount()
    {
        // Obtener solo los paquetes con estado RETORNO
        $this->paquetes = Paquete::where('accion', 'RETORNO')->get();
    }

    public function render()
    {
        return view('livewire.despacho', ['paquetes' => $this->paquetes]);
    }
}
