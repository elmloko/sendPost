<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;

class Dashboard extends Component
{
    public $dataChart;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // Lista de las 9 capitales de Bolivia
        $capitales = [
            'LA PAZ', 'COCHABAMBA', 'SANTA CRUZ', 'SUCRE',
            'ORURO', 'POTOSÍ', 'TARIJA', 'TRINIDAD', 'COBIJA'
        ];        

        // Inicializar datos con todas las ciudades en 0
        $data = [];
        foreach ($capitales as $ciudad) {
            $data[$ciudad] = [
                'ASIGNADO' => 0,
                'CARTERO' => 0,
                'RETORNO' => 0,
                'INTENTO' => 0,
                'ENTREGADO' => 0,
            ];
        }

        // Obtener datos reales de la base de datos
        $paquetes = Paquete::selectRaw("cuidad, accion, COUNT(*) as cantidad")
            ->whereNotNull('cuidad')
            ->groupBy('cuidad', 'accion')
            ->get();

        foreach ($paquetes as $paquete) {
            $ciudad = $paquete->cuidad;
            $accion = $paquete->accion;

            if (isset($data[$ciudad])) {
                $data[$ciudad][$accion] = $paquete->cantidad;
            }
        }

        // Contar los paquetes ENTREGADOS eliminados (SoftDeletes)
        $entregados = Paquete::onlyTrashed()
            ->where('accion', 'ENTREGADO')
            ->selectRaw("cuidad, COUNT(*) as cantidad")
            ->groupBy('cuidad')
            ->get();

        foreach ($entregados as $entregado) {
            $ciudad = $entregado->cuidad;
            if (isset($data[$ciudad])) {
                $data[$ciudad]['ENTREGADO'] = $entregado->cantidad;
            }
        }

        $this->dataChart = $data;
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
