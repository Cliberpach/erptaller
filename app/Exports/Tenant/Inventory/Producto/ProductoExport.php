<?php

namespace App\Exports\Tenant\Inventory\Producto;

use App\Exports\Tenant\Inventory\Producto\Hojas\DetallesSheet;
use App\Exports\Tenant\Inventory\Producto\Hojas\InstruccionesSheet;
use App\Exports\Tenant\Inventory\Producto\Hojas\ProductosSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;


class ProductoExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ProductosSheet(),    // Primera hoja con datos de productos
            new InstruccionesSheet(), // Segunda hoja con subtítulo "INSTRUCCIONES"
            new DetallesSheet()
        ];
    }
}
