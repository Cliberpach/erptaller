<?php

namespace App\Exports\Tenant\Inventory\Producto\Hojas;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Productos\Categoria;
use App\Models\Productos\Marca;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductosSheet implements  FromCollection, WithStyles, ShouldAutoSize, WithTitle,WithEvents
{

    protected $categoriasCount;
    protected $marcasCount;

    public function __construct()
    {
        $this->categoriasCount  =   Category::where('status', 'ACTIVE')->count();
        $this->marcasCount      =   Brand::where('status', 'ACTIVE')->count();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        $contenido = [
            ['NOMBRE','CÓDIGO BARRAS','CÓDIGO INTERNO','CATEGORÍA','MARCA','PRECIO VENTA','PRECIO COMPRA','STOCK MÍNIMO'],
            ['PRODUCTO 1','12345678','12345678','PRODUCTO','NACIONAL',1,1,1],
            ['PRODUCTO 2','12345678','12345678','PRODUCTO','NACIONAL',1,1,1]
        ];

        return collect($contenido);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('bcd9e7');

        return [];
    }

    public function afterSheet(\Maatwebsite\Excel\Events\AfterSheet $event)
 {
     $sheet = $event->sheet->getDelegate();


     $categoriasRange = "'DETALLES'!\$A\$2:\$A\$" . ($this->categoriasCount + 1);
     $marcasRange = "'DETALLES'!\$B\$2:\$B\$" . ($this->marcasCount + 1);

     for ($row = 2; $row <= 100; $row++) {

         $sheet->getCell("D{$row}")->getDataValidation()
               ->setType(DataValidation::TYPE_LIST)
               ->setFormula1($categoriasRange)
               ->setShowDropDown(true);

         $sheet->getCell("E{$row}")->getDataValidation()
               ->setType(DataValidation::TYPE_LIST)
               ->setFormula1($marcasRange)
               ->setShowDropDown(true);

     }
 }

 public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $this->afterSheet($event);
            },
        ];
    }



    public function title(): string
    {
        return 'PRODUCTOS';
    }
}
