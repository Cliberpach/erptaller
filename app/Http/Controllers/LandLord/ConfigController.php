<?php

namespace App\Http\Controllers\LandLord;

use App\Http\Controllers\Controller;
use App\Models\Landlord\GlobalSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Configuración GLOBAL de la plataforma (panel padre / landlord). Guarda parámetros
 * en la BD central (global_settings). Acceso: super-admin landlord (auth:web).
 */
class ConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** API de Placas: form con TOKEN + URL (global, para todos los tenants). */
    public function apiPlacas(): View
    {
        return view('configuracion.api_placas', [
            'api_placa_token' => GlobalSetting::valor('api_placa_token'),
            'api_placa_url'   => GlobalSetting::valor('api_placa_url'),
        ]);
    }

    public function apiPlacasUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'api_placa_token' => ['nullable', 'string', 'max:255'],
            'api_placa_url'   => ['nullable', 'string', 'max:255'],
        ]);

        GlobalSetting::setValor('api_placa_token', $data['api_placa_token'] ?? null);
        GlobalSetting::setValor('api_placa_url', $data['api_placa_url'] ?? null);

        return redirect()
            ->route('landlord.configuracion.api_placas')
            ->with('message_success', 'Configuración del API de Placas guardada.');
    }
}
