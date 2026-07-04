<?php

namespace App\Http\Services\Landlord\Maintenance\Backup;

class BackupManager
{
    protected BackupService $s_backup;

    public function __construct()
    {
        $this->s_backup = new BackupService();
    }

    public function estadoConBackups(): array
    {
        return $this->s_backup->estadoConBackups();
    }

    public function generar(): array
    {
        return $this->s_backup->generar();
    }

    public function eliminar(string $archivo): void
    {
        $this->s_backup->eliminar($archivo);
    }

    public function rutaSegura(string $archivo): ?string
    {
        return $this->s_backup->rutaSegura($archivo);
    }
}
