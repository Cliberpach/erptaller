<?php

namespace App\Console\Commands;

use App\Jobs\GenerarBackupGlobal;
use Illuminate\Console\Command;

class BackupGlobal extends Command
{
    protected $signature = 'backup:global';

    protected $description = 'Encola la generación del backup global (BD central + tenants + archivos).';

    public function handle(): int
    {
        GenerarBackupGlobal::dispatch();
        $this->info('Backup global encolado.');

        return self::SUCCESS;
    }
}
