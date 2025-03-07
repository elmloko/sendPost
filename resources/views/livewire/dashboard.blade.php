<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-center">
            <h2 class="mb-4">Estadísticas de Paquetes por Ciudad</h2>
        </div>
    </div>

    <div class="row">
        @foreach ($dataChart as $ciudad => $accions)
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="card-title">{{ $ciudad }}</h3>
                    </div>
                    <div class="card-body" style="height: 350px;">
                        <div id="chart-{{ Str::slug($ciudad) }}" style="height: 100%;"></div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    Highcharts.chart("chart-{{ Str::slug($ciudad) }}", {
                        chart: {
                            type: 'pie'
                            // height: 350 // (opcional) puedes fijar la altura del chart en vez de usar CSS
                        },
                        title: {
                            text: "Distribución en {{ $ciudad }}",
                            style: { fontSize: '14px' }
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                // Ajusta el tamaño del círculo (en porcentaje o píxeles, por ejemplo "70%" o 200)
                                size: '70%',
                                dataLabels: {
                                    enabled: true,
                                    format: '<b>{point.name}</b>: {point.y}',
                                    style: { fontSize: '12px' }
                                }
                            }
                        },
                        series: [{
                            name: 'Cantidad',
                            colorByPoint: true,
                            data: [
                                { name: 'Asignado',  y: {{ $accions['ASIGNADO'] ?? 0 }} },
                                { name: 'Cartero',   y: {{ $accions['CARTERO']  ?? 0 }} },
                                { name: 'Retorno',   y: {{ $accions['RETORNO']  ?? 0 }} },
                                { name: 'Intentos',  y: {{ $accions['INTENTO']  ?? 0 }} },
                                { name: 'Entregado', y: {{ $accions['ENTREGADO'] ?? 0 }} }
                            ]
                        }]
                    });
                });
            </script>
        @endforeach
    </div>
</div>

<!-- Dependencia de Highcharts -->
<script src="https://code.highcharts.com/highcharts.js"></script>
