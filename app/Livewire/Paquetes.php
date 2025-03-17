<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Paquete;

class Paquetes extends Component
{
    use WithPagination;

    public $search = '';
    public $input_search = '';

    protected $updatesQueryString = ['search'];

    public function buscar()
    {
        $this->search = $this->input_search;
        $this->resetPage();
    }

    public function render()
    {
        $paquetes = Paquete::withTrashed()
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                      ->orWhere('destinatario', 'like', '%' . $this->search . '%')
                      ->orWhere('estado', 'like', '%' . $this->search . '%')
                      ->orWhere('cuidad', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('id')
            ->paginate(100);

        return view('livewire.paquetes', compact('paquetes'));
    }
}
