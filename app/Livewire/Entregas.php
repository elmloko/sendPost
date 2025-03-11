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
        // Si quieres que la firma y foto sean obligatorias también para RETORNO, 
        // cambia las reglas a 'required_if:estado,ENTREGADO,RETORNO', etc.
        $this->validate([
            'estado'      => 'required|in:ENTREGADO,RETORNO',
            'observacion' => 'required_if:estado,RETORNO',  // ← OBLIGATORIO en RETORNO
            'photo'       => 'nullable|image|max:10240',
            'firma'       => 'required_if:estado,ENTREGADO', // ← OBLIGATORIO en ENTREGADO
        ]);
    
        Log::info('Contenido de la firma al guardar: ' . substr($this->firma, 0, 100) . '...');
        Log::info('Estado seleccionado: ' . $this->estado);
    
        $paquete = Paquete::find($this->selectedPaquete);
        if (! $paquete) {
            session()->flash('error', 'Paquete no encontrado.');
            $this->closeModal();
            $this->dispatch('reloadPage');
            return;
        }
    
        // Convertir imagen a base64 si se subió
        $photoBase64 = null;
        if ($this->photo) {
            $imageContents = file_get_contents($this->photo->getRealPath());
            $base64        = base64_encode($imageContents);
            $extension     = $this->photo->extension(); // 'png','jpg', etc.
            $photoBase64   = 'data:image/' . $extension . ';base64,' . $base64;
    
            Log::info('Imagen convertida a base64: ' . substr($photoBase64, 0, 100) . '...');
        }
    
        // APIs externas según sys
        $api_urls = [
            'TRACKINGBO' => "http://172.65.10.52/api/updatePackage/{$paquete->codigo}",
            'EMS'        => "http://172.65.10.52:8011/api/admisiones/cambiar-estado-ems",
            'GESCON'     => "http://172.65.10.52:8450/api/solicitud/actualizar-estado",
        ];
    
        // Data según origen
        $api_data = [
            'TRACKINGBO' => [
                "ESTADO"        => $this->estado === 'ENTREGADO' ? 'REPARTIDO' : $this->estado,
                "action"        => $this->estado,
                "user_id"       => 86,
                "descripcion"   => ($this->estado === 'ENTREGADO')
                    ? 'Entrega de paquete con Cartero'
                    : 'El Cartero Intento de Entrega por Cartero',
                "OBSERVACIONES" => ($this->estado === 'RETORNO') ? $this->observacion : '',
                "usercartero"   => Auth::user()->name,
            ],
            'EMS' => [
                "codigo"      => $paquete->codigo,
                "estado"      => ($this->estado === 'ENTREGADO') ? 5 : 10,
                "usercartero" => Auth::user()->name,
                "action"      => ($this->estado === 'ENTREGADO') ? "Entregar Envío" : "Return",
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
    
        $headers = [
            'Authorization' => 'Bearer eZMlItx6mQMNZjxoijEvf7K3pYvGGXMvEHmQcqvtlAPOEAPgyKDVOpyF7JP0ilbK',
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    
        try {
            $origen = $paquete->sys;
    
            if (isset($api_urls[$origen]) && isset($api_data[$origen])) {
    
                // --- 1) PUT a la API principal ---
                $response = Http::withHeaders($headers)
                    ->put($api_urls[$origen], $api_data[$origen]);
    
                if ($response->successful()) {
                    Log::info("Paquete {$paquete->codigo} actualizado en la API {$origen} con éxito.");
    
                    // --- 2) Subir imágenes/firma a TRACKINGBO (si aplica) ---
                    if ($origen === 'TRACKINGBO') {
                        try {
                            $imagenesData = [
                                "codigo" => $paquete->codigo,
                                "foto"   => $photoBase64, // data:image/...;base64,...
                                "firma"  => $this->firma, // data:image/...;base64,...
                            ];
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
    
                    // --- 3) Actualización local según sea ENTREGADO o RETORNO ---
                    if ($this->estado === 'ENTREGADO') {
                        $paquete->update([
                            'accion'              => 'ENTREGADO',
                            'firma'               => $this->firma,
                            'observacion_entrega' => $this->observacion_entrega,
                            'photo'               => $photoBase64,
                        ]);
                        
                        // Si deseas, lo borras de la tabla principal:
                        $paquete->delete();
    
                        Event::create([
                            'action'      => 'ENTREGADO',
                            'descripcion' => 'Paquete entregado por el cartero',
                            'codigo'      => $paquete->codigo,
                            'user_id'     => auth()->id(),
                        ]);
    
                        // --- Llamada local a /entregar-envio ---
                        try {
                            $datosLocal = [
                                "codigo"              => $paquete->codigo,
                                "estado"              => 5, // Ajusta si tu API lo requiere
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
    
                    } else if ($this->estado === 'RETORNO') {
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
    
                        // --- Llamada local a /retornar-envio (AJUSTA si necesitas) ---
                        try {
                            $datosLocalRetorno = [
                                "codigo"      => $paquete->codigo,
                                "estado"      => 10, // Ajusta según tu lógica
                                "firma"       => $this->firma,
                                "observacion" => $this->observacion,
                                "photo"       => $photoBase64,
                            ];
    
                            $headersLocal = [
                                'Accept'       => 'application/json',
                                'Content-Type' => 'application/json',
                            ];
    
                            $responseLocalRetorno = Http::withHeaders($headersLocal)
                                ->put('http://172.65.10.52:8011/api/retornar-envio', $datosLocalRetorno);
    
                            if ($responseLocalRetorno->successful()) {
                                Log::info("Paquete {$paquete->codigo} actualizado en la API local `/retornar-envio` con éxito.");
                            } else {
                                Log::error("Error en `/retornar-envio`: " . $responseLocalRetorno->body());
                                session()->flash('error', 'Error en la API local de retorno.');
                            }
                        } catch (\Exception $e) {
                            Log::error("Excepción en `/retornar-envio`: " . $e->getMessage());
                            session()->flash('error', 'Error de conexión con la API local.');
                        }
                    }
    
                    session()->flash('message', 'El paquete se ha dado de baja correctamente.');
                } else {
                    // Hubo error en la respuesta
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
        $this->dispatch('reloadPage');  // fuerza recarga en el front
    }
    
    

    public function render()
    {
        return view('livewire.entregas');
    }
}
