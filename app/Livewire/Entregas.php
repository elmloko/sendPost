<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paquete;
use App\Models\Event;
use Livewire\WithFileUploads;          // <-- Importante para subir archivos
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class Entregas extends Component
{
    use WithFileUploads;               // <-- Necesario para subir archivos

    public $codigo = '';      // Campo para la búsqueda
    public $paquetes = [];

    // Variables para el modal
    public $showModal = false;
    public $selectedPaquete;
    public $estado;
    public $observacion;
    public $observacion_entrega;

    // Campo para capturar la firma en base64
    public $firma;

    // Nuevo campo para almacenar la imagen (archivo) que se sube
    public $photo;

    // Reglas de validación
    protected $rules = [
        'estado'      => 'required|in:ENTREGADO,RETORNO',
        'observacion' => 'required_if:estado,RETORNO',
        'photo'       => 'nullable|image|max:10240', // hasta 10 MB
        // 'firma'    => 'required_if:estado,ENTREGADO', // Descomenta si la firma es obligatoria al ENTREGAR
    ];

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
        $this->selectedPaquete   = $id;
        $this->estado            = '';
        $this->observacion       = '';
        $this->observacion_entrega = '';
        $this->firma             = '';   // Limpiar la firma
        $this->photo             = null; // Limpiar la imagen

        // Dispara un evento de JS para que se inicie la firma justo al abrir el modal
        $this->dispatch('initFirma');

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
        // Validar datos de entrada
        $validatedData = $this->validate();

        Log::info('Contenido de la firma al guardar: ' . $this->firma);

        $paquete = Paquete::find($this->selectedPaquete);
        if (! $paquete) {
            session()->flash('error', 'Paquete no encontrado.');
            $this->closeModal();
            $this->dispatch('reloadPage');
            return;
        }

        // Procesar la imagen si se subió
        $photoBase64 = null;
        if ($this->photo) {
            // Tomamos el contenido binario
            $imageContents = file_get_contents($this->photo->getRealPath());
            // Lo convertimos a base64
            $base64     = base64_encode($imageContents);
            $extension  = $this->photo->extension();
            // Creamos la cadena con el mime-type
            $photoBase64 = 'data:image/' . $extension . ';base64,' . $base64;
        }

        // Definir las URLs de las APIs según el origen del paquete
        $api_urls = [
            'TRACKINGBO' => "http://172.65.10.52/api/updatePackage/{$paquete->codigo}",
            'EMS'        => "http://172.65.10.52:8011/api/admisiones/cambiar-estado-ems",
            // AQUÍ se reemplaza la ruta anterior de GESCON:
            'GESCON'     => "http://172.65.10.52:8450/api/solicitud/actualizar-estado",
        ];

        // Definir la data específica según el origen
        $api_data = [
            'TRACKINGBO' => [
                "ESTADO"        => $validatedData['estado'] === 'ENTREGADO' ? 'REPARTIDO' : $validatedData['estado'],
                "action"        => $validatedData['estado'],
                "user_id"       => 86,
                "descripcion"   => ($validatedData['estado'] === 'ENTREGADO')
                    ? 'Entrega de paquete con Cartero'
                    : 'El Cartero Intento de Entrega por Cartero',
                "OBSERVACIONES" => ($validatedData['estado'] === 'RETORNO')
                    ? $validatedData['observacion']
                    : '',
                "usercartero"   => Auth::user()->name,
            ],
            'EMS' => [
                "codigo"       => $paquete->codigo,
                "estado"       => $validatedData['estado'] === 'ENTREGADO' ? 5 : 10,
                "usercartero"  => Auth::user()->name,
                "action"       => ($validatedData['estado'] === 'ENTREGADO')
                    ? "Entregar Envio"
                    : "Return",
            ],
            // AQUÍ preparamos la data para la NUEVA ruta de GESCON
            'GESCON' => [
                "guia"               => $paquete->codigo,
                "estado"             => ($validatedData['estado'] === 'ENTREGADO') ? 3 : 7,
                "firma_d"            => $this->firma, // Puedes modificarlo si prefieres otro valor fijo
                // Si está ENTREGADO, se toma 'observacion_entrega'; si RETORNO, la 'observacion' normal
                "entrega_observacion" => ($validatedData['estado'] === 'ENTREGADO')
                    ? $this->observacion_entrega
                    : $validatedData['observacion'],
                "imagen"            => $photoBase64,
            ],
        ];

        // Cabeceras de autorización para las APIs
        $headers = [
            'Authorization' => 'Bearer eZMlItx6mQMNZjxoijEvf7K3pYvGGXMvEHmQcqvtlAPOEAPgyKDVOpyF7JP0ilbK',
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];

        try {
            $origen = $paquete->sys;

            // Verificamos si el origen es válido
            if (isset($api_urls[$origen]) && isset($api_data[$origen])) {
                $response = Http::withHeaders($headers)->put($api_urls[$origen], $api_data[$origen]);

                if ($response->successful()) {
                    Log::info("Paquete {$paquete->codigo} actualizado en la API {$origen} con éxito.");

                    // Actualizaciones locales según el estado
                    if ($validatedData['estado'] === 'ENTREGADO') {
                        $paquete->update([
                            'accion'               => 'ENTREGADO',
                            'firma'                => $this->firma,
                            'observacion_entrega'  => $this->observacion_entrega,
                            'photo'                => $photoBase64, // Guardamos la imagen en la BD
                        ]);

                        // Borramos el registro si así se requiere
                        $paquete->delete();

                        Event::create([
                            'action'      => 'ENTREGADO',
                            'descripcion' => 'Paquete entregado por el cartero',
                            'codigo'      => $paquete->codigo,
                            'user_id'     => auth()->id(),
                        ]);

                        // **PUT A API LOCAL: `entregar-envio`**
                        try {
                            $datosLocal = [
                                "codigo"             => $paquete->codigo,
                                "estado"             => 5,
                                "firma_entrega"      => $this->firma,
                                "observacion_entrega" => $this->observacion_entrega,
                                "photo"              => $photoBase64,
                            ];

                            $headersLocal = [
                                'Accept'       => 'application/json',
                                'Content-Type' => 'application/json',
                            ];

                            $responseLocal = Http::withHeaders($headersLocal)
                                ->put('http://172.65.10.52:8011/api/entregar-envio', $datosLocal);

                            if ($responseLocal->successful()) {
                                Log::info("Paquete {$paquete->codigo} actualizado en API local `/entregar-envio` con éxito.");
                            } else {
                                Log::error("Error en `/entregar-envio`: " . $responseLocal->body());
                                session()->flash('error', 'Error en la API local de entrega.');
                            }
                        } catch (\Exception $e) {
                            Log::error("Excepción en `/entregar-envio`: " . $e->getMessage());
                            session()->flash('error', 'Error de conexión con la API local.');
                        }
                    } elseif ($validatedData['estado'] === 'RETORNO') {
                        $paquete->update([
                            'accion'      => 'RETORNO',
                            'observacion' => $validatedData['observacion'],
                            'firma'       => $this->firma,
                            'photo'       => $photoBase64,
                        ]);

                        Event::create([
                            'action'      => 'RETORNO',
                            'descripcion' => 'Paquete con retorno a ventanilla',
                            'codigo'      => $paquete->codigo,
                            'user_id'     => auth()->id(),
                        ]);
                    }

                    session()->flash('message', 'El paquete se ha dado de baja correctamente.');
                } else {
                    Log::error("Error al actualizar paquete {$paquete->codigo} en la API {$origen}: " . $response->body());
                    session()->flash('error', 'Error al actualizar el paquete en la API.');
                }
            } else {
                Log::error("Origen no válido para el paquete {$paquete->codigo}: {$origen}");
                session()->flash('error', 'El sistema de origen no es válido.');
            }
        } catch (\Exception $e) {
            Log::error("Excepción en API {$origen} para el paquete {$paquete->codigo}: " . $e->getMessage());
            session()->flash('error', 'Error de conexión con la API.');
        }

        $this->closeModal();
        // Forzar recarga de la página (si así lo deseas)
        $this->dispatch('reloadPage');
    }


    public function render()
    {
        return view('livewire.entregas');
    }
}
