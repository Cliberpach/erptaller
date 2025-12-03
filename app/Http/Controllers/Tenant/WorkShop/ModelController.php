<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\Model\ModelStoreRequest;
use App\Http\Requests\Landlord\Model\ModelUpdateRequest;
use App\Http\Services\Landlord\WorkShop\Models\ModelManager;
use App\Models\Landlord\Brand;
use App\Models\Landlord\ModelV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Throwable;

class ModelController extends Controller
{

    private ModelManager $s_model;

    public function __construct()
    {
        $this->s_model  =   new ModelManager();
    }

    public function index()
    {
        $brands =   Brand::where('status', 'ACTIVE')->get();
        return view('workshop.models.index', compact('brands'));
    }

    public function getModelos(Request $request)
    {

        $marcas = DB::connection('landlord')
            ->table('models as m')
            ->join('brandsv as b', 'b.id', '=', 'm.brand_id')
            ->select(
                'm.id',
                'm.description',
                'b.description as brand_name'
            )
            ->where('m.status', 'ACTIVE');


        return DataTables::of($marcas)->toJson();
    }

    public function getModelo(int $id)
    {
        try {

            $modelo  =   $this->s_model->getModel($id);

            return response()->json(['success' => true, 'message' => 'MODELO OBTENIDO', 'data' => $modelo]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /*
array:4 [ // app\Http\Controllers\Tenant\WorkShop\ModelController.php:79
  "_token" => "whjxe4Khd8ttu83I4cdp4MshzLG5d1HbDuXliTWt"
  "_method" => "POST"
  "description" => "als23"
  "brand_id" => "1"
]
*/
    public function store(ModelStoreRequest $request)
    {
        DB::beginTransaction();

        try {

            $modelo  =   $this->s_model->store($request->toArray());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'MODELO REGISTRADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }


        if ($request->has('fetch') && $request->input('fetch') == 'SI') {
            return response()->json(['message' => 'success',    'data' => $color]);
        }

        Session::flash('success', 'Color creado.');
        return redirect()->route('almacenes.colores.index')->with('guardar', 'success');
    }

    /*
array:4 [ // app\Http\Controllers\Tenant\WorkShop\ModelController.php:102
  "_token" => "whjxe4Khd8ttu83I4cdp4MshzLG5d1HbDuXliTWt"
  "description_edit" => "ALS23"
  "brand_id_edit" => "2"
  "_method" => "PUT"
]
*/
    public function update(ModelUpdateRequest $request, int $id)
    {
        DB::beginTransaction();
        try {

            $modelo  =   $this->s_model->update($id, $request->toArray());

            DB::commit();

            return response()->json(['success' => true, 'message' => 'MODELO ACTUALIZADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }


    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {

            $modelo  =   $this->s_model->destroy($id);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'MODELO ELIMINADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function searchModel(Request $request)
    {
        $term = $request->input('q');

        $results = ModelV::query()
            ->select('models.id', 'models.description as model', 'brandsv.description as brand')
            ->join('brandsv', 'brandsv.id', '=', 'models.brand_id')
            ->where('models.status', 'ACTIVE')
            ->where('brandsv.status', 'ACTIVE')
            ->when($term, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('models.description', 'like', "%{$term}%")
                        ->orWhere('brandsv.description', 'like', "%{$term}%");
                });
            })
            ->orderBy('brandsv.description')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => "{$item->brand} - {$item->model}"
                ];
            });

        return response()->json($results);
    }
}
