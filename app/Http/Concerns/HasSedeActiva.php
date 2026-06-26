<?php

namespace App\Http\Concerns;

use App\Models\Tenant\Sede;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Única fuente de verdad para la "sede activa" del usuario.
 *
 * Reglas:
 *  - El admin (rol 'admin') ve y elige entre TODAS las sedes activas del tenant.
 *  - Los demás usuarios solo ven las sedes donde están asignados (sede_user), activas.
 *  - La sede activa se guarda en sesión (clave 'sede_activa_id') y se autosana si deja de ser válida.
 */
trait HasSedeActiva
{
    private const SESSION_KEY = 'sede_activa_id';

    /**
     * Sedes que el usuario puede elegir.
     */
    public function sedesDisponibles()
    {
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        if ($user->hasRole('admin')) {
            return Sede::where('status', 'ACTIVO')->orderBy('numero')->get();
        }

        return $user->sedes()
            ->where('sedes.status', 'ACTIVO')
            ->orderBy('numero')
            ->get();
    }

    /**
     * Id de la sede activa (de sesión, con fallback autosanado).
     */
    public function sedeActivaId()
    {
        $disponibles = $this->sedesDisponibles();

        if ($disponibles->isEmpty()) {
            return null;
        }

        $id = Session::get(self::SESSION_KEY);

        // Si no hay sede en sesión, o ya no es válida para el usuario → recae a una válida.
        if (! $id || ! $disponibles->contains('id', (int) $id)) {
            $user    = Auth::user();
            $default = $user ? $user->sedeDefault() : null;

            $id = ($default && $disponibles->contains('id', $default->id))
                ? $default->id
                : (optional($disponibles->firstWhere('es_principal', true))->id
                    ?? $disponibles->first()->id);

            Session::put(self::SESSION_KEY, $id);
        }

        return (int) $id;
    }

    /**
     * Modelo de la sede activa.
     */
    public function sedeActiva()
    {
        $id = $this->sedeActivaId();

        if (! $id) {
            return null;
        }

        return $this->sedesDisponibles()->firstWhere('id', $id);
    }

    /**
     * Id del almacén PRINCIPAL de la sede activa.
     *
     * Única fuente de verdad para los flujos de stock (lectura y movimiento):
     * en lugar del antiguo warehouse_id=1 hardcodeado, el stock se lee/mueve sobre
     * el almacén principal de la sede activa del usuario.
     *
     * Cada sede nace con su almacén principal (es_principal=true). Si por algún motivo
     * faltara, se corta con un error CLARO en vez de devolver null/0 y operar sobre el
     * almacén equivocado (lo que corrompería inventario).
     */
    public function almacenPrincipalSedeActivaId(): int
    {
        $warehouse = Warehouse::where('sede_id', $this->sedeActivaId())
            ->where('es_principal', true)
            ->first();

        if (! $warehouse) {
            throw new \RuntimeException('La sede activa no tiene almacén principal configurado.');
        }

        return $warehouse->id;
    }

    /**
     * Cambia la sede activa, validando que el usuario pueda usarla.
     *
     * @return bool true si cambió; false si la sede no es válida para el usuario.
     */
    public function cambiarSede($sedeId): bool
    {
        if (! $this->sedesDisponibles()->contains('id', (int) $sedeId)) {
            return false;
        }

        Session::put(self::SESSION_KEY, (int) $sedeId);

        return true;
    }
}
