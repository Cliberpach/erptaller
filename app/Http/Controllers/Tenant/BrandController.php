<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\Tenant\Inventory\Brand\BrandFormatExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Inventory\Brand\BrandImportExcelRequest;
use App\Http\Requests\Tenant\Inventory\Brand\BrandStoreRequest;
use App\Http\Requests\Tenant\Inventory\Brand\BrandUpdateRequest;
use App\Imports\Inventory\Brand\BrandImport;
use Illuminate\Support\Facades\DB;
use App\Models\Brand;
use Throwable;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class BrandController extends Controller
{
    //
    public function index()
    {
        $brandList = Brand::all();
        return view('brand.index', compact('brandList'));
    }

    public function getAll(Request $request)
    {
        $categories =   Brand::select(
            'id',
            'name'
        )
            ->where('status', 'ACTIVE');

        return DataTables::of($categories)->make(true);
    }

    // Almacenar una nueva marca
    public function store(BrandStoreRequest $request)
    {

        try {
            $data           =   $request->validated();
            $data['name']   =   mb_strtoupper($data['name'], 'UTF-8');

            $brand          =   Brand::create($data);

            // Responder con JSON en caso de éxito
            if ($request->ajax()) {
                return response()->json(['type' => 'success', 'data' => $brand, 'message' => 'Marca registrada exitosamente.']);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'MARCA REGISTRADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['type' => 'error', 'message' => $th->getMessage()]);
            }

            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    // Actualizar una marca existente
    public function update(BrandUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data           =   $request->validated();
            $data['name']   =   $request->get('name_edit');

            $category   =   Brand::findOrFail($id);
            $category->update($data);

            // Responder con JSON en caso de éxito
            if ($request->ajax()) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'Marca actualizada exitosamente.',
                    'data' => [
                        'id' => $category->id,
                        'name' => $category->name,
                    ]
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'MARCA ACTUALIZADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'type' => 'error',
                    'message' => $th->getMessage()
                ]);
            }

            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $brand           =   Brand::findOrFail($id);
            $brand->status   =   'INACTIVE';
            $brand->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'MARCA ELIMINADA CON ÉXITO']);
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
array:1 [ // app\Http\Controllers\Tenant\CategoryController.php:146
  "marcas_import_excel" =>
Illuminate\Http\UploadedFile {#1885
*/
    public function importExcel(BrandImportExcelRequest $request)
    {
        DB::beginTransaction();
        try {

            $import = new BrandImport();

            Excel::import($import, $request->file('marcas_import_excel'));

            $resultado = $import->getResultados();

            if ($resultado->con_errores) {
                return response()->json(['success' => false, 'message' => 'ERRORES EN EL EXCEL', 'resultado' => $resultado]);
            } else {
                $lstMarcas  =   $resultado->listadoMarcas;
                foreach ($lstMarcas as $categoria_excel) {
                    $brand              =   new Brand();
                    $brand->name        =   mb_strtoupper($categoria_excel['name'], 'UTF-8');
                    $brand->save();
                }
                DB::commit();
                return response()->json(['success' => true, 'message' => 'EXCEL IMPORTADO CON ÉXITO', 'resultado' => $resultado]);
            }
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
