<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\Year\YearStoreRequest;
use App\Http\Requests\Landlord\Year\YearUpdateRequest;
use App\Http\Requests\Tenant\WorkShop\Service\ServiceStoreRequest;
use App\Http\Requests\Tenant\WorkShop\Service\ServiceUpdateRequest;
use App\Http\Services\Tenant\WorkShop\Services\ServiceManager;
use App\Models\Landlord\Year;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Throwable;

class QuoteController extends Controller
{
    private ServiceManager $s_service;

    public function __construct()
    {
        $this->s_service  =   new ServiceManager();
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
            ->where('q.status','<>', 'ANULADO');

        return DataTables::of($quotes)->toJson();
    }

    public function getService(int $id)
    {
        try {

            $year  =   $this->s_service->getService($id);

            return response()->json(['success' => true, 'message' => 'SERVICIO OBTENIDO', 'data' => $year]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function create(Request $request){
        return view('workshop.quotes.create');
    }

/*
array:5 [ // app\Http\Controllers\Tenant\WorkShop\ServiceController.php:70
  "_token" => "WNiCYcelXPamrMrwCEwMpkGmbqb3gcz0HVwsnn68"
  "_method" => "POST"
  "name" => "LAVADO DE AUTOS"
  "price" => "21"
  "description" => "TEST"
]
*/
    public function store(ServiceStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $service  =   $this->s_service->store($request->toArray());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'SERVICIO REGISTRADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
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

            $service  =   $this->s_service->update($request->validated(),$id);

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

            $service  =   $this->s_service->destroy($id);

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
