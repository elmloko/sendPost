<?php

namespace App\Exports;

use App\Models\Paquete;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class DistribuicionExport implements FromCollection, WithHeadings, WithStyles
{
    protected $fecha;

    // Recibimos la fecha desde el constructor (opcional)
    public function __construct($fecha = null)
    {
        $this->fecha = $fecha;
    }

    // Obtenemos la colección a exportar
    public function collection()
    {
        // Consulta para obtener los paquetes asignados al usuario con estado "ASIGNADO"
        $query = Paquete::where('user', auth()->user()->name)
            ->where('accion', 'ASIGNADO');

        // Si se proporciona una fecha, se filtra por la columna updated_at
        if ($this->fecha) {
            $date = Carbon::parse($this->fecha)->format('Y-m-d');
            $query->whereDate('updated_at', $date);
        }

        $paquetes = $query->get();

        // Mapeamos los datos para estructurarlos con los campos deseados
        return $paquetes->map(function($paquete) {
            return [
                'Código'         => $paquete->codigo,
                'Destinatario'   => $paquete->destinatario,
                'Estado'         => $paquete->accion,
                'Ciudad'         => $paquete->cuidad, // Verifica que el campo sea "ciudad" o "cuidad"
                'Peso'           => $paquete->peso,
                'Fecha Asignado' => $paquete->updated_at ? $paquete->updated_at->format('Y-m-d H:i:s') : 'N/A',
            ];
        });
    }

    // Definimos los encabezados del Excel
    public function headings(): array
    {
        return [
            'Código',
            'Destinatario',
            'Estado',
            'Ciudad',
            'Peso',
            'Fecha Asignado',
        ];
    }

    // Aplicamos estilos a la hoja
    public function styles(Worksheet $sheet)
    {
        // Estilo para la cabecera
        $sheet->getStyle('A1:F1')->getAlignment()->setVertical('center');
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        // Estilo para el resto de las filas
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A2:F{$highestRow}")->getAlignment()->setVertical('center');
        $sheet->getStyle("A2:F{$highestRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A:F')->getFont()->setSize(12);

        // Autoajuste del ancho de las columnas
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
