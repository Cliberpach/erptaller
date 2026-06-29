<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Model;

/**
 * Parámetros GLOBALES de la plataforma (BD central, no por tenant). Tabla key/value.
 * Legible desde el tenant porque vive en la conexión 'landlord' (central).
 * Ej.: api_placa_token, api_placa_url.
 */
class GlobalSetting extends Model
{
    protected $connection = 'landlord';

    protected $table = 'global_settings';

    protected $fillable = ['key', 'value'];

    /** Lee el valor de una clave global (o $default si no existe). */
    public static function valor(string $key, $default = null)
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    /** Crea/actualiza el valor de una clave global. */
    public static function setValor(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
