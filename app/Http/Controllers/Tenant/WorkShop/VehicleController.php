<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Exports\Tenant\Inventory\Brand\BrandFormatExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilController;
use App\Http\Requests\Tenant\WorkShop\VehicleStoreRequest;
use App\Http\Requests\Tenant\WorkShop\VehicleUpdateRequest;
use App\Http\Services\Tenant\WorkShop\Vehicles\VehicleManager;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyInvoice;
use App\Models\Department;
use App\Models\District;
use App\Models\Landlord\Brand;
use App\Models\Landlord\Color;
use App\Models\Landlord\Customer;
use App\Models\Landlord\ModelV;
use App\Models\Province;
use App\Models\Tenant\WorkShop\Vehicle;
use Illuminate\Support\Facades\Session;
use Throwable;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class VehicleController extends Controller
{
    private VehicleManager $s_vehicle;

    public function __construct()
    {
        $this->s_vehicle    =   new VehicleManager();
    }

    public function index()
    {
        return view('workshop.vehicles.index');
    }

    public function getVehiculos(Request $request)
    {
        $vehicles = DB::table('vehicles as v')
            ->join(DB::raw('erptaller.customers as cu'), 'cu.id', '=', 'v.customer_id')
            ->join(DB::raw('erptaller.brandsv as b'), 'b.id', '=', 'v.brand_id')
            ->join(DB::raw('erptaller.models as m'), 'm.id', '=', 'v.model_id')
            ->leftJoin(DB::raw('erptaller.years as y'), 'y.id', '=', 'v.year_id')
            ->join(DB::raw('erptaller.colors as c'), 'c.id', '=', 'v.color_id')
            ->select(
                'v.id',
                DB::raw('CONCAT(cu.type_document_abbreviation,":",cu.document_number,"-",cu.name) as customer_name'),
                'v.plate',
                'b.description as brand_name',
                'm.description as model_name',
                'y.description as year_name',
                'c.description as color_name',
                'v.observation'
            )->where('v.status', 'ACTIVO');

        return DataTables::of($vehicles)
            ->filterColumn('customer_name', function ($query, $keyword) {
                $query->whereRaw(
                    "CONCAT(cu.type_document_abbreviation,':',cu.document_number,'-',cu.name) LIKE ?",
                    ["%{$keyword}%"]
                );
            })
            ->make(true);
    }

    public function create(Request $request)
    {

        $types_identity_documents   =   UtilController::getIdentityDocuments();
        $departments                =   Department::all();
        $districts                  =   District::all();
        $provinces                  =   Province::all();
        $company_invoice            =   CompanyInvoice::find(1);
        $years                      =   UtilController::getYears();
        $colors                     =   Color::where('status', 'ACTIVE')->get();

        return view(
            'workshop.vehicles.create',
            compact('types_identity_documents', 'departments', 'districts', 'provinces', 'company_invoice', 'years', 'colors')
        );
    }

    /*
array:7 [ // app\Http\Controllers\Tenant\WorkShop\VehicleController.php:81
  "_token" => "gvszdFqMWspbR6jfBvQlpAnHEoPLYYHZCir3uY0A"
  "_method" => "POST"
  "client_id" => "2"
  "plate" => "T3B033"
  "model_id" => "200"
  "year_id" => "80"
  "vin" => "MA3FB31S290018614"
  "serie" => "MA3FB31S290018614"
  "observation" => "test"
]
*/
    public function store(VehicleStoreRequest $request)
    {
        DB::beginTransaction();
        try {

            $vehicle    =   $this->s_vehicle->store($request->toArray());

            Session::flash('message_success', 'VEHÍCULO REGISTRADO CON ÉXITO');

            DB::commit();
            return response()->json(['success' => true, 'message' => 'VEHÍCULO REGISTRADO CON ÉXITO', 'vehicle' => $vehicle]);
        } catch (Throwable $th) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function edit(int $id)
    {
        $vehicle                    =   Vehicle::findOrFail($id);
        $customer                   =   Customer::findOrFail($vehicle->customer_id);
        $customerFormatted = [
            'id'        => $customer->id,
            'full_name' => $customer->type_document_abbreviation . ':' . $customer->document_number . '-' . $customer->name,
            'email'     => $customer->email,
        ];
        $brand  =   Brand::findOrFail($vehicle->brand_id);
        $model  =   ModelV::findOrFail($vehicle->model_id);
        $modelFormatted =   [
            'id'    =>  $model->id,
            'text'  =>  $brand->description . ' - ' . $model->description
        ];

        $types_identity_documents   =   UtilController::getIdentityDocuments();
        $departments                =   Department::all();
        $districts                  =   District::all();
        $provinces                  =   Province::all();
        $company_invoice            =   CompanyInvoice::find(1);
        $years                      =   UtilController::getYears();
        $colors                     =   Color::where('status', 'ACTIVE')->get();

        return view(
            'workshop.vehicles.edit',
            compact(
                'types_identity_documents',
                'departments',
                'districts',
                'provinces',
                'company_invoice',
                'years',
                'colors',
                'vehicle',
                'customerFormatted',
                'modelFormatted'
            )
        );
    }

    /*
array:8 [ // app\Http\Controllers\Tenant\WorkShop\VehicleController.php:159
  "_token" => "gvszdFqMWspbR6jfBvQlpAnHEoPLYYHZCir3uY0A"
  "_method" => "PUT"
  "client_id" => "2"
  "plate" => "TR3423"
  "model_id" => "147"
  "year_id" => "98"
  "color_id" => "1"
  "observation" => "t"
  "vin" => "MA3FB31S290018614"
  "serie" => "MA3FB31S290018614"
]
*/
    public function update(VehicleUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $vehicle    =   $this->s_vehicle->update($request->toArray(), $id);

            Session::flash('message_success', 'VEHÍCULO ACTUALIZADO CON ÉXITO');

            DB::commit();
            return response()->json(['success' => true, 'message' => 'VEHÍCULO ACTUALIZADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }


    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {

            $this->s_vehicle->destroy($id);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'VEHÍCULO ELIMINADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function getFormatExcel(Request $request)
    {
        return Excel::download(new BrandFormatExport(), 'formato_import_marcas.xlsx');
    }


    /*
{
"success":true,
"data":{
    "mensaje":"SUCCESS",
    "data":{
        "mensaje":"Encontrado",
        "placa":"T3B033",
        "marca":"KIA",
        "modelo":"PICANTO",
        "serie":"KNABE511AFT844784",
        "color":"",
        "motor":"",
        "vin":"KNABE511AFT844784"
    }
},
"model_insert":true,
"model":{id,brand_id,description},
"origin":"API"
}
*/
    public function searchPlate(string $placa)
    {
        DB::connection('landlord')->beginTransaction();
        try {

            $res    =   $this->s_vehicle->searchPlate($placa);
            DB::connection('landlord')->commit();
            return $res;
        } catch (Throwable $th) {
            DB::connection('landlord')->rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    /**
     * Buscar clientes (para TomSelect server-side)
     */
    public function searchVehicle(Request $request)
    {
        try {
            $query          = trim($request->get('q', ''));
            $customer_id    = $request->get('customer_id', null);

            $vehicles = Vehicle::from('vehicles as v')
                ->join('erptaller.models as m', 'm.id', 'v.model_id')
                ->join('erptaller.brandsv as b', 'b.id', 'v.brand_id');

            if ($query) {
                $vehicles->where('v.plate', 'LIKE', "%{$query}%");
            }

            if ($customer_id) {
                $vehicles->where('v.customer_id', $customer_id);
            }

            $vehicles = $vehicles->limit(20)
                ->get(['v.id', 'm.description as model_name', 'v.plate', 'b.description as brand_name']);

            $data = $vehicles->map(fn($v) => [
                'id' => $v->id,
                'text' => "{$v->plate}",
                'subtext' => "{$v->brand_name}-{$v->model_name}",
            ]);

            return response()->json(['success' => true, 'data' => $data, 'message' => 'VEHÍCULOS OBTENIDOS']);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
