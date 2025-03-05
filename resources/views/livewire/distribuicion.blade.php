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

    {{-- Card para búsqueda y exportación --}}
    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit.prevent="buscar">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="codigo" class="form-label">Código del paquete:</label>
                        <input type="text" id="codigo" wire:model="codigo" class="form-control"
                            placeholder="Ingrese el código"
                            oninput="if(this.value.length == 13){ $wire.call('buscar'); $wire.set('codigo',''); }">
                    </div>
                    <div class="col-md-7 d-flex align-items-end justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">
                            Buscar
                        </button>
                        <button class="btn btn-secondary" type="button" wire:click="exportExcel">
                            Exportar a Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla con los paquetes --}}
    <div class="card">
        <div class="card-header">
            Lista de Paquetes para ser Asignados por Cartero
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Destinatario</th>
                        <th>Estado</th>
                        <th>Ciudad</th>
                        <th>Peso</th>
                        @hasrole('Administrador')
                            <th>Usuario</th>
                        @endhasrole
                        <th>Acción</th>
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
                            @hasrole('Administrador')
                                <td>{{ $p->user }}</td>
                            @endhasrole
                            <td>
                                <button class="btn btn-danger btn-sm" wire:click="eliminar('{{ $p->codigo }}')"
                                    onclick="return confirm('¿Estás seguro de eliminar este paquete?')">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay paquetes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-end mt-3">
                <button id="boton-entrega" class="btn btn-success" wire:click="asignarACartero()"
                    wire:loading.attr="disabled"
                    onclick="return confirm('¿Estás seguro de recoger todos los paquetes?')">
                    Iniciar a entregar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('pdf-descargado', function() {
        setTimeout(function() {
            location.reload();
        }, 500);
    });
</script>
