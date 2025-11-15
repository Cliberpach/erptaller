<?php

namespace App\Http\Controllers;

use App\Models\Landlord\Company;
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

            $url = "https://multijc.com/api/queryplaca/" . $placa."/".$token;

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
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'data' => $th->getMessage()]);
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
}
