<?php

namespace App\Support;

/**
 * Normalización de placas vehiculares. DOS comportamientos SEPARADOS (no se cruzan):
 *  - claveComparacion(): clave canónica para COMPARAR (quita guion Y espacios). NUNCA guardar.
 *  - normalizarStorage(): valor a GUARDAR (conserva el guion; quita espacios).
 * Si el storage usara la clave de comparación, las placas quedarían sin guion.
 */
class Placa
{
    /** Clave canónica para comparar (quita guion + espacios, upper). Ej: "p5t-895" -> "P5T895". */
    public static function claveComparacion(string $p): string
    {
        return mb_strtoupper(preg_replace('/[\s-]/', '', trim($p)), 'UTF-8');
    }

    /** Valor a guardar (conserva el guion; quita espacios, upper). Ej: "p5t 895" -> "P5T895" ; "p5t-895" -> "P5T-895". */
    public static function normalizarStorage(string $p): string
    {
        return mb_strtoupper(str_replace(' ', '', trim($p)), 'UTF-8');
    }
}
