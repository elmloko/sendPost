<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Despacho extends Component
{
    public $paquetes;

    public function mount()
    {
        $this->paquetes = Paquete::where('accion', 'RETORNO')->get();
    }

    public function devolverAVentanilla($codigo)
    {
        $paquete = Paquete::where('codigo', $codigo)->first();

        if ($paquete) {
            $url = "http://172.65.10.52/api/updatePackage/{$codigo}";

            $data = [
                "ESTADO" => "VENTANILLA",
                "action" => "DEVUELTO",
                "user_id" => 86, // Puede ser dinámico si lo necesitas
                "descripcion" => "Paquete Devuelto a Oficina Postal Regional.",
                "OBSERVACIONES" => "",
                "usercartero" => Auth::user()->name,
            ];

            $headers = [
                'Authorization' => 'Bearer eZMlItx6mQMNZjxoijEvf7K3pYvGGXMvEHmQcqvtlAPOEAPgyKDVOpyF7JP0ilbK',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ];

            try {
                $response = Http::withHeaders($headers)->put($url, $data);

                if ($response->successful()) {
                    $paquete->accion = 'INTENTO';
                    $paquete->save();

                    session()->flash('message', "El paquete {$codigo} fue devuelto a ventanilla exitosamente.");
                } else {
                    session()->flash('error', "Error al devolver el paquete {$codigo}: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Error al conectar con la API para el paquete {$codigo}: " . $e->getMessage());
                session()->flash('error', "Error al conectar con la API para el paquete {$codigo}.");
            }
        } else {
            session()->flash('error', "El paquete con código {$codigo} no fue encontrado.");
        }

        $this->mount(); // Recargar paquetes después de la actualización
    }

    public function render()
    {
        return view('livewire.despacho', ['paquetes' => $this->paquetes]);
    }
}
