<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Registro de Entregas</title>
    <style>
        /* Estilos para la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            line-height: 1.5;
            /* Ajusta aquí el espaciado */
        }

        .first-table th,
        .first-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            line-height: 1.2;
            /* Incrementa el line-height aquí */
        }

        thead {
            background-color: #f2f2f2;
        }

        /* Estilos para la imagen y el título */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            line-height: 0.5;
        }

        .title {
            text-align: center;
        }

        .date {
            line-height: 0.5;
        }

        .second-table {
            border: none;
            margin: 20px auto;
            /* Centra la segunda tabla en el medio */
            line-height: 0.5;
            /* Ajusta el line-height para quitar el interlineado */
        }

        .second-table th {
            background-color: white;
            /* Establece el fondo de los th a blanco */
            border: none;
            padding: 5px;
            text-align: center;
            /* Centra el texto en las celdas */
            line-height: 1;
            /* Ajusta el line-height para quitar el interlineado */
        }

        .second-table td {
            border: none;
            padding: 5px;
            text-align: center;
            /* Centra el texto en las celdas */
            line-height: 1;
            /* Ajusta el line-height para quitar el interlineado */
        }

        .notification-table {
            border: 1px solid #000;
            margin: 20px auto;
            /* Centra la segunda tabla en el medio */
            line-height: 1;
            /* Ajusta el line-height para quitar el interlineado */
        }

        .notification-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            /* Centra el texto en las celdas */
            line-height: 1;
            /* Ajusta el line-height para quitar el interlineado */
        }

        .resume-table {
            border: 1px solid #000;
            margin: 20px auto;
            width: 70%;
            /* Ancho de la tabla */
            text-align: center;
            /* Centra el contenido de la tabla */
        }

        .resume-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 10px;
            /* Tamaño de la fuente más pequeño */
            line-height: 0.5;
            /* Ajusta el line-height para quitar el interlineado */
        }

        .resume-table th {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 12px;
            /* Tamaño de la fuente para los títulos */
            font-weight: bold;
            /* Texto en negrita para los títulos */
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('images/images.png') }}" alt="" width="150" height="50">
        </div>
        <div class="title">
            <h3>Registro de entregas de Correspondencia a Domicilio</h3>
        </div><br>
    </div>
    <table class="date">
        <tbody>
            @foreach ($packages as $package)
                <tr>
                    <td>
                        <p>Nombre del Distribuidor: {{ auth()->user()->name }}</p>
                    </td>
                    <td>Regional: {{ auth()->user()->city }}</td>
                </tr>
                <tr>
                    <td>
                        <p>Fecha: {{ now()->format('Y-m-d H:i') }}</p>
                    </td>
                    <td>

                    </td>
                </tr>
            @break
        @endforeach
    </tbody>
</table>
<table class="first-table">
    <thead>
        <tr>
            <th>No</th>
            <th>Código Rastreo</th>
            <th>Destinatario</th>
            <th>Peso (Kg.)</th>
            <th>Fecha y Hora</th>
            <th>Razon / Accion</th>
            <th>Firma/Sello Destinatario</th>
            <th>Cobro (Bs.)</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp <!-- Inicializa $i con 1 -->
        @foreach ($packages as $package)
            {{-- @if ($package->CUIDAD === auth()->user()->Regional) --}}
            <tr>
                <td>{{ $i }}</td>
                <td>
                    <p class="barcode">{!! DNS1D::getBarcodeHTML($package->codigo, 'C128', 1.25, 25) !!} <br></p>{{ $package->codigo }}
                </td>
                <td>{{$package->destinatario }}</td>
                <td>{{ $package->peso }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @php $i++; @endphp <!-- Incrementa $i en cada iteración -->
            {{-- @endif --}}
        @endforeach
    </tbody>
</table>
<table class="notification-table">
    <thead>
        <tr>
            <td>Accion</td>
            <td>
                <b>10.</b> Dirección incorrecta
                <b>11.</b> Destinatario no localizado
                <b>12.</b> Destinatario ausente
                <b>13.</b> Artículo rechazado 
                <b>14.</b> Reprogramación solicitada 
                <b>15.</b> Acceso restringido 
                <b>16.</b> Artículo equivocado 
                <b>17.</b> Artículo dañado 
                <b>18.</b> No reclamado
                <b>19.</b> Fallecido
                <b>20.</b> Fuerza mayor
                <b>21.</b> Recojo en agencia
                <b>22.</b> Destinatario de vacaciones
                <b>23.</b> Destinatario en traslado
                <b>24.</b> Falta de identificación
                <b>25.</b> Reintentos fallidos 
                <b>99.</b> Otros
              </td>              
        </tr>
        <tr>
            <td>Razon</td>
            <td>
                <b>A.</b> Entrega intentada hoy
                <b>B.</b> Entrega reprogramada para mañana
                <b>C.</b> Artículo retenido; destinatario notificado
                <b>D.</b> Remitente contactado
                <b>E.</b> Devuelto a ventanilla
              </td>              
        </tr>
    </thead>
</table>
<table class="resume-table">
    <thead>
        <tr>
            <th></th>
            <th>CERTIFICADO</th>
            <th>ORDINARIO</th>
            <th>EMS</th>
            <th>CONTRATOS</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>TOTAL ENTREGADOS</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>TOTAL NOTIFICADOS</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>TOTAL PENDIENTE</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>TOTAL REZAGO</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><b>TOTAL ENVIOS LLEVADOS</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>
<table class="second-table">
    <thead>
        <tr>
            <th>__________________________</th>
            <th>__________________________</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($packages as $package)
            <tr>
                <td>
                    <p>SUPERVISOR/SALIDA<br></p>
                </td>
                <td>
                    <p>ENTREGADO POR<br>{{ auth()->user()->name }}</p>
                </td>
            </tr>
        @break
    @endforeach
</tbody>
</table>
</body>

</html>
