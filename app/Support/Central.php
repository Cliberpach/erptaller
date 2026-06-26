<?php

namespace App\Support;

class Central
{
    /**
     * Prefija una tabla con el nombre REAL de la BD central (conexión landlord),
     * leído de config → portable (local: erptaller_central; producción: el real).
     * Única fuente de verdad para los JOINs cross-database a catálogos centrales.
     */
    public static function table(string $name): string
    {
        return config('database.connections.landlord.database') . '.' . $name;
    }
}
