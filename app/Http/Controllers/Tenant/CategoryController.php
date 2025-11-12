<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\Tenant\Inventory\Category\CategoryFormatExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Inventory\Category\CategoryImportExcelRequest;
use App\Http\Requests\Tenant\Inventory\Category\CategoryStoreRequest;
use App\Http\Requests\Tenant\Inventory\Category\CategoryUpdateRequest;
use App\Imports\Inventory\Category\CategoryImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use Throwable;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class CategoryController extends Controller
{
    //
    public function index()
    {
        $categoryList = DB::select('select * from categories');
        return view('category.index', compact('categoryList'));
    }

    public function getAll(Request $request)
    {
        $categories =   Category::select(
            'id',
            'name'
        )
        ->where('status', 'ACTIVE');

        return DataTables::of($categories)->make(true);
    }

    /*
array:2 [ // app\Http\Controllers\Tenant\CategoryController.php:36
  "_token" => "3NmSrQu5G7rQ9G7WXocJqZMn8R0EGaMWWXIo5Rhy"
  "name" => "ASDASD"
]
*/
    public function store(CategoryStoreRequest $request)
    {

        DB::beginTransaction();
        try {

            $data           =   $request->validated();
            $data['name']   =   mb_strtoupper($data['name'], 'UTF-8');

            $category       =   Category::create($data);

            // Responder con JSON en caso de éxito
            if ($request->ajax()) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'Categoría creada exitosamente.',
                    'data' => [
                        'id' => $category->id,
                        'name' => $category->name
                    ]
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CATEGORÍA REGISTRADA CON ÉXITO']);
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

    public function update(CategoryUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data           =   $request->validated();
            $data['name']   =   $request->get('name_edit');

            $category   =   Category::findOrFail($id);
            $category->update($data);

            // Responder con JSON en caso de éxito
            if ($request->ajax()) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'Categoría actualizada exitosamente.',
                    'data' => [
                        'id' => $category->id,
                        'name' => $category->name,
                    ]
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CATEGORÍA ACTUALIZADA CON ÉXITO']);
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

            $category           =   Category::findOrFail($id);
            $category->status   =   'INACTIVE';
            $category->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CATEGORÍA ELIMINADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function getFormatExcel(Request $request)
    {
        return Excel::download(new CategoryFormatExport(), 'formato_import_categorias.xlsx');
    }

/*
array:1 [ // app\Http\Controllers\Tenant\CategoryController.php:146
  "categorias_import_excel" =>
Illuminate\Http\UploadedFile {#1885
*/
    public function importCategoriesExcel(CategoryImportExcelRequest $request)
    {
        DB::beginTransaction();
        try {

            $import = new CategoryImport();

            Excel::import($import, $request->file('categorias_import_excel'));

            $resultado = $import->getResultados();

            if ($resultado->con_errores) {
                return response()->json(['success' => false, 'message' => 'ERRORES EN EL EXCEL', 'resultado' => $resultado]);
            } else {
                $lstCategorias  =   $resultado->listadoCategorias;
                foreach ($lstCategorias as $categoria_excel) {
                    $categoria              =   new Category();
                    $categoria->name        =   mb_strtoupper($categoria_excel['nombre'], 'UTF-8');
                    $categoria->save();
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
