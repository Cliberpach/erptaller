<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\WorkShop\Service\ServiceStoreRequest;
use App\Http\Requests\Tenant\WorkShop\Service\ServiceUpdateRequest;
use App\Http\Services\Tenant\WorkShop\Services\ServiceManager;
use App\Models\Landlord\Year;
use App\Models\Tenant\WorkShop\Service;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Throwable;

class ServiceController extends Controller
{
    private ServiceManager $s_service;

    public function __construct()
    {
        $this->s_service  =   new ServiceManager();
    }

    public function index()
    {
        $brands =   Year::where('status', 'ACTIVE')->get();
        return view('workshop.services.index', compact('brands'));
    }

    public function getServices(Request $request)
    {
        $years = DB::connection('tenant')
            ->table('services as s')
            ->select(
                's.id',
                's.description',
                's.price',
                's.name'
            )
            ->where('s.status', 'ACTIVE');

        return DataTables::of($years)->toJson();
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
            return response()->json(['success' => true, 'message' => 'SERVICIO REGISTRADO CON ÉXITO','service'=>$service]);
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

            $service  =   $this->s_service->update($request->validated(), $id);

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

    /**
     * Buscar clientes (para TomSelect server-side)
     */
    public function searchService(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $services = Service::from('services as s')
            ->where(function ($q) use ($query) {
                $q->where('s.name', 'LIKE', "%{$query}%");
            })->limit(20)
            ->get(
                [
                    's.id',
                    's.name',
                    's.price',
                ]
            );

        $data = $services->map(fn($s) => [
            'id' => $s->id,
            'text' => "{$s->name}",
            'subtext' => "S/ " . number_format($s->price, 2),
            'sale_price' => $s->price,
            'name'  =>  $s->name
        ]);

        return response()->json(['data' => $data]);
    }
}
