<?php

namespace App\Exports\Tenant\WorkShop\Servicio\Hojas;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Collection;

class InstruccionesSheet implements FromCollection, WithTitle, WithStyles
{
    public function collection()
    {
        $instrucciones = [
            [''],
            ['Campo', 'Descripción', 'Validación'],
            ['NOMBRE', 'Nombre del servicio', 'Requerido, máximo 160 caracteres. Si está duplicado (ya existe o repetido en el Excel) la fila se OMITE.'],
            ['PRECIO', 'Precio del servicio', 'Requerido, numérico mayor o igual a 0. Si es inválido se ABORTA toda la importación.'],
            ['DESCRIPCIÓN', 'Descripción del servicio', 'Opcional.'],
        ];

        return new Collection($instrucciones);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', 'INSTRUCCIONES PARA EL FORMATO DE SERVICIOS');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A2:C2')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('bcd9e7');
        $sheet->getStyle('A2:C2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('bcd9e7');

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(50);
        $sheet->getStyle('A:C')->getAlignment()->setWrapText(true);

        return [];
    }

    public function title(): string
    {
        return 'INSTRUCCIONES';
    }
}
