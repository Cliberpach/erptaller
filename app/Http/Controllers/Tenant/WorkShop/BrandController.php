<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\Brand\BrandStoreRequest;
use App\Http\Requests\Landlord\Brand\BrandUpdateRequest;
use App\Http\Services\Landlord\WorkShop\Brands\BrandManager;
use App\Models\Landlord\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Throwable;

class BrandController extends Controller
{

    private BrandManager $s_brand;

    public function __construct()
    {
        $this->s_brand  =   new BrandManager();
    }

    public function index()
    {
        return view('workshop.brands.index');
    }

    public function getMarcas(Request $request)
    {

        $marcas = Brand::where('status', 'ACTIVE');

        return DataTables::of($marcas)->toJson();
    }

    public function getMarca(int $id)
    {
        try {

            $marca  =   $this->s_brand->getMarca($id);

            return response()->json(['success' => true, 'message' => 'MARCA OBTENIDA', 'data' => $marca]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

/*
array:4 [ // app\Http\Controllers\Tenant\WorkShop\ColorController.php:59
  "_token" => "t8QA76dov2nXgUTBWOU0lm2jxPnO0tvobE5eEc7T"
  "_method" => "POST"
  "description" => "AZUL"
  "codigo" => "#0062ff"
]
*/
    public function store(BrandStoreRequest $request)
    {
        DB::beginTransaction();

        try {

            $marca  =   $this->s_brand->store($request->toArray());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'MARCA REGISTRADA CON ÉXITO']);
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

    public function update(BrandUpdateRequest $request, int $id)
    {
        DB::beginTransaction();
        try {

            $marca  =   $this->s_brand->update($id, $request->toArray());

            DB::commit();

            return response()->json(['success' => true, 'message' => 'MARCA ACTUALIZADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }


    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {

            $marca  =   $this->s_brand->destroy($id);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'MARCA ELIMINADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
