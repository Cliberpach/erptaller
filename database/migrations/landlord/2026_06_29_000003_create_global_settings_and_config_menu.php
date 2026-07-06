<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Config GLOBAL de la plataforma (BD central):
 *  - tabla global_settings (key/value) para parámetros globales (no por tenant).
 *  - semilla de las claves del API de placas (token + url) vacías.
 *
 * El menú landlord "Configuración" lo siembra únicamente ModuleSeeder
 * (database/seeders/landlord/ModuleSeeder.php); no se crea aquí para evitar
 * módulos duplicados en instalaciones fresh.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Tabla key/value de config global.
        if (! Schema::hasTable('global_settings')) {
            Schema::create('global_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 120)->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // 2) Semilla de claves del API de placas (vacías; se editan desde el panel).
        foreach (['api_placa_token', 'api_placa_url'] as $key) {
            DB::table('global_settings')->updateOrInsert(
                ['key' => $key],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
