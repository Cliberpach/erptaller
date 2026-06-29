<?php

namespace App\Exports\Tenant\WorkShop\Servicio\Hojas;

use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Hoja de datos de la plantilla de servicios. Servicio es plano: NOMBRE, PRECIO,
 * DESCRIPCIÓN (sin categoría/marca/stock).
 */
class ServiciosSheet implements FromCollection, WithStyles, ShouldAutoSize, WithTitle
{
    public function collection()
    {
        $contenido = [
            ['NOMBRE', 'PRECIO', 'DESCRIPCIÓN'],
            ['LAVADO DE AUTO', 25, 'LAVADO EXTERIOR E INTERIOR'],
            ['CAMBIO DE ACEITE', 80, ''],
        ];

        return collect($contenido);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('bcd9e7');

        return [];
    }

    public function title(): string
    {
        return 'SERVICIOS';
    }
}
