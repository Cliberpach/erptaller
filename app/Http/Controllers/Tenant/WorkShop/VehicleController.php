<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Exports\Tenant\Inventory\Brand\BrandFormatExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Inventory\Brand\BrandImportExcelRequest;
use App\Http\Requests\Tenant\Inventory\Brand\BrandStoreRequest;
use App\Http\Requests\Tenant\Inventory\Brand\BrandUpdateRequest;
use App\Imports\Inventory\Brand\BrandImport;
use Illuminate\Support\Facades\DB;
use App\Models\Brand;
use App\Models\CompanyInvoice;
use App\Models\Department;
use App\Models\District;
use App\Models\Province;
use App\Models\Tenant\TypeIdentityDocument;
use Throwable;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class VehicleController extends Controller
{
    //
    public function index()
    {
        return view('workshop.vehicles.index');
    }

    public function getVehiculos(Request $request)
    {
        $vehicles  =   DB::table('vehicles as v')
            ->join('brandsv as b', 'b.id', 'v.brand_id')
            ->join('models as m', 'm.id', 'v.model_id')
            ->join('model_years as my', 'my.id', 'v.year_id')
            ->join('colors as c', 'c.id', 'v.color_id')
            ->select(
                'v.id',
                'v.name as customer_name',
                'v.plate',
                'b.description as brand_name',
                'm.description as model_name',
                'my.description as year_name',
                'c.description as color_name',
                'v.observation'
            );

        return DataTables::of($vehicles)->make(true);
    }

    public function create(Request $request)
    {

        $types_identity_documents   =   TypeIdentityDocument::where('status', 'ACTIVO')->get();
        $departments        =   Department::all();
        $districts          =   District::all();
        $provinces          =   Province::all();
        $company_invoice    =   CompanyInvoice::find(1);

        return view('workshop.vehicles.create',
        compact('types_identity_documents','departments','districts','provinces','company_invoice'));
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
