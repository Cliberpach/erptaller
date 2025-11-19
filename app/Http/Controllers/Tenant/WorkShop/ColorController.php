<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\Color\ColorStoreRequest;
use App\Http\Requests\Landlord\Color\ColorUpdateRequest;
use App\Http\Services\Landlord\WorkShop\Colors\ColorManager;
use App\Models\Landlord\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Throwable;

class ColorController extends Controller
{

    private ColorManager $s_color;

    public function __construct()
    {
        $this->s_color  =   new ColorManager();
    }

    public function index()
    {
        return view('workshop.colors.index');
    }

    public function getColores(Request $request)
    {

        $colores = Color::where('status', 'ACTIVE');

        return DataTables::of($colores)->toJson();
    }

    public function getColor(int $id)
    {
        try {

            $color  =   $this->s_color->getColor($id);

            return response()->json(['success' => true, 'message' => 'COLOR OBTENIDO', 'data' => $color]);
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
    public function store(ColorStoreRequest $request)
    {
        DB::beginTransaction();

        try {

            $color  =   $this->s_color->store($request->toArray());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'COLOR REGISTRADO CON ÉXITO']);
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

    public function update(ColorUpdateRequest $request, int $id)
    {
        DB::beginTransaction();
        try {

            $color  =   $this->s_color->update($id, $request->toArray());

            DB::commit();

            return response()->json(['success' => true, 'message' => 'COLOR ACTUALIZADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }


    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {

            $color  =   $this->s_color->destroy($id);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'COLOR ELIMINADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
