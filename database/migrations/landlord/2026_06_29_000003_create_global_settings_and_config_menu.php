<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Config GLOBAL de la plataforma (BD central):
 *  - tabla global_settings (key/value) para parámetros globales (no por tenant).
 *  - menú landlord "Configuración" (top-level) -> "API de Placas".
 *  - semilla de las claves del API de placas (token + url) vacías.
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

        // 3) Menú landlord: módulo top-level "Configuración" + hijo "API de Placas".
        $modId = DB::table('modules')->where('description', 'Configuración')->where('show', 'landlord')->value('id');
        if (! $modId) {
            $modId = DB::table('modules')->insertGetId([
                'description' => 'Configuración',
                'order'       => 2,
                'show'        => 'landlord',
                'icon'        => 'configuration-svgrepo-com.svg',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $exists = DB::table('module_children')
            ->where('module_id', $modId)
            ->where('route_name', 'configuracion.api_placas')
            ->exists();
        if (! $exists) {
            DB::table('module_children')->insert([
                'module_id'  => $modId,
                'description' => 'API de Placas',
                'route_name' => 'configuracion.api_placas',
                'permission' => null,
                'order'      => 1,
                'show'       => 'landlord',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4) Limpiar cache del menú landlord (se reconstruye en el próximo request).
        Cache::forget('modules_menu_landlord_landlord');
        Cache::forget('modules_search_landlord_landlord');
    }

    public function down(): void
    {
        DB::table('module_children')->where('route_name', 'configuracion.api_placas')->delete();
        DB::table('modules')->where('description', 'Configuración')->where('show', 'landlord')->delete();
        Schema::dropIfExists('global_settings');
        Cache::forget('modules_menu_landlord_landlord');
        Cache::forget('modules_search_landlord_landlord');
    }
};
