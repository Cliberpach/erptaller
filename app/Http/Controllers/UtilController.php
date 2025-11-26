<?php

namespace App\Http\Controllers;

use App\Models\Landlord\Company;
use App\Models\User;
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

            $token  =   Company::find(1)->token_placa;

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
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
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

    public static function getIdentityDocuments(){
        $tipos_documento    =   DB::connection('landlord')
                                ->table('general_table_details as gtd')
                                ->where('gtd.status','ACTIVO')
                                ->where('gtd.general_table_id',2)
                                ->get();

       return $tipos_documento;
    }

    public static function getPositions(){
        $cargos    =   DB::table('positions as p')
                                ->where('p.status','ACTIVO')
                                ->get();

       return $cargos;
    }

    public static function getTechnicians(){

        $technicians   =   DB::table('users as u')
                        ->join('model_has_roles as mhr','mhr.model_id','u.id')
                        ->join('roles as r','r.id','mhr.role_id')
                        ->where('r.name','TECNICO')
                        ->select(
                            'u.id',
                            'u.name'
                        )
                        ->get();
        return $technicians;
    }
}
