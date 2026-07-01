<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $guarded = [];

    public static function booted()
    {
        static::creating(fn(Tenant $tenant) => $tenant->createDatabase($tenant));
        static::created(fn(Tenant $tenant) => $tenant->runMigrationsSeeders($tenant));
    }

    public function createDatabase($tenant)
    {
        $database_name = "tenancy_" . $tenant->domain;
        $database = Str::of($database_name)->replace('.', '_')->lower()->__toString();

        $tenant->database = $database;

        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
        $db = DB::select($query, [$database]);
        if (empty($db)) {
            DB::connection('tenant')->statement("CREATE DATABASE {$database};");
        }
    }

    public function runMigrationsSeeders($tenant)
    {
        $tenant->refresh();

        $t        = microtime(true);
        $template = 'tenancy_template_erptaller';
        $newDb    = $tenant->database;
        $user     = config('database.connections.landlord.username');
        $pass     = config('database.connections.landlord.password');
        $host     = config('database.connections.landlord.host', '127.0.0.1');

        Log::channel('tenant_store')->info('CLONE INICIANDO', [
            'tenant_id' => $tenant->id,
            'newDb'     => $newDb,
            'os'        => PHP_OS,
        ]);

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isWindows) {
            $this->cloneViaPDO($template, $newDb, $user, $pass, $host);
        } else {
            $dump = "mysqldump -u{$user} -p'{$pass}' -h{$host} --routines --no-create-db {$template}";
            $restore = "mysql -u{$user} -p'{$pass}' -h{$host} {$newDb}";

            $command = "{$dump} | {$restore} 2>&1";

            exec($command, $output, $resultCode);

            $outputText = implode("\n", $output);

            if ($resultCode !== 0) {
                Log::channel('tenant_store')->error('CLONE ERROR', [
                    'output' => $outputText,
                    'code'   => $resultCode,
                ]);

                throw new \Exception("Error clonando template: {$outputText}");
            }
        }

        Log::channel('tenant_store')->info('CLONE DONE', [
            'seconds' => round(microtime(true) - $t, 2),
        ]);
    }

    private function cloneViaPDO(string $template, string $newDb, string $user, string $pass, string $host): void
    {
        $port = config('database.connections.landlord.port', '3306');

        $pdo = new \PDO(
            "mysql:host={$host};port={$port};charset=utf8mb4",
            $user,
            $pass,
            [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]
        );

        $tables = $pdo->query("SHOW FULL TABLES FROM `{$template}`")
            ->fetchAll(\PDO::FETCH_NUM);

        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

        foreach ($tables as $row) {
            $tableName = $row[0];
            $tableType = $row[1];

            if ($tableType === 'VIEW') {
                $createRow = $pdo->query("SHOW CREATE VIEW `{$template}`.`{$tableName}`")
                    ->fetch(\PDO::FETCH_ASSOC);
                $createSql = $createRow['Create View'];
            } else {
                $createRow = $pdo->query("SHOW CREATE TABLE `{$template}`.`{$tableName}`")
                    ->fetch(\PDO::FETCH_ASSOC);
                $createSql = $createRow['Create Table'];
            }

            $pdo->exec("USE `{$newDb}`");
            $pdo->exec($createSql);

            if ($tableType === 'BASE TABLE') {
                $pdo->exec("INSERT INTO `{$newDb}`.`{$tableName}` SELECT * FROM `{$template}`.`{$tableName}`");
            }
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

        // Copiar rutinas
        $routines = $pdo->query("
        SELECT ROUTINE_NAME, ROUTINE_TYPE
        FROM information_schema.ROUTINES
        WHERE ROUTINE_SCHEMA = '{$template}'
    ")->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($routines as $routine) {
            try {
                $type      = $routine['ROUTINE_TYPE'];
                $name      = $routine['ROUTINE_NAME'];
                $createRow = $pdo->query("SHOW CREATE {$type} `{$template}`.`{$name}`")
                    ->fetch(\PDO::FETCH_ASSOC);
                $key       = "Create {$type}";

                if (isset($createRow[$key])) {
                    $pdo->exec("USE `{$newDb}`");
                    $pdo->exec($createRow[$key]);
                }
            } catch (\Exception $e) {
                Log::channel('tenant_store')->warning("Error copiando rutina {$routine['ROUTINE_NAME']}: " . $e->getMessage());
            }
        }
    }
}
