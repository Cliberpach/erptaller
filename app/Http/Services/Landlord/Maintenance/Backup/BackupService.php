<?php

namespace App\Http\Services\Landlord\Maintenance\Backup;

use App\Jobs\GenerarBackupGlobal;
use Exception;
use Illuminate\Support\Facades\Cache;

class BackupService
{
    public function estadoConBackups(): array
    {
        return [
            'estado'  => Cache::get(GenerarBackupGlobal::ESTADO_KEY, [
                'estado'  => 'idle',
                'fecha'   => null,
                'mensaje' => '',
            ]),
            'backups' => $this->listarBackups(),
        ];
    }

    public function generar(): array
    {
        GenerarBackupGlobal::dispatch();

        $estado = [
            'estado'  => 'en_proceso',
            'fecha'   => now()->toDateTimeString(),
            'mensaje' => '',
        ];

        Cache::put(GenerarBackupGlobal::ESTADO_KEY, $estado);

        return $estado;
    }

    public function eliminar(string $archivo): void
    {
        $ruta = $this->rutaSegura($archivo);

        if (!$ruta) {
            throw new Exception('ARCHIVO NO VÁLIDO O NO ENCONTRADO.');
        }

        @unlink($ruta);
    }

    public function rutaSegura(string $archivo): ?string
    {
        if (!preg_match('/^backup_\d{8}_\d{6}\.tar\.gz$/', $archivo)) {
            return null;
        }

        $baseDir = storage_path('app/backups');
        $real    = realpath($baseDir . DIRECTORY_SEPARATOR . $archivo);

        if ($real === false || !str_starts_with($real, realpath($baseDir) . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $real;
    }

    private function listarBackups(): array
    {
        $baseDir  = storage_path('app/backups');
        $archivos = glob($baseDir . DIRECTORY_SEPARATOR . 'backup_*.tar.gz') ?: [];

        usort($archivos, fn($a, $b) => filemtime($b) <=> filemtime($a));

        return array_map(fn($ruta) => [
            'nombre' => basename($ruta),
            'tamano' => $this->tamanoLegible(filesize($ruta) ?: 0),
            'fecha'  => date('d/m/Y H:i', filemtime($ruta)),
        ], $archivos);
    }

    private function tamanoLegible(int $bytes): string
    {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i        = 0;
        $valor    = (float) $bytes;

        while ($valor >= 1024 && $i < count($unidades) - 1) {
            $valor /= 1024;
            $i++;
        }

        return round($valor, $i === 0 ? 0 : 1) . ' ' . $unidades[$i];
    }
}
