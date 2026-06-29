<?php

namespace App\Exports\Tenant\WorkShop\Servicio;

use App\Exports\Tenant\WorkShop\Servicio\Hojas\InstruccionesSheet;
use App\Exports\Tenant\WorkShop\Servicio\Hojas\ServiciosSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Plantilla de importación de servicios de taller (espejo de ProductoExport, plano:
 * sin categoría/marca/stock). Hoja de datos + hoja de instrucciones.
 */
class ServicioExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ServiciosSheet(),
            new InstruccionesSheet(),
        ];
    }
}
