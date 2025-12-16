<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Landlord\Company;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Landlord\TypeIdentityDocument;
use App\Models\Landlord\Year;
use App\Models\Tenant\BillingCompany;
use App\Models\Tenant\DocumentSerialization;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
        try {

            $token = Company::first()->token_placa;

            $url = "https://multijc.com/api/queryplaca/" . $placa . "/" . $token;

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

            return response()->json(['success' => true, 'data' => $data, 'origin' => 'API']);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
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
            ->where('r.name', 'TECNICO')
            ->select(
                'u.id',
                'u.name'
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

    public static function getCategoriesProducts(){
        $categories =   Category::where('status','ACTIVE')->get();
        return $categories;
    }

    public static function getBrandsProducts(){
        $brands =   Brand::where('status','ACTIVE')->get();
        return $brands;
    }

    public static function getBanks(){
        $banks  =   GeneralTableDetail::where('general_table_id',3)->where('status','ACTIVO')->get();
        return $banks;
    }

    public static function getInvoiceTypes(){
        $invoice_types  =   GeneralTableDetail::where('general_table_id',4)->where('status','ACTIVO')->get();
        return $invoice_types;
    }

    public function isActiveInvoiceType(int $id){
        try {

            $invoice_type   =   GeneralTableDetail::findOrFail($id);
            $exists         =   DocumentSerialization::where('document_type_id',$id)->exists();

            if(!$exists){
                throw new Exception($invoice_type->name.", NO ESTÁ ACTIVO EN LA EMPRESA");
            }

            return response()->json([ 'success'=>true,'message'=>$invoice_type->name.",ACTIVO EN LA EMPRESA"]);

        } catch (Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }
    }
}
