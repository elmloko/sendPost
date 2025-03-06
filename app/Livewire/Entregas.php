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
        // Validaciones. Descomenta la regla de 'firma' si quieres que sea obligatoria al ENTREGAR.
        // Igualmente, si deseas que 'photo' sea obligatoria al ENTREGAR, cámbiala a: 
        // 'photo' => 'required_if:estado,ENTREGADO|image|max:10240'
        $this->validate([
            'estado'      => 'required|in:ENTREGADO,RETORNO',
            'observacion' => 'required_if:estado,RETORNO',
            'photo'       => 'nullable|image|max:10240',
            'firma'       => 'required_if:estado,ENTREGADO',  // <--- IMPORTANTE si quieres forzar la firma
        ]);
    
        // Para debug, ve qué contenido tienes en la firma y en la foto:
        Log::info('Contenido de la firma al guardar: ' . substr($this->firma, 0, 100) . '...');
        Log::info('Estado seleccionado: ' . $this->estado);
    
        $paquete = Paquete::find($this->selectedPaquete);
        if (! $paquete) {
            session()->flash('error', 'Paquete no encontrado.');
            $this->closeModal();
            $this->dispatch('reloadPage');
            return;
        }
    
        // Convertir la foto subida a base64 si existe:
        $photoBase64 = null;
        if ($this->photo) {
            // Contenido binario de la imagen
            $imageContents = file_get_contents($this->photo->getRealPath());
            // Convertimos a base64
            $base64    = base64_encode($imageContents);
            $extension = $this->photo->extension();  // e.g. 'png', 'jpg', etc.
    
            // Construimos la cadena con mime-type
            $photoBase64 = 'data:image/' . $extension . ';base64,' . $base64;
            // Log para debug
            Log::info('Imagen convertida a base64: ' . substr($photoBase64, 0, 100) . '...');
        }
    
        // URLs de APIs según el origen
        $api_urls = [
            'TRACKINGBO' => "http://172.65.10.52/api/updatePackage/{$paquete->codigo}",
            'EMS'        => "http://172.65.10.52:8011/api/admisiones/cambiar-estado-ems",
            'GESCON'     => "http://172.65.10.52:8450/api/solicitud/actualizar-estado",
        ];
    
        // Data que se va a enviar dependiendo de sys/origen:
        $api_data = [
            'TRACKINGBO' => [
                "ESTADO"        => $this->estado === 'ENTREGADO' ? 'REPARTIDO' : $this->estado,
                "action"        => $this->estado,
                "user_id"       => 86,
                "descripcion"   => ($this->estado === 'ENTREGADO')
                    ? 'Entrega de paquete con Cartero'
                    : 'El Cartero Intento de Entrega por Cartero',
                "OBSERVACIONES" => ($this->estado === 'RETORNO')
                    ? $this->observacion
                    : '',
                "usercartero"   => Auth::user()->name,
            ],
            'EMS' => [
                "codigo"       => $paquete->codigo,
                "estado"       => $this->estado === 'ENTREGADO' ? 5 : 10,
                "usercartero"  => Auth::user()->name,
                "action"       => ($this->estado === 'ENTREGADO') ? "Entregar Envio" : "Return",
            ],
            'GESCON' => [
                "guia"                => $paquete->codigo,
                "estado"             => ($this->estado === 'ENTREGADO') ? 3 : 7,
                "firma_d"            => $this->firma,
                "entrega_observacion" => ($this->estado === 'ENTREGADO')
                    ? $this->observacion_entrega
                    : $this->observacion,
                "imagen"             => $photoBase64,
            ],
        ];
    
        // Cabeceras para la llamada HTTP
        $headers = [
            'Authorization' => 'Bearer eZMlItx6mQMNZjxoijEvf7K3pYvGGXMvEHmQcqvtlAPOEAPgyKDVOpyF7JP0ilbK',
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    
        try {
            $origen = $paquete->sys;
    
            // Verificamos si existe config de la API para este origen
            if (isset($api_urls[$origen]) && isset($api_data[$origen])) {
                // 1) Llamada a la API principal
                $response = Http::withHeaders($headers)->put($api_urls[$origen], $api_data[$origen]);
    
                if ($response->successful()) {
                    Log::info("Paquete {$paquete->codigo} actualizado en la API {$origen} con éxito.");
    
                    // 2) Llamada extra para TRACKINGBO: imágenes/firma
                    if ($origen === 'TRACKINGBO') {
                        try {
                            $imagenesData = [
                                "codigo" => $paquete->codigo,
                                "foto"   => $photoBase64,  // data:image/...;base64,...
                                "firma"  => $this->firma,  // data:image/...;base64,...
                            ];
    
                            // Llamada POST (verifica que el endpoint acepte POST y JSON)
                            $imagenesResponse = Http::withHeaders($headers)
                                ->put('http://172.65.10.52/api/actualizar-imagenes', $imagenesData);
    
                            if ($imagenesResponse->successful()) {
                                Log::info("Imagen(es) para paquete {$paquete->codigo} actualizadas en TRACKINGBO con éxito.");
                            } else {
                                Log::error("Error al actualizar imágenes en TRACKINGBO: " . $imagenesResponse->body());
                            }
                        } catch (\Exception $e) {
                            Log::error("Excepción al actualizar imágenes en TRACKINGBO: " . $e->getMessage());
                        }
                    }
    
                    // Actualizaciones locales según el estado
                    if ($this->estado === 'ENTREGADO') {
                        $paquete->update([
                            'accion'               => 'ENTREGADO',
                            'firma'                => $this->firma,
                            'observacion_entrega'  => $this->observacion_entrega,
                            'photo'                => $photoBase64,  // guardamos imagen base64 en la BD
                        ]);
    
                        // Borramos el registro de la tabla principal si así lo requieres
                        $paquete->delete();
    
                        Event::create([
                            'action'      => 'ENTREGADO',
                            'descripcion' => 'Paquete entregado por el cartero',
                            'codigo'      => $paquete->codigo,
                            'user_id'     => auth()->id(),
                        ]);
    
                        // Llamada a la API local /entregar-envio
                        try {
                            $datosLocal = [
                                "codigo"              => $paquete->codigo,
                                "estado"              => 5,
                                "firma_entrega"       => $this->firma,
                                "observacion_entrega" => $this->observacion_entrega,
                                "photo"               => $photoBase64,
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
                    } elseif ($this->estado === 'RETORNO') {
                        $paquete->update([
                            'accion'      => 'RETORNO',
                            'observacion' => $this->observacion,
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
        $this->dispatch('reloadPage');  // fuerza la recarga
    }
    
    

    public function render()
    {
        return view('livewire.entregas');
    }
}
