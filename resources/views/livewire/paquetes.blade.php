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

    {{-- Formulario para buscar --}}
    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit.prevent="buscar">
                <div class="row align-items-end">
                    <div class="col-md-8 mb-3">
                        <label for="input_search" class="form-label">Buscar por código, destinatario, estado o ciudad:</label>
                        <input type="text" id="input_search" wire:model.defer="input_search" class="form-control"
                            placeholder="Ingrese término de búsqueda">
                    </div>
                    <div class="col-md-4 mb-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla con los paquetes --}}
    <div class="card">
        <div class="card-header">
            Lista de Paquetes
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Destinatario</th>
                        <th>Estado</th>
                        <th>Ciudad</th>
                        <th>Peso</th>
                        <th>Observación</th>
                        <th>Foto</th>
                        <th>Fecha Actualización</th>
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
                            <td>{{ $p->observacion }}</td>
                            <td>
                                @if ($p->photo)
                                    <a href="{{ $p->photo }}" download="foto.png"
                                        class="btn btn-sm btn-secondary">Descargar</a>
                                @endif
                            </td>
                            <td>{{ $p->updated_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No hay paquetes encontrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $paquetes->links() }}
        </div>
    </div>

</div>
