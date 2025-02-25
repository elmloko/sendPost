<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class Inventario extends Component
{
    public $paquetes;

    public function mount()
    {
        // Obtener solo los paquetes eliminados con estado ENTREGADO
        $this->paquetes = Paquete::onlyTrashed()
            ->where('accion', 'ENTREGADO')
            ->get();
    }

    public function darAlta($codigo)
    {
        $paquete = Paquete::onlyTrashed()->where('codigo', $codigo)->first();

        if ($paquete) {
            // Definir las URLs y datos según el sistema de origen
            $api_urls = [
                'TRACKINGBO' => "http://172.65.10.52/api/updatePackage/{$codigo}",
                'EMS' => "http://172.65.10.52:8011/api/admisiones/cambiar-estado-ems",
                'GESCON' => "http://172.65.10.52:8450/api/solicitudes/cambiar-estado"
            ];

            $api_data = [
                'TRACKINGBO' => [
                    "ESTADO" => "CARTERO",
                    "action" => "ESTADO",
                    "user_id" => 86,
                    "descripcion" => "Alta de Paquete para Cartero",
                    "usercartero" => Auth::user()->name,
                ],
                'EMS' => [
                    "codigo" => $paquete->codigo,
                    "estado" => 4, // Estado correspondiente en la API
                    "observacion_entrega" => "",
                    "usercartero" => auth()->user()->name,
                    "action" => "Alta de Paquete para Cartero",
                ],
                'GESCON' => [
                    "guia" => $paquete->codigo,
                    "estado" => 2, // Estado correspondiente en la API
                    "entrega_observacion" => "",
                    "usercartero" => auth()->user()->name,
                    "action" => "Alta de Paquete para Cartero",
                ]
            ];

            // Encabezados comunes
            $headers = [
                'Authorization' => 'Bearer eZMlItx6mQMNZjxoijEvf7K3pYvGGXMvEHmQcqvtlAPOEAPgyKDVOpyF7JP0ilbK',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ];

            try {
                $origen = $paquete->sys;

                if (isset($api_urls[$origen]) && isset($api_data[$origen])) {
                    $response = Http::withHeaders($headers)->put($api_urls[$origen], $api_data[$origen]);

                    if ($response->successful()) {
                        // Restaurar el paquete eliminado
                        $paquete->restore();
                        $paquete->accion = 'CARTERO';
                        $paquete->save();

                        session()->flash('message', "El paquete {$codigo} fue dado de alta exitosamente desde el sistema {$origen}.");
                    } else {
                        session()->flash('error', "Error al dar de alta el paquete {$codigo}: " . $response->body());
                    }
                } else {
                    session()->flash('error', "No se pudo identificar la API correspondiente para el paquete {$codigo}.");
                }
            } catch (\Exception $e) {
                session()->flash('error', "Error al conectar con la API para el paquete {$codigo}: " . $e->getMessage());
            }
        } else {
            session()->flash('error', "El paquete con código {$codigo} no fue encontrado.");
        }

        $this->mount(); // Recargar la lista de paquetes
    }

    public function render()
    {
        return view('livewire.inventario', ['paquetes' => $this->paquetes]);
    }
}
