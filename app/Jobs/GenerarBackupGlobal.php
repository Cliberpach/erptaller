<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GenerarBackupGlobal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 1800;

    public const ESTADO_KEY = 'backup_global_estado';

    public function __construct()
    {
        $this->onConnection('database');
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $ts      = date('Ymd_His');
        $baseDir = storage_path('app/backups');
        $tmpDir  = $baseDir . DIRECTORY_SEPARATOR . 'tmp_' . $ts;
        $sqlDir  = $tmpDir . DIRECTORY_SEPARATOR . 'sql';
        $tarPath = $baseDir . DIRECTORY_SEPARATOR . 'backup_' . $ts . '.tar.gz';

        Cache::put(self::ESTADO_KEY, ['estado' => 'en_proceso', 'fecha' => now()->toDateTimeString(), 'mensaje' => '']);

        try {
            $this->asegurarDir($baseDir);
            $this->asegurarDir($sqlDir);

            $landlordConfig = config('database.connections.landlord');

            $this->dump(
                $landlordConfig,
                $landlordConfig['database'],
                $sqlDir . DIRECTORY_SEPARATOR . 'central_' . $landlordConfig['database'] . '.sql.gz'
            );

            foreach (Tenant::all() as $tenant) {
                $this->dump(
                    $landlordConfig,
                    $tenant->database,
                    $sqlDir . DIRECTORY_SEPARATOR . 'tenant_' . $tenant->database . '.sql.gz'
                );
            }

            $this->empaquetar($tarPath, $tmpDir);

            if (!file_exists($tarPath) || filesize($tarPath) === 0) {
                throw new RuntimeException('EL ARCHIVO DE BACKUP NO SE GENERÓ CORRECTAMENTE.');
            }

            $this->aplicarRetencion($baseDir);
            $this->borrarDir($tmpDir);

            Cache::put(self::ESTADO_KEY, [
                'estado'  => 'completado',
                'fecha'   => now()->toDateTimeString(),
                'mensaje' => 'Backup generado: ' . basename($tarPath),
            ]);
        } catch (Throwable $e) {
            $this->borrarDir($tmpDir);

            Log::error('GenerarBackupGlobal: ' . $e->getMessage(), ['exception' => $e]);

            Cache::put(self::ESTADO_KEY, [
                'estado'  => 'error',
                'fecha'   => now()->toDateTimeString(),
                'mensaje' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function dump(array $cfg, string $database, string $destino): void
    {
        $binary = env('MYSQLDUMP_BINARY', 'mysqldump');

        $cmd = [
            $binary,
            '--single-transaction',
            '--quick',
            '--no-tablespaces',
            '-h', $cfg['host'],
            '-P', (string) $cfg['port'],
            '-u', $cfg['username'],
            $database,
        ];

        $descriptorspec = [
            0 => ['file', $this->devNull(), 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $env = getenv();
        $env['MYSQL_PWD'] = $cfg['password'];

        $process = proc_open($cmd, $descriptorspec, $pipes, null, $env);

        if (!is_resource($process)) {
            throw new RuntimeException("NO SE PUDO INICIAR mysqldump PARA {$database}.");
        }

        $gz = gzopen($destino, 'wb6');

        while (!feof($pipes[1])) {
            $chunk = fread($pipes[1], 8192);
            if ($chunk !== false && $chunk !== '') {
                gzwrite($gz, $chunk);
            }
        }
        gzclose($gz);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            @unlink($destino);
            throw new RuntimeException("ERROR AL RESPALDAR {$database}: {$stderr}");
        }
    }

    private function empaquetar(string $tarPath, string $tmpDir): void
    {
        $binary = env('TAR_BINARY', 'tar');

        $cmd = [$binary, '-czf', $tarPath, '-C', $tmpDir, 'sql'];

        $storageAppDir = storage_path('app');
        if (is_dir($storageAppDir . DIRECTORY_SEPARATOR . 'public')) {
            $cmd = array_merge($cmd, ['-C', $storageAppDir, 'public']);
        }

        $descriptorspec = [
            0 => ['file', $this->devNull(), 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptorspec, $pipes, null, getenv());

        if (!is_resource($process)) {
            throw new RuntimeException('NO SE PUDO INICIAR tar.');
        }

        stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException("ERROR AL EMPAQUETAR BACKUP: {$stderr}");
        }
    }

    private function aplicarRetencion(string $baseDir, int $conservar = 3): void
    {
        $archivos = glob($baseDir . DIRECTORY_SEPARATOR . 'backup_*.tar.gz') ?: [];
        usort($archivos, fn($a, $b) => filemtime($b) <=> filemtime($a));

        foreach (array_slice($archivos, $conservar) as $antiguo) {
            @unlink($antiguo);
        }
    }

    private function asegurarDir(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException("NO SE PUDO CREAR EL DIRECTORIO: {$dir}");
        }
    }

    private function borrarDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($dir);
    }

    private function devNull(): string
    {
        return DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
    }
}
