<?php

namespace App\Exports\Tenant\Inventory\Brand;

use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class BrandFormatExport implements FromCollection, WithStyles,ShouldAutoSize,WithTitle
{

    public function __construct()
    {

    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $contenido = [ ['NOMBRE'],['MARCA 1'],['MARCA 2'] ];

        return collect($contenido);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('bcd9e7');

        return [];
    }

    public function title(): string
    {
        return 'MARCAS';
    }
}
