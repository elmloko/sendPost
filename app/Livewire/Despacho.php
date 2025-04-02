<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Exports\DespachoExport;
use Maatwebsite\Excel\Facades\Excel;

class Despacho extends Component
{
    public $paquetes;
    public $codigo = '';
    public $fecha;

    // Método auxiliar para verificar si el usuario tiene rol de Administrador
    protected function isAdmin()
    {
        return auth()->user()->hasAnyRole(['Administrador', 'Encargado']);
    }    

    public function mount()
    {
        if ($this->isAdmin()) {
            // Si es Administrador, se muestran todos los paquetes con estado 'RETORNO'
            $this->paquetes = Paquete::where('accion', 'RETORNO')->get();
        } else {
            // Para otros roles, se muestran solo los paquetes asignados al usuario
            $this->paquetes = Paquete::where('accion', 'RETORNO')
                ->where('user', auth()->user()->name)
                ->get();
        }
    }

    public function devolverAVentanilla($codigo)
    {
        if ($this->isAdmin()) {
            $paquete = Paquete::where('codigo', $codigo)->first();
        } else {
            $paquete = Paquete::where('codigo', $codigo)
                ->where('user', auth()->user()->name)
                ->first();
        }

        if ($paquete) {
            // Definir URLs según el origen del paquete
            $api_urls = [
                'TRACKINGBO' => "http://correos.gob.bo/api/updatePackage/{$codigo}",
                'EMS' => "http://172.65.10.52:8011/api/admisiones/cambiar-estado-ems",
                'GESCON' => "http://172.65.10.52:8450/api/solicitudes/cambiar-estado"
            ];

            // Definir datos para cada API
            $api_data = [
                'TRACKINGBO' => [
                    "ESTADO" => "VENTANILLA",
                    "action" => "DEVUELTO",
                    "user_id" => 86,
                    "descripcion" => "Paquete Devuelto a Oficina Postal Regional.",
                    "OBSERVACIONES" => "",
                    "usercartero" => Auth::user()->name,
                ],
                'EMS' => [
                    "codigo" => $paquete->codigo,
                    "estado" => 3,
                    "observacion_entrega" => "Paquete Devuelto a Oficina Postal Regional.",
                    "usercartero" => Auth::user()->name,
                    "action" => "Devuelto a Ventanilla",
                ],
                'GESCON' => [
                    "guia" => $paquete->codigo,
                    "estado" => 5,
                    "cartero_entrega_id" => Auth::id(),
                    "entrega_observacion" => "Paquete Devuelto a Oficina Postal Regional.",
                    "usercartero" => Auth::user()->name,
                    "action" => "Devuelto a Ventanilla",
                ]
            ];

            // Encabezados comunes
            $headers = [
                'Authorization' => 'Bearer eZMlItx6mQMNZjxoijEvf7K3pYvGGXMvEHmQcqvtlAPOEAPgyKDVOpyF7JP0ilbK',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ];

            try {
                $origen = $paquete->sys; // Obtener el origen desde el campo sys

                if (isset($api_urls[$origen]) && isset($api_data[$origen])) {
                    $response = Http::withHeaders($headers)->put($api_urls[$origen], $api_data[$origen]);

                    if ($response->successful()) {
                        // Actualizar el estado del paquete en la base de datos
                        $paquete->accion = 'INTENTO';
                        $paquete->save();

                        // Registrar el evento en la tabla Eventos
                        Event::create([
                            'action' => 'RETORNO',
                            'descripcion' => 'Paquete devuelto por el cartero a ventanilla',
                            'codigo' => $paquete->codigo,
                            'user_id' => auth()->id(),
                        ]);

                        session()->flash('message', "El paquete {$codigo} fue devuelto a ventanilla exitosamente usando la API {$origen}.");
                    } else {
                        session()->flash('error', "Error al devolver el paquete {$codigo} usando la API {$origen}: " . $response->body());
                    }
                } else {
                    session()->flash('error', "No se pudo identificar la API correspondiente para el paquete {$codigo}.");
                }
            } catch (\Exception $e) {
                Log::error("Error al conectar con la API {$origen} para el paquete {$codigo}: " . $e->getMessage());
                session()->flash('error', "Error al conectar con la API {$origen} para el paquete {$codigo}.");
            }
        } else {
            session()->flash('error', "El paquete con código {$codigo} no fue encontrado.");
        }

        $this->mount(); // Recargar paquetes después de la actualización
    }

