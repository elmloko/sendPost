<?php

namespace App\Exports;

use App\Models\Paquete;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class DespachoExport implements FromCollection, WithHeadings, WithStyles
{
    protected $fecha;

    // Recibimos la fecha desde el constructor
    public function __construct($fecha)
    {
        $this->fecha = $fecha;
    }

    // Obtenemos la colección a exportar
    public function collection()
    {
        $query = Paquete::where('accion', 'RETORNO');

        // Si se proporciona una fecha, filtramos por la columna deseada (por ejemplo, deleted_at)
        if ($this->fecha) {
            // Convertimos la fecha al formato deseado
            $date = Carbon::parse($this->fecha)->format('Y-m-d');
            $query->whereDate('updated_at', $date);
        }

        $paquetes = $query->get();

        // Mapeamos los datos para mostrar las columnas deseadas
        return $paquetes->map(function($paquete) {
            return [
                'Código'          => $paquete->codigo,
                'Destinatario'    => $paquete->destinatario,
                'Ciudad'          => $paquete->cuidad, // Verifica si el nombre del campo es "ciudad" o "cuidad"
                'Peso'            => $paquete->peso,
                'Fecha Entregado' => $paquete->updated_at ? $paquete->updated_at->format('Y-m-d H:i:s') : 'N/A',
            ];
        });
    }

    // Definimos los encabezados del Excel
    public function headings(): array
    {
        return [
            'Código',
            'Destinatario',
            'Ciudad',
            'Peso',
            'Fecha Entregado',
        ];
    }

    // Aplicamos estilos a la hoja
    public function styles(Worksheet $sheet)
    {
        // Estilo para la cabecera
        $sheet->getStyle('A1:E1')->getAlignment()->setVertical('center');
        $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        // Estilo para el resto de las filas
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A2:E{$highestRow}")->getAlignment()->setVertical('center');
        $sheet->getStyle("A2:E{$highestRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A:E')->getFont()->setSize(12);

        // Autoajuste del ancho de las columnas
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
