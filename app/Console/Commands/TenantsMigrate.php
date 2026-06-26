<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Wrapper SEGURO para migrar las BDs de los tenants.
 *
 * Fija SIEMPRE `--database=tenant` y `--path=database/migrations/tenant`. Esto evita
 * el error de correr `tenants:artisan "migrate ..."` SIN `--database=tenant`, que hace
 * que migrate use la conexión `default` (landlord/central) y termine creando el esquema
 * de tenant EN LA CENTRAL en vez de en la BD del tenant (y mintiendo en migrate:status).
 *
 * Uso:
 *   php artisan tenants:migrate                 # todos los tenants
 *   php artisan tenants:migrate --tenant=1      # un tenant
 *   php artisan tenants:migrate --seed          # corre seeders
 *   php artisan tenants:migrate --pretend       # muestra el SQL sin ejecutar
 */
class TenantsMigrate extends Command
{
    protected $signature = 'tenants:migrate
                            {--tenant= : Id del tenant (opcional; por defecto todos)}
                            {--seed : Ejecutar seeders despues de migrar}
                            {--pretend : Mostrar el SQL sin ejecutarlo}';

    protected $description = 'Migra las BDs de tenants forzando --database=tenant (wrapper seguro de tenants:artisan)';

    public function handle(): int
    {
        // Comando de migracion con las banderas obligatorias fijadas.
        $migrate = 'migrate --database=tenant --path=database/migrations/tenant --force';

        if ($this->option('seed')) {
            $migrate .= ' --seed';
        }
        if ($this->option('pretend')) {
            $migrate .= ' --pretend';
        }

        $params = ['artisanCommand' => $migrate];

        if ($this->option('tenant')) {
            $params['--tenant'] = [$this->option('tenant')];
        }

        $this->info("Ejecutando: tenants:artisan \"$migrate\""
            . ($this->option('tenant') ? " (tenant {$this->option('tenant')})" : ' (todos los tenants)'));

        return $this->call('tenants:artisan', $params);
    }
}
