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
            <form wire:submit.prevent="buscar" class="row g-2">
                <div class="col-12 col-md-6">
                    <label for="codigo" class="form-label">Código del paquete:</label>
                    <input type="text" id="codigo" wire:model="codigo" class="form-control"
                        placeholder="Ingrese el código y presione Enter">
                </div>
                <div class="col-12 col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Lista de Paquetes en Modo Vertical para Móviles --}}
    <div class="d-block d-md-none">
        @forelse ($paquetes as $p)
            <div class="card mb-2">
                <div class="card-body">
                    <p><strong>Código:</strong> {{ $p->codigo }}</p>
                    <p><strong>Destinatario:</strong> {{ $p->destinatario }}</p>
                    <p><strong>Estado:</strong> {{ $p->accion }}</p>
                    <p><strong>Ciudad:</strong> {{ $p->cuidad }}</p>
                    <p><strong>Peso:</strong> {{ $p->peso }}</p>
                    @hasrole('Administrador|Encargado')
                        <p><strong>Usuario:</strong> {{ $p->user }}</p>
                    @endhasrole
                    @hasrole('Administrador|Cartero')
                        <button class="btn btn-danger btn-sm w-100" wire:click="openModal({{ $p->id }})">
                            Dar de baja
                        </button>
                    @endhasrole
                </div>
            </div>
        @empty
            <p class="text-center">No hay paquetes con estado CARTERO.</p>
        @endforelse
    </div>

    {{-- Tabla para Escritorio --}}
    <div class="card d-none d-md-block">
        <div class="card-header">Lista de Paquetes por Entregar</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="text-center">
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
                                        <button class="btn btn-danger btn-sm w-100" wire:click="openModal({{ $p->id }})">
                                            Dar de baja
                                        </button>
                                    </td>
                                @endhasrole
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay paquetes con estado CARTERO.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


    {{-- Modal Dar de baja --}}
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.8);">
            <div class="modal-dialog modal-dialog-centered modal-lg w-100" role="document">
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
                            <div class="mb-3">
                                <label for="estado" class="form-label fw-bold">Estado</label>
                                <select id="estado" wire:model="estado" class="form-select">
                                    <option value="">Seleccione un estado</option>
                                    <option value="ENTREGADO">ENTREGADO</option>
                                    <option value="RETORNO">RETORNO</option>
                                </select>
                            </div>

                            @if ($estado === 'RETORNO')
                                <div class="mb-3">
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
                                </div>
                            @endif

                            @if ($estado !== 'RETORNO')
                            <div id="firmaContainer" class="mb-3">
                                <label for="firma" class="form-label fw-bold">Firma</label>
                                <input type="hidden" wire:model="firma" id="inputbase64">
                                <div class="text-center">
                                    <canvas id="canvas" class="border border-secondary rounded bg-white w-100" width="600" height="250"></canvas>
                                </div>
                                <div class="mt-2 text-center">
                                    <button type="button" id="guardar" class="btn btn-primary me-2">Guardar Firma</button>
                                    <button type="button" id="limpiar" class="btn btn-secondary">Limpiar</button>
                                </div>
                            </div>
                            @endif

                            <!-- Campo para subir la imagen -->
                            <div class="mb-3">
                                <label for="photo" class="form-label fw-bold">Imagen (opcional)</label>
                                <input type="file" wire:model="photo" id="photo" class="form-control">
                                @error('photo')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Previsualización de la imagen -->
                            @if ($photo)
                                <div class="mb-3 text-center">
                                    <img src="{{ $photo->temporaryUrl() }}" alt="Previsualización"
                                        class="img-thumbnail mt-2" style="max-width: 200px;">
                                </div>
                            @endif

                            <div class="modal-footer d-flex flex-column flex-md-row">
                                <button type="button" class="btn btn-outline-secondary w-100 w-md-auto" wire:click="closeModal">Cancelar</button>
                                <button type="submit" class="btn btn-success w-100 w-md-auto">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Responsividad para ocultar firma si se elige "RETORNO" -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const estadoSelect = document.getElementById("estado");
        const firmaContainer = document.getElementById("firmaContainer");

        estadoSelect.addEventListener("change", function () {
            if (estadoSelect.value === "RETORNO") {
                firmaContainer.style.display = "none";
            } else {
                firmaContainer.style.display = "block";
            }
        });
    });
</script>

<!-- Librería SignaturePad -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@5.0.0/dist/signature_pad.umd.min.js"></script>

<script>
    // Declaramos variables globales
    let signaturePad;
    let canvas;
    let inputBase64;

    window.addEventListener('initFirma', function() {
        // Esperamos a que Livewire pinte el modal:
        setTimeout(() => {
            canvas = document.getElementById('canvas');
            if (!canvas) return; // Si no se encuentra, salimos

            // Recomendado para permitir gestos táctiles:
            canvas.style.touchAction = 'none';

            // Inicializar la SignaturePad
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255,255,255)',
                penColor: 'rgb(0, 0, 0)'
            });

            inputBase64 = document.getElementById('inputbase64');

            const saveButton = document.getElementById('guardar');
            const clearButton = document.getElementById('limpiar');

            // Asignar eventos
            clearButton.addEventListener('click', limpiarFirma);
            saveButton.addEventListener('click', guardarFirma);
        }, 300);
        // pequeño delay para asegurarnos de que el modal ya se pintó y es visible
    });

    function limpiarFirma() {
        if (!signaturePad) return;
        signaturePad.clear();
        if (inputBase64) {
            inputBase64.value = "";
            // Disparamos el evento para que Livewire actualice la propiedad
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
</script>
<script>
    function toggleObservacion() {
        const estadoSelect = document.getElementById('estado');
        const obsContainer = document.getElementById('observacionContainer');

        if (estadoSelect.value === 'RETORNO') {
            obsContainer.style.display = 'block';
        } else {
            obsContainer.style.display = 'none';
        }
    }
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const estadoSelect = document.getElementById("estado");
        const firmaContainer = document.getElementById("firmaContainer");

        estadoSelect.addEventListener("change", function () {
            if (estadoSelect.value === "RETORNO") {
                firmaContainer.style.display = "none";
            } else {
                firmaContainer.style.display = "block";
            }
        });
    });
</script>
