<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\WorkShop\Service\ServiceUpdateRequest;
use App\Http\Services\Tenant\WorkShop\Quotes\QuoteManager;
use App\Models\Company;
use App\Models\Landlord\Year;
use App\Models\Tenant\Warehouse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Throwable;

class QuoteController extends Controller
{
    private QuoteManager $s_quote;

    public function __construct()
    {
        $this->s_quote  =   new QuoteManager();
    }

    public function index()
    {
        return view('workshop.quotes.index');
    }

    public function getQuotes(Request $request)
    {
        $quotes = DB::connection('tenant')
            ->table('quotes as q')
            ->select(
                'q.id',
                DB::raw('CONCAT(q.customer_type_document_abbreviation,":",q.customer_document_number,"-",q.customer_name) as customer_name'),
                'q.plate',
                'q.warehouse_name',
                'q.total',
                'q.create_user_name',
                'q.status',
                'q.expiration_date',
                'q.created_at'
            )
            ->where('q.status', '<>', 'ANULADO');

        return DataTables::of($quotes)->toJson();
    }

    public function getService(int $id)
    {
        try {

            $year  =   $this->s_quote->getService($id);

            return response()->json(['success' => true, 'message' => 'SERVICIO OBTENIDO', 'data' => $year]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function create(Request $request)
    {
        $igv        =   round(Company::find(1)->igv, 2);
        $warehouses =   Warehouse::where('estado', 'ACTIVO')->get();
        return view('workshop.quotes.create', compact('igv', 'warehouses'));
    }

/*
array:17 [ // app\Http\Controllers\Tenant\WorkShop\QuoteController.php:91
  "_token" => "4olnC7YeO8JO17Yg4QWlat1MAQSJzc8VyqntCFyC"
  "_method" => "POST"
  "warehouse_id" => "1"
  "client_id" => "2"
  "vehicle_id" => "6"
  "plate" => "TR3423"
  "expiration_date" => "2025-11-28"
  "product_id" => "1"
  "product_quantity" => "3"
  "product_price" => "14.99"
  "dt-quotes-products_length" => "10"
  "service_id" => "1"
  "service_quantity" => "1"
  "service_price" => "30.00"
  "dt-quotes-services_length" => "10"
  "lst_products" => "[{"id":1,"name":"BUJÍA 20 MM","category_name":"BUJÍAS","brand_name":"ASUS","sale_price":14.99,"quantity":3,"total":44.97}]"
  "lst_services" => "[{"id":1,"name":"LAVADO DE AUTOS","sale_price":30,"quantity":1,"total":30}]"
]
*/
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
          
            $quote  =   $this->s_quote->store($request->toArray());

            Session::flash('success', 'COTIZACIÓN REGISTRADA CON ÉXITO');
            //DB::commit();
            return response()->json(['success' => true, 'message' => 'COTIZACIÓN REGISTRADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'file' => $th->getFile(), 'line' => $th->getLine()]);
        }
    }

    /*
array:5 [ // app\Http\Controllers\Tenant\WorkShop\ServiceController.php:94
  "_token" => "WNiCYcelXPamrMrwCEwMpkGmbqb3gcz0HVwsnn68"
  "name_edit" => "LAVADO DE AUTOS"
  "price_edit" => "21.00"
  "description_edit" => "TEST"
  "_method" => "PUT"
]
*/
    public function update(ServiceUpdateRequest $request, int $id)
    {
        DB::beginTransaction();
        try {

            $service  =   $this->s_quote->update($request->validated(), $id);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'SERVICIO ACTUALIZADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }


    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {

            $service  =   $this->s_quote->destroy($id);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'SERVICIO ELIMINADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function getYearsModel(int $model)
    {
        try {

            $years  =   Year::where('model_id', $model)->where('status', 'ACTIVE')->get();

            return response()->json(['success' => true, 'message' => 'AÑOS OBTENIDOS', 'years' => $years]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
