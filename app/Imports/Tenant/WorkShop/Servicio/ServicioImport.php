<?php

namespace App\Imports\Tenant\WorkShop\Servicio;

use App\Models\Tenant\WorkShop\Service;
use Exception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Importador de servicios (Parte B): parsea + clasifica las filas en 3 listas
 * (errores de formato / duplicados / válidos). NO crea — la creación la orquesta el
 * controller (errores -> aborta; válidos -> crea; duplicados -> omite).
 */
class ServicioImport implements ToCollection, WithMultipleSheets
{
    protected $resultado = null;

    public function collection(Collection $rows)
    {
        $errores            = [];
        $duplicados         = [];
        $validos            = [];
        $nombresProcesados  = [];

        foreach ($rows as $key => $row) {

            // Validar encabezados (primera fila).
            if ($key === 0) {
                $headers = ["NOMBRE", "PRECIO", "DESCRIPCIÓN"];
                foreach ($headers as $index => $header) {
                    if (strtoupper(trim((string) ($row[$index] ?? ''))) !== $header) {
                        throw new Exception("FORMATO INCORRECTO DEL ARCHIVO EXCEL");
                    }
                }
                continue;
            }

            $nombre      = mb_strtoupper(trim((string) ($row[0] ?? '')));
            $precio      = trim((string) ($row[1] ?? ''));
            $descripcion = isset($row[2]) ? trim((string) $row[2]) : '';
            $fila        = $key + 1;

            // Fila completamente vacía -> se ignora (línea en blanco).
            if ($nombre === '' && $precio === '') {
                continue;
            }

            // ===== 1) ERROR DE FORMATO (aborta todo) =====
            $erroresFila = [];
            if ($nombre === '') {
                $erroresFila[] = "el nombre es obligatorio";
            } elseif (mb_strlen($nombre) > 160) {
                $erroresFila[] = "el nombre supera 160 caracteres";
            }
            if ($precio === '' || !is_numeric($precio) || (float) $precio < 0) {
                $erroresFila[] = "el precio debe ser un número mayor o igual a 0";
            }
            if (count($erroresFila) > 0) {
                $errores[] = ['fila' => $fila, 'nombre' => $nombre, 'mensaje' => implode('; ', $erroresFila)];
                continue;
            }

            // ===== 2) DUPLICADO (omite la fila) =====
            if (in_array($nombre, $nombresProcesados)) {
                $duplicados[] = ['fila' => $fila, 'nombre' => $nombre, 'motivo' => 'repetido en el Excel'];
                continue;
            }
            if (Service::where('name', $nombre)->where('status', 'ACTIVE')->exists()) {
                $duplicados[] = ['fila' => $fila, 'nombre' => $nombre, 'motivo' => 'ya existe en servicios'];
                $nombresProcesados[] = $nombre;
                continue;
            }

            // ===== 3) VÁLIDO (se crea) =====
            $nombresProcesados[] = $nombre;
            $validos[] = [
                'fila'        => $fila,
                'name'        => $nombre,
                'price'       => $precio,
                'description' => $descripcion, // '' si vacío (el DTO no admite null)
            ];
        }

        $this->resultado = (object) [
            'errores'    => $errores,
            'duplicados' => $duplicados,
            'validos'    => $validos,
        ];

        return $this->resultado;
    }

    public function sheets(): array
    {
        return [
            'SERVICIOS' => $this,
        ];
    }

    public function getResultados()
    {
        return $this->resultado;
    }
}
