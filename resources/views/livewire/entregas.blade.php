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
                        <th>Acciones</th>
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
                            <td>
                                <button class="btn btn-danger btn-sm" wire:click="openModal({{ $p->id }})">
                                    Dar de baja
                                </button>
                            </td>
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

    {{-- Modal Dar de baja --}}
    @if ($showModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.8);">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-primary shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Dar de baja {{ $p->codigo }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="darDeBaja">
                        <div class="mb-3">
                            <label for="estado" class="form-label fw-bold">Estado</label>
                            <select id="estado" wire:model="estado" onchange="toggleObservacion()" class="form-select">
                                <option value="">Seleccione un estado</option>
                                <option value="ENTREGADO">ENTREGADO</option>
                                <option value="RETORNO">RETORNO</option>
                            </select>
                            @error('estado')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- Contenedor de observaciones, oculto por defecto -->
                        <div class="mb-3" id="observacionContainer" style="display: none;">
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
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="closeModal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

    <script>
        function toggleObservacion() {
            var estadoSelect = document.getElementById('estado');
            var observacionContainer = document.getElementById('observacionContainer');
    
            if (estadoSelect.value === 'RETORNO') {
                observacionContainer.style.display = 'block';
            } else {
                observacionContainer.style.display = 'none';
            }
        }
        // Ejecuta la función cuando se cargue la página o el modal
        document.addEventListener('DOMContentLoaded', function() {
            toggleObservacion();
        });
    </script>    
</div>

