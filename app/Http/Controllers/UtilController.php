<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Department;
use App\Models\District;
use App\Models\Landlord\Company;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Landlord\GlobalSetting;
use App\Models\Landlord\TypeIdentityDocument;
use App\Models\Landlord\Year;
use App\Models\Province;
use App\Models\Tenant\BillingCompany;
use App\Models\Tenant\DocumentSerialization;
use App\Models\Tenant\Sale\PaymentCondition\PaymentCondition;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UtilController extends Controller
{

    public static function apiDni($dni)
    {

        try {
            $url = "https://apiperu.dev/api/dni/" . $dni;
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $token = 'c36358c49922c564f035d4dc2ff3492fbcfd31ee561866960f75b79f7d645d7d';
            $response = $client->get($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$token}"
                ]
            ]);
            $estado     =   $response->getStatusCode();
            $data       =   json_decode($response->getBody()->getContents());


            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'data' => $th->getMessage()]);
        }
    }

    public static function apiRuc($ruc)
    {
        try {
            $url = "https://apiperu.dev/api/ruc/" . $ruc;
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $token = 'c36358c49922c564f035d4dc2ff3492fbcfd31ee561866960f75b79f7d645d7d';
            $response = $client->get($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$token}"
                ]
            ]);
            $estado     =   $response->getStatusCode();
            $data       =   json_decode($response->getBody()->getContents());


            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'data' => $th->getMessage()]);
        }
    }

    public static function apiPlaca(string $placa)
    {
        // Declaradas antes del try -> en scope del catch para redactar el secreto.
        $token = $base = $bearer = null;

        try {
            // Config GLOBAL central (GlobalSetting fuerza conexión 'landlord').
            $token  = GlobalSetting::valor('api_placa_token');
            $base   = GlobalSetting::valor('api_placa_url');
            $bearer = GlobalSetting::valor('api_placa_bearer');

            // Guard: token + url son obligatorios (el bearer NO -> el API puede validar
            // solo con el token del path). Mensaje claro, sin exponer secretos.
            $faltan = [];
            if (empty($token)) $faltan[] = 'api_placa_token';
            if (empty($base))  $faltan[] = 'api_placa_url';
            if (! empty($faltan)) {
                return response()->json([
                    'success' => false,
                    'message' => 'API de Placas no configurada: falta ' . implode(', ', $faltan)
                        . '. Configurar en panel padre → Configuración → API de Placas.',
                ]);
            }

            // La API espera la placa SIN guion/espacios. claveComparacion normaliza (quita
            // guion/espacios + uppercase) -> con guion o sin guion, a la API le llega igual.
            // Solo afecta la URL de la API; el storage (con guion) y findPlate no se tocan.
            $placaApi = \App\Support\Placa::claveComparacion($placa);

            // URL desde config (sin hardcodear el dominio). Base ej: https://multijc.com/api/queryplaca
            $url = rtrim($base, '/') . '/' . $placaApi . '/' . $token;

            // Header Authorization SOLO si hay bearer configurado (opcional).
            $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
            if (! empty($bearer)) {
                $headers['Authorization'] = "Bearer {$bearer}";
            }

            $client   = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->get($url, ['headers' => $headers]);
            $data     = json_decode($response->getBody()->getContents());

            return response()->json(['success' => true, 'data' => $data, 'origin' => 'API']);
        } catch (Throwable $th) {
            // El token va en el PATH -> la excepción de Guzzle incluye la URL con el token.
            // Redactar token/bearer (secreto GLOBAL) y NO devolver file/line al browser.
            $msg = str_replace(array_filter([$token, $bearer]), '***', $th->getMessage());
            return response()->json(['success' => false, 'message' => $msg]);
        }
    }

    public static function donwloadFile($ubication)
    {

        if (File::exists($ubication)) {
            return response()->download($ubication);
        } else {
            abort(404, 'Archivo no encontrado');
        }
    }

    public static function getStock($product_id)
    {

        //======= VERIFICANDO SI EXISTE PRODUCTO EN EL ALMACÉN =======
        $warehouse_product          =   DB::select('select
                                        wp.stock
                                        from warehouse_products as wp
                                        where wp.warehouse_id = 1
                                        and wp.product_id = ?', [$product_id]);

        return $warehouse_product[0]->stock;
    }


    public static function getInventoryVehicleChecks()
    {
        $items = DB::connection('landlord')
            ->table('general_table_details as gtd')
            ->join('general_tables as gt', 'gt.id', '=', 'gtd.general_table_id')
            ->join('general_table_categories as gtc', 'gtc.id', '=', 'gtd.category_id')
            ->where('gtd.status', 'ACTIVO')
            ->where('gt.id', 1)
            ->select(
                'gt.id as general_table_id',
                'gt.name as general_table_name',
                'gtd.id as detail_id',
                'gtd.name as detail_name',
                'gtc.id as category_id',
                'gtc.name as category_name'
            )
            ->orderBy('gtc.id')
            ->orderBy('gtd.id')
            ->get();

        $groupedByCategoryId = [];

        foreach ($items as $item) {
            $categoryId = $item->category_id;

            if (!isset($groupedByCategoryId[$categoryId])) {
                $groupedByCategoryId[$categoryId] = [
                    'category_id'   => $item->category_id,
                    'category_name' => $item->category_name,
                    'items'         => []
                ];
            }

            $groupedByCategoryId[$categoryId]['items'][] = [
                'id'   => $item->detail_id,
                'name' => $item->detail_name
            ];
        }

        return array_values($groupedByCategoryId);
    }

    public static function getIdentityDocuments()
    {
        $tipos_documento    =   TypeIdentityDocument::where('status', 'ACTIVO')
            ->whereIn('id', [1, 3, 6])
            ->get();

        return $tipos_documento;
    }

    public static function getPositions()
    {
        $cargos    =   DB::table('positions as p')
            ->where('p.status', 'ACTIVO')
            ->get();

        return $cargos;
    }

    public static function getTechnicians()
    {

        $technicians   =   DB::table('users as u')
            ->join('model_has_roles as mhr', 'mhr.model_id', 'u.id')
            ->join('roles as r', 'r.id', 'mhr.role_id')
            ->join('collaborators as c','c.id','u.collaborator_id')
            ->where('r.name', 'TECNICO')
            ->select(
                'u.id',
                'u.name',
                'c.document_type_abbreviation',
                'c.document_number'
            )
            ->get();
        return $technicians;
    }

    public static function getYears()
    {
        $currentYear = date('Y');
        $years  =   Year::where('status', 'ACTIVE')
            ->where('description', '<=', $currentYear)
            ->orderByRaw('CAST(description AS UNSIGNED) DESC')
            ->get();
        return $years;
    }

    public static function getCategoriesProducts()
    {
        $categories =   Category::where('status', 'ACTIVE')->get();
        return $categories;
    }

    public static function getBrandsProducts()
    {
        $brands =   Brand::where('status', 'ACTIVE')->get();
        return $brands;
    }

    public static function getBanks()
    {
        $banks  =   GeneralTableDetail::where('general_table_id', 3)->where('status', 'ACTIVO')->get();
        return $banks;
    }

    public static function getInvoiceTypes()
    {
        $invoice_types  =   GeneralTableDetail::where('general_table_id', 4)->where('status', 'ACTIVO')->get();
        return $invoice_types;
    }

    /**
     * Tipos de comprobante OFRECIBLES en "crear venta": solo BOLETA (03), FACTURA (01)
     * y TICKET (50, documento interno). Excluye NOTA DE VENTA / NC / ND / GUÍA (no se
     * crean acá: NC/ND son ajustes, Guía es traslado, NV se reemplaza por TICKET).
     * getInvoiceTypes() (los 7) queda INTACTO para la config de series.
     */
    public static function getInvoiceTypesVenta()
    {
        return GeneralTableDetail::where('general_table_id', 4)
            ->where('status', 'ACTIVO')
            ->whereIn('symbol', ['03', '01', '50'])
            ->get();
    }

    /**
     * Tipos de comprobante OFRECIBLES al facturar una ORDEN DE TRABAJO: solo BOLETA (03) y
     * FACTURA (01) -> el comprobante fiscal del trabajo. Sin TICKET/GUÍA/NC/ND/NV (no aplican).
     * getInvoiceTypes() (los 7) queda INTACTO para la config de series.
     */
    public static function getInvoiceTypesOT()
    {
        return GeneralTableDetail::where('general_table_id', 4)
            ->where('status', 'ACTIVO')
            ->whereIn('symbol', ['03', '01'])
            ->get();
    }

    public function isActiveInvoiceType(int $id)
    {
        try {

            $invoice_type   =   GeneralTableDetail::findOrFail($id);
            $exists         =   DocumentSerialization::where('document_type_id', $id)->exists();

            if (!$exists) {
                throw new Exception($invoice_type->name . ", NO ESTÁ ACTIVO EN LA EMPRESA");
            }

            return response()->json(['success' => true, 'message' => $invoice_type->name . ",ACTIVO EN LA EMPRESA"]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public static function getPaymentConditions()
    {
        $payment_conditions =   PaymentCondition::where('status', 'ACTIVO')->get();
        return $payment_conditions;
    }

    public static function getUnitsMeasurement()
    {
        $data   =   GeneralTableDetail::where('general_table_id', 5)->get();
        return $data;
    }

    /**
     * PASO 4 (transversal): cuentas asociadas a un método (combo dependiente del cobro de
     * venta y del egreso). Lee el pivote payment_method_accounts. EFECTIVO -> 0 cuentas.
     * Label por método: YAPE -> titular + celular; otros (TRANSFERENCIA/POS) -> titular + n° cuenta.
     */
    public function paymentAccounts(int $method)
    {
        $metodo = DB::table('payment_methods')->where('id', $method)->first();
        if (! $metodo) {
            return response()->json(['needs_account' => false, 'data' => []]);
        }

        $cuentas = DB::table('bank_accounts as ba')
            ->join('payment_method_accounts as pma', 'pma.bank_account_id', '=', 'ba.id')
            ->where('pma.payment_method_id', $method)
            ->where('ba.status', 'ACTIVO')
            ->select('ba.id', 'ba.holder', 'ba.phone', 'ba.account_number')
            ->get();

        $usaCelular = in_array(strtoupper($metodo->description), ['YAPE']);

        $data = $cuentas->map(function ($c) use ($usaCelular) {
            $detalle = $usaCelular ? ($c->phone ?? '') : ($c->account_number ?? '');
            return ['id' => $c->id, 'label' => trim($c->holder . ' - ' . $detalle, ' -')];
        })->values();

        return response()->json(['needs_account' => $data->isNotEmpty(), 'data' => $data]);
    }
    
    public static function saveFileFromLandlord(UploadedFile $file, string $file_name, $folder): string
    {
        $path           =   $folder;
        $extension      =   $file->getClientOriginalExtension();
        $file_name      =   $file_name . '.' . $extension;

        Storage::disk('public')->putFileAs($path, $file, $file_name);

        return $path . '/' . $file_name;
    }

    public static function deleteFile(string $path)
    {
        $cleanPath = str_replace('storage/', '', $path);

        Storage::disk('public')->delete($cleanPath);
    }

    public static function getDepartments()
    {
        return Department::all();
    }

    public static function getProvinces()
    {
        return Province::all();
    }

    public static function getDistricts()
    {
        return District::all();
    }
}
