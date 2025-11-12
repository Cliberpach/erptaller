<?php

namespace App\Exports\Tenant\Inventory\Producto\Hojas;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Collection;

class InstruccionesSheet implements FromCollection, WithTitle, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $instrucciones = [
            [''],
            ['Campo', 'Descripción', 'Validación'],
            ['nombre', 'Nombre del producto', 'Requerido, máximo 200 caracteres, único si no está anulado'],
            ['codigo_barras', 'Código de barras del producto', 'Opcional, máximo 20 caracteres'],
            ['codigo_interno', 'Código interno del producto', 'Opcional, máximo 20 caracteres'],
            ['categoria', 'Categoría del producto', 'Requerido, debe existir en la tabla de categorías'],
            ['marca', 'Marca del producto', 'Requerido, debe existir en la tabla de marcas'],
            ['unidad_medida', 'Unidad de medida del producto', 'Requerido, debe existir en la tabla de detalles generales'],
            ['precio', 'Precio del producto', 'Requerido, debe ser un valor numérico'],
            ['stock_minimo', 'Stock mínimo del producto', 'Requerido, debe ser un valor numérico'],

        ];

        return new Collection($instrucciones);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', 'INSTRUCCIONES PARA EL FORMATO DE PRODUCTOS');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A2:C2')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('bcd9e7');
        $sheet->getStyle('A2:C2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('bcd9e7');

        // Ajusta el ancho de las columnas A, B y C
        $sheet->getColumnDimension('A')->setWidth(20); // Ancho para "Campo"
        $sheet->getColumnDimension('B')->setWidth(35); // Ancho para "Descripción"
        $sheet->getColumnDimension('C')->setWidth(35); // Ancho para "Validación"

        // Establece el ajuste de texto sin modificar la altura de las celdas
        $sheet->getStyle('A:C')->getAlignment()->setWrapText(true);

        return [];
    }


    public function title(): string
    {
        return 'INSTRUCCIONES';
    }
}
