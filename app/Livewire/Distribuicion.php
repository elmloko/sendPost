<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class Distribuicion extends Component
{
    public $codigo;
    public $paquetes = [];

    public function mount()
    {
        // Solo carga los paquetes asignados al usuario autenticado
        $this->paquetes = Paquete::where('user', auth()->user()->name)->get();
    }

    public function buscar()
    {
        $this->validate([
            'codigo' => 'required|string',
        ]);

        // Verifica si el paquete ya existe para este usuario
        $existe = Paquete::where('codigo', $this->codigo)
            ->where('user', auth()->user()->name)
            ->first();
        if ($existe) {
            session()->flash('message', 'El paquete con este código ya ha sido registrado.');
            return;
        }

        $client = new Client();

        try {
            $response = $client->get("https://correos.gob.bo:8000/api/prueba/{$this->codigo}", [
                'headers' => [
                    'Authorization' => 'Bearer eZMlItx6mQMNZjxoijEvf7K3pYvGGXMvEHmQcqvtlAPOEAPgyKDVOpyF7JP0ilbK',
                ],
                'verify' => false, // Desactiva la verificación SSL
            ]);

            $data = json_decode($response->getBody(), true);

            // Valida que el estado devuelto sea VENTANILLA o LISTO
            if (!in_array($data['ESTADO'], ['VENTANILLA', 'LISTO'])) {
                session()->flash('error', 'El paquete no se puede registrar porque su estado no es válido (debe ser VENTANILLA o LISTO).');
                return;
            }

            // Valida que la ciudad del paquete (CUIDAD) coincida con la ciudad del usuario autenticado (users->city)
            if ($data['CUIDAD'] !== auth()->user()->city) {
                session()->flash('error', 'El paquete no se puede registrar porque la ciudad del paquete no coincide con la ciudad del usuario.');
                return;
            }

            // Crea el paquete guardando el nombre del usuario autenticado
            Paquete::create([
                'codigo'       => $data['CODIGO'],
                'destinatario' => $data['DESTINATARIO'] ?? null,
                'estado'       => $data['ESTADO'] ?? null,
                'cuidad'       => $data['CUIDAD'] ?? null,
                'peso'         => isset($data['PESO']) ? floatval($data['PESO']) : null,
                'accion'       => 'ASIGNADO',
                'user'         => auth()->user()->name,
            ]);

            session()->flash('message', 'Paquete encontrado y guardado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al buscar el paquete: ' . $e->getMessage());
        }

        // Actualiza la lista de paquetes asignados al usuario autenticado
        $this->paquetes = Paquete::where('user', auth()->user()->name)->get();
    }

    public function eliminar($codigo)
    {
        $paquete = Paquete::where('codigo', $codigo)
            ->where('user', auth()->user()->name)
            ->first();

        if ($paquete) {
            $paquete->forceDelete();
            session()->flash('message', 'Paquete eliminado correctamente.');
        } else {
            session()->flash('error', 'No se encontró el paquete o no tienes permiso para eliminarlo.');
        }

        // Recargar la lista de paquetes
        $this->paquetes = Paquete::where('user', auth()->user()->name)->get();
    }
    public function render()
    {
        return view('livewire.distribuicion');
    }
}
