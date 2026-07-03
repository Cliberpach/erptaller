<?php

namespace App\Http\Controllers\Tenant\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Configuration\ConfigurationRequest;
use App\Http\Services\Tenant\Maintenance\TenantResetService;
use App\Models\Tenant;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Maintenance\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ConfigurationController extends Controller
{
    public function index()
    {
        $configuration  =   Configuration::all();
        $company        =   Company::find(1);
        return view('maintenance.configuration.index', compact('configuration', 'company'));
    }

    /** Symbols cuyo valor es un select de texto (no un toggle 0/1) + los valores que aceptan. */
    private const SELECT_CONFIGS = [
        'AMB_GRE' => ['DEMO', 'PRODUCCION'],
    ];

    /** Symbols cuyo valor es numérico libre (no toggle, no select fijo). */
    private const NUMERIC_CONFIGS = [
        'UMB_DNI' => ['min' => 0],
    ];

    public function store(ConfigurationRequest $request)
    {
        DB::beginTransaction();
        $environment = null;

        try {

            $data   =   $request->all();
            $configurations =   Configuration::all();
            foreach ($configurations as $configuration) {
                $field              =   'configuration_' . $configuration->id;
                $configuration_bd   =   Configuration::findOrFail($configuration->id);

                if (array_key_exists($configuration->symbol, self::SELECT_CONFIGS)) {
                    $value = $data[$field] ?? null;
                    if (!in_array($value, self::SELECT_CONFIGS[$configuration->symbol], true)) {
                        throw new \Exception("VALOR INVÁLIDO PARA {$configuration->symbol}");
                    }
                    $configuration_bd->property = $value;

                    if ($configuration->symbol === 'AMB_GRE') {
                        $environment = $value;
                    }
                } elseif (array_key_exists($configuration->symbol, self::NUMERIC_CONFIGS)) {
                    $value = $data[$field] ?? null;
                    $min   = self::NUMERIC_CONFIGS[$configuration->symbol]['min'];
                    if (!is_numeric($value) || (float) $value < $min) {
                        throw new \Exception("VALOR INVÁLIDO PARA {$configuration->symbol}");
                    }
                    $configuration_bd->property = (string) $value;
                } else {
                    $configuration_bd->property = array_key_exists($field, $data) ? '1' : '0';
                }

                $configuration_bd->save();
            }

            //====== IGV DE LA EMPRESA (companies.igv, no vive en la tabla configuration) ======
            if ($request->filled('igv')) {
                $igv = $request->input('igv');
                if (!is_numeric($igv) || (float) $igv < 0 || (float) $igv > 100) {
                    throw new \Exception("EL PORCENTAJE DE IGV DEBE ESTAR ENTRE 0 Y 100");
                }
                Company::findOrFail(1)->update(['igv' => $igv]);
            }

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }

        if ($environment !== null) {
            try {
                $this->syncGreenterEnvironmentToLandlord($environment);
            } catch (Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => 'CONFIGURACIÓN GUARDADA, PERO FALLÓ LA SINCRONIZACIÓN CON LANDLORD: ' . $th->getMessage(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'CONFIGURACIÓN GUARDADA']);
    }

    /** Refleja el ambiente Greenter en company_invoice (landlord), leído por el listado de empresas. */
    private function syncGreenterEnvironmentToLandlord(string $environment): void
    {
        $tenant_id = Tenant::current()->id;

        $company_id = DB::connection('landlord')->table('companies')->where('tenant_id', $tenant_id)->value('id');

        if ($company_id) {
            DB::connection('landlord')->table('company_invoice')->where('company_id', $company_id)->update(['environment' => $environment]);
        }
    }

    /** Descarga un backup (.sql.gz) de la BD del tenant actual. Solo admin. */
    public function backup(TenantResetService $service)
    {
        if (!auth()->user()?->hasRole('admin')) {
            abort(403);
        }

        return $service->backup();
    }

    /** Elimina documentos de venta/compra/OT conservando catálogo, productos y clientes. Solo admin. */
    public function limpiarDocumentos(Request $request, TenantResetService $service)
    {
        if (!auth()->user()?->hasRole('admin')) {
            abort(403);
        }

        if (trim((string) $request->input('confirmacion')) !== 'ELIMINAR') {
            return response()->json(['success' => false, 'message' => 'DEBE ESCRIBIR "ELIMINAR" PARA CONFIRMAR']);
        }

        try {
            $service->limpiarDocumentos();
            return response()->json(['success' => true, 'message' => 'DOCUMENTOS ELIMINADOS. CATÁLOGO, PRODUCTOS Y CLIENTES CONSERVADOS']);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /** Elimina TODA la información del tenant y re-siembra solo datos base (seeders). Solo admin. */
    public function limpiarTodo(Request $request, TenantResetService $service)
    {
        if (!auth()->user()?->hasRole('admin')) {
            abort(403);
        }

        if (trim((string) $request->input('confirmacion')) !== 'ELIMINAR') {
            return response()->json(['success' => false, 'message' => 'DEBE ESCRIBIR "ELIMINAR" PARA CONFIRMAR']);
        }

        try {
            $service->limpiarTodo();
            return response()->json([
                'success' => true,
                'message' => 'TENANT REINICIADO. INICIE SESIÓN CON EL USUARIO ADMINISTRADOR POR DEFECTO (admin@gmail.com / 123456789) Y CAMBIE LA CLAVE',
                'logout'  => true,
            ]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
