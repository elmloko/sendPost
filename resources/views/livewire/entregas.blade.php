<div class="container-fluid p-4">
    {{-- Mensajes de éxito o error --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Formulario de búsqueda --}}
    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit.prevent="buscar">
                <div class="mb-3">
                    <label for="codigo" class="form-label">Código del paquete:</label>
                    <input type="text" id="codigo" wire:model="codigo" class="form-control"
                        placeholder="Ingrese el código y presione Enter">
                </div>
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
        </div>
    </div>

    {{-- Tabla con los paquetes --}}
    <div class="card">
        <div class="card-header">Lista de Paquetes por Entregar</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Destinatario</th>
                        <th>Estado</th>
                        <th>Ciudad</th>
                        <th>Peso</th>
                        @hasrole('Administrador|Encargado')
                            <th>Usuario</th>
                        @endhasrole
                        @hasrole('Administrador|Cartero')
                            <th>Acciones</th>
                        @endhasrole
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paquetes as $p)
                        <tr>
                            <td>{{ $p->codigo }}</td>
                            <td>{{ $p->destinatario }}</td>
                            <td>{{ $p->accion }}</td>
                            <td>{{ $p->cuidad }}</td>
                            <td>{{ $p->peso }}</td>
                            @hasrole('Administrador|Encargado')
                                <td>{{ $p->user }}</td>
                            @endhasrole
                            @hasrole('Administrador|Cartero')
                                <td>
                                    <button class="btn btn-danger btn-sm" wire:click="openModal({{ $p->id }})">
                                        Dar de baja
                                    </button>
                                </td>
                            @endhasrole
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No hay paquetes con estado CARTERO.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Dar de baja --}}
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.8);">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content border-primary shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        @php
                            $modalPaquete = $paquetes->where('id', $selectedPaquete)->first();
                            $codigoPaquete = $modalPaquete ? $modalPaquete->codigo : '';
                        @endphp
                        <h5 class="modal-title">Dar de baja {{ $codigoPaquete }}</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="darDeBaja">

                            {{-- Selección de estado --}}
                            <div class="mb-3" wire:ignore.self>
                                <label for="estado" class="form-label fw-bold">Estado</label>
                                <select id="estado" class="form-select" wire:model="estado"
                                    onchange="toggleCampos()">
                                    <option value="">Seleccione un estado</option>
                                    <option value="ENTREGADO">ENTREGADO</option>
                                    <option value="RETORNO">RETORNO</option>
                                </select>
                                @error('estado')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Observaciones: en DOM siempre, mostramos/ocultamos con JS --}}
                            <div class="mb-3" id="observacionContainer" style="display:none;">
                                <label for="observacion" class="form-label fw-bold">Observaciones</label>
                                <select id="observacion" wire:model="observacion" class="form-select">
                                    <option value="">Seleccione una observación</option>
                                    <option value="Dirección incorrecta">Dirección incorrecta</option>
                                    <option value="Destinatario no localizado">Destinatario no localizado</option>
                                    <option value="Destinatario ausente">Destinatario ausente</option>
                                    <option value="Artículo rechazado">Artículo rechazado</option>
                                    <option value="Reprogramación solicitada">Reprogramación solicitada</option>
                                    <option value="Acceso restringido">Acceso restringido</option>
                                    <option value="Artículo equivocado">Artículo equivocado</option>
                                    <option value="Artículo dañado">Artículo dañado</option>
                                    <option value="No reclamado">No reclamado</option>
                                    <option value="Fallecido">Fallecido</option>
                                    <option value="Fuerza mayor">Fuerza mayor</option>
                                    <option value="Recojo en agencia">Recojo en agencia</option>
                                    <option value="Destinatario de vacaciones">Destinatario de vacaciones</option>
                                    <option value="Destinatario en traslado">Destinatario en traslado</option>
                                    <option value="Falta de identificación">Falta de identificación</option>
                                    <option value="Reintentos fallidos">Reintentos fallidos</option>
                                    <option value="Otros">Otros</option>
                                </select>
                                @error('observacion')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Firma: en DOM siempre, mostramos/ocultamos con JS --}}
                            <div class="mb-3" id="firmaContainer" style="display:none;">
                                <label for="firma" class="form-label fw-bold">Firma</label>
                                <!-- input oculto donde guardamos el base64 -->
                                <input type="hidden" wire:model="firma" id="inputbase64">

                                <div class="text-center">
                                    <canvas id="canvas" class="border border-secondary rounded bg-white"
                                        width="600" height="250">
                                    </canvas>
                                </div>

                                <div class="mt-2 text-center">
                                    <button type="button" id="guardar" class="btn btn-primary me-2">
                                        Guardar Firma
                                    </button>
                                    <button type="button" id="limpiar" class="btn btn-secondary">
                                        Limpiar
                                    </button>
                                </div>
                                @error('firma')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Imagen opcional --}}
                            <div class="mb-3">
                                <label for="photo" class="form-label fw-bold">Imagen (opcional)</label>
                                <input type="file" wire:model="photo" id="photo" class="form-control">
                                @error('photo')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Previsualización de la imagen --}}
                            @if ($photo)
                                <div class="mb-3 text-center">
                                    <img src="{{ $photo->temporaryUrl() }}" alt="Previsualización"
                                        class="img-thumbnail mt-2" style="max-width: 200px;">
                                </div>
                            @endif

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" wire:click="closeModal">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn btn-success">
                                    Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Librería SignaturePad -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@5.0.0/dist/signature_pad.umd.min.js"></script>