    public function devolverACartero($codigo)
    {
        if ($this->isAdmin()) {
            $paquete = Paquete::where('codigo', $codigo)->first();
        } else {
            $paquete = Paquete::where('codigo', $codigo)
                ->where('user', auth()->user()->name)
                ->first();
        }

        if ($paquete) {
            $api_urls = [
                'TRACKINGBO' => "http://correos.gob.bo/api/updatePackage/{$codigo}",
                'EMS' => "http://172.65.10.52:8011/api/admisiones/cambiar-estado-ems",
                'GESCON' => "http://172.65.10.52:8450/api/solicitudes/cambiar-estado"
            ];

            $api_data = [
                'TRACKINGBO' => [
                    "ESTADO" => "CARTERO",
                    "action" => "ESTADO",
                    "user_id" => 86,
                    "descripcion" => "Correcion de Estado para Cartero",
                    "usercartero" => Auth::user()->name,
                ],
                'EMS' => [
                    "codigo" => $paquete->codigo,
                    "estado" => 4,
                    "observacion_entrega" => "",
                    "usercartero" => Auth::user()->name,
                    "action" => "Correcion de Estado para Cartero",
                ],
                'GESCON' => [
                    "guia" => $paquete->codigo,
                    "estado" => 2,
                    "entrega_observacion" => "",
                    "usercartero" => Auth::user()->name,
                    "action" => "Correcion de Estado para Cartero",
                ]
            ];

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
                        $paquete->accion = 'CARTERO';
                        $paquete->save();

                        // Registrar el evento en la tabla Eventos
                        Event::create([
                            'action' => 'CORRECCION',
                            'descripcion' => 'Paquete devuelto a inventario de cartero',
                            'codigo' => $paquete->codigo,
                            'user_id' => auth()->id(),
                        ]);

                        session()->flash('message', "El paquete {$codigo} fue devuelto al cartero exitosamente usando la API {$origen}.");
                    } else {
                        session()->flash('error', "Error al devolver el paquete {$codigo} al cartero usando la API {$origen}: " . $response->body());
                    }
                } else {
                    session()->flash('error', "No se pudo identificar la API correspondiente para el paquete {$codigo}.");
                }
            } catch (\Exception $e) {
                Log::error("Error al conectar con la API {$origen} para el paquete {$codigo}: " . $e->getMessage());
                session()->flash('error', "Error al conectar con la API {$origen} para el paquete {$codigo}.");
            }
        } else {
            session()->flash('error', "El paquete con código {$codigo} no fue encontrado.");
        }

        $this->mount(); // Recargar paquetes después de la actualización
    }

    public function buscar()
    {
        if (empty($this->codigo)) {
            if ($this->isAdmin()) {
                $this->paquetes = Paquete::where('accion', 'RETORNO')->get();
            } else {
                $this->paquetes = Paquete::where('accion', 'RETORNO')
                    ->where('user', auth()->user()->name)
                    ->get();
            }
        } else {
            if ($this->isAdmin()) {
                $this->paquetes = Paquete::where('accion', 'RETORNO')
                    ->where('codigo', 'LIKE', "%{$this->codigo}%")
                    ->get();
            } else {
                $this->paquetes = Paquete::where('accion', 'RETORNO')
                    ->where('codigo', 'LIKE', "%{$this->codigo}%")
                    ->where('user', auth()->user()->name)
                    ->get();
            }
        }
    }

    public function exportarExcel()
    {
        return Excel::download(new DespachoExport($this->fecha), 'paquetes_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        return view('livewire.despacho', ['paquetes' => $this->paquetes]);
    }
}
