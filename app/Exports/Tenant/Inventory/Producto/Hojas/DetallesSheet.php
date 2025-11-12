<?php

namespace App\Exports\Tenant\Inventory\Producto\Hojas;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Productos\Categoria;
use App\Models\Productos\Marca;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DetallesSheet implements FromCollection, WithTitle,ShouldAutoSize,WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $categorias =   Category::where('status', 'ACTIVE')
                        ->pluck('name')
                        ->sort()
                        ->values();

        $marcas     =   Brand::where('status', 'ACTIVE')
                        ->pluck('name')
                        ->sort()
                        ->values();



        $maxCount           =   max($categorias->count(), $marcas->count());

        $categorias         =   $categorias->pad($maxCount, null);
        $marcas             =   $marcas->pad($maxCount, null);

        $combined           =   $categorias->zip($marcas)->map(function ($item) {
            return [
                'CATEGORÍA'        => $item[0],
                'MARCAS'           => $item[1],
            ];
        });

        return $combined->prepend([
            'CATEGORÍA'             => 'CATEGORÍA',
            'MARCAS'                => 'MARCAS',
        ]);

    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('bcd9e7');

        $highestRow = $sheet->getHighestRow();
        $sheet->getParent()->addNamedRange(
            new \PhpOffice\PhpSpreadsheet\NamedRange('categorias', $sheet, "A2:A{$highestRow}")
        );

        $sheet->getParent()->addNamedRange(
            new \PhpOffice\PhpSpreadsheet\NamedRange('marcas', $sheet, "B2:B{$highestRow}")
        );

        return [];
    }

    public function title(): string
    {
        return 'DETALLES';
    }
}