<script>
    // Variables para SignaturePad
    let signaturePad;
    let inputBase64;

    // Inicializar firma cuando Livewire lo indique
    window.addEventListener('initFirma', function() {
        setTimeout(() => {
            const canvas = document.getElementById('canvas');
            if (!canvas) return;

            // Ajustar el tamaño del canvas según atributos width/height
            canvas.width = 600; // Fijo
            canvas.height = 250; // Fijo

            // Crear SignaturePad
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255,255,255)',
                penColor: 'rgb(0, 0, 0)'
            });

            inputBase64 = document.getElementById('inputbase64');

            const saveButton = document.getElementById('guardar');
            const clearButton = document.getElementById('limpiar');

            clearButton.addEventListener('click', limpiarFirma);
            saveButton.addEventListener('click', guardarFirma);
        }, 300);
    });

    function limpiarFirma() {
        if (!signaturePad) return;
        signaturePad.clear();
        if (inputBase64) {
            inputBase64.value = "";
            inputBase64.dispatchEvent(new Event('input'));
        }
    }

    function guardarFirma() {
        if (!signaturePad) return;
        if (signaturePad.isEmpty()) {
            alert('Por favor, haga una firma antes de guardar.');
            return;
        }
        const base64Signature = signaturePad.toDataURL();
        inputBase64.value = base64Signature;
        inputBase64.dispatchEvent(new Event('input'));
        alert('Firma guardada correctamente.');
    }

    /**
     * Mostrar/ocultar los contenedores de Observación y Firma
     * inmediatamente al cambiar el valor de Estado.
     */
    function toggleCampos() {
        const estado = document.getElementById('estado').value;
        const firmaContainer = document.getElementById('firmaContainer');
        const observacionContainer = document.getElementById('observacionContainer');

        if (!firmaContainer || !observacionContainer) return;

        // Si RETORNO, mostramos Observaciones y ocultamos Firma
        if (estado === 'RETORNO') {
            observacionContainer.style.display = 'block';
            firmaContainer.style.display = 'none';
        }
        // Si ENTREGADO, mostramos Firma y ocultamos Observaciones
        else if (estado === 'ENTREGADO') {
            firmaContainer.style.display = 'block';
            observacionContainer.style.display = 'none';
            // Si quieres reiniciar la firma cada vez que pase a ENTREGADO, llama aquí:
            // Livewire.emit('initFirma');
        }
        // Si nada seleccionado, oculta ambos
        else {
            observacionContainer.style.display = 'none';
            firmaContainer.style.display = 'none';
        }
    }

    // Recargar la página cuando Livewire lo indique
    window.addEventListener('reloadPage', event => {
        location.reload();
    });
</script>
