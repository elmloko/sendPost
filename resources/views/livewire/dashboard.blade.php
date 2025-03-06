<div>
    <h2 class="text-xl font-bold mb-4 text-center">Estadísticas de Paquetes por Ciudad</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-5 gap-4 max-w-screen-lg mx-auto">
        @foreach ($dataChart as $ciudad => $accions)
            <div class="bg-white shadow-md rounded-lg p-4 h-[400px] flex flex-col items-center">
                <h3 class="text-lg font-semibold text-center">{{ $ciudad }}</h3>
                <div id="chart-{{ Str::slug($ciudad) }}" class="h-64 w-full"></div>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    Highcharts.chart("chart-{{ Str::slug($ciudad) }}", {
                        chart: {
                            type: 'pie'
                        },
                        title: {
                            text: "Distribución en {{ $ciudad }}",
                            style: { fontSize: '14px' }
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
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
                                { name: 'Asignado', y: {{ $accions['ASIGNADO'] ?? 0 }} },
                                { name: 'Cartero', y: {{ $accions['CARTERO'] ?? 0 }} },
                                { name: 'Retorno', y: {{ $accions['RETORNO'] ?? 0 }} },
                                { name: 'Intentos', y: {{ $accions['INTENTO'] ?? 0 }} },
                                { name: 'Entregado', y: {{ $accions['ENTREGADO'] ?? 0 }} }
                            ]
                        }]
                    });
                });
            </script>
        @endforeach
    </div>

    <script src="https://code.highcharts.com/highcharts.js"></script>
</div>
