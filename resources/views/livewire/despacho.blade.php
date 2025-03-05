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

    {{-- Formulario para buscar por código (opcional) --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                {{-- Columna para buscar paquete por código --}}
                <div class="col-md-6 mb-3">
                    <form wire:submit.prevent="buscar">
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Buscar paquete por código:</label>
                            <input type="text" id="codigo" wire:model="codigo" class="form-control"
                                placeholder="Ingrese el código del paquete"
                                oninput="if(this.value.length == 13){ $wire.call('buscar'); $wire.set('codigo',''); }">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Buscar</button>
                    </form>
                </div>

                {{-- Columna para exportar a Excel --}}
                <div class="col-md-6 mb-3">
                    <form wire:submit.prevent="exportarExcel">
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Selecciona una fecha:</label>
                            <input type="date" id="fecha" wire:model="fecha" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Exportar a Excel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla con los paquetes en estado RETORNO --}}
    <div class="card">
        <div class="card-header">
            Lista de Paquetes para Devolver a Ventanilla
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Destinatario</th>
                        <th>Ciudad</th>
                        <th>Peso</th>
                        <th>Observación</th>
                        <th>Fecha Retorno</th>
                        @hasrole('Administrador|Encargado')
                            <th>Acción</th>
                        @endhasrole
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paquetes as $p)
                        <tr>
                            <td>{{ $p->codigo }}</td>
                            <td>{{ $p->destinatario }}</td>
                            <td>{{ $p->cuidad }}</td>
                            <td>{{ $p->peso }}</td>
                            <td>{{ $p->observacion }}</td>
                            <td>{{ $p->updated_at }}</td>
                            @hasrole('Administrador|Encargado')
                                <td>
                                    <button wire:click="devolverAVentanilla('{{ $p->codigo }}')" class="btn btn-warning">
                                        Devolver a Ventanilla
                                    </button>
                                    <button wire:click="devolverACartero('{{ $p->codigo }}')" class="btn btn-info">
                                        Devolver a Cartero
                                    </button>
                                </td>
                            @endhasrole
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay paquetes para Retornar</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    document.addEventListener('pdf-descargado', function() {
        setTimeout(function() {
            location.reload();
        }, 500); // Espera 0.5 segundos antes de recargar para asegurar la descarga del PDF
    });
</script>
