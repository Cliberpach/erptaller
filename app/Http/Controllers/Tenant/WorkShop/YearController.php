<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\Year\YearStoreRequest;
use App\Http\Requests\Landlord\Year\YearUpdateRequest;
use App\Http\Services\Landlord\WorkShop\Years\YearManager;
use App\Models\Landlord\Year;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Throwable;

class YearController extends Controller
{
    private YearManager $s_year;

    public function __construct()
    {
        $this->s_year  =   new YearManager();
    }

    public function index()
    {
        $brands =   Year::where('status', 'ACTIVE')->get();
        return view('workshop.years.index', compact('brands'));
    }

    public function getYears(Request $request)
    {
        $years = DB::connection('landlord')
            ->table('years as y')
            ->select(
                'y.id',
                'y.description'
            )
            ->where('y.status', 'ACTIVE');

        return DataTables::of($years)->toJson();
    }

    public function getYear(int $id)
    {
        try {

            $year  =   $this->s_year->getYear($id);

            return response()->json(['success' => true, 'message' => 'AÑO OBTENIDO', 'data' => $year]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /*
array:4 [ // app\Http\Controllers\Tenant\WorkShop\YearController.php:81
  "_token" => "whjxe4Khd8ttu83I4cdp4MshzLG5d1HbDuXliTWt"
  "_method" => "POST"
  "description" => "2022"
  "model_id" => "2"
]
*/
    public function store(YearStoreRequest $request)
    {
        DB::beginTransaction();

        try {

            $year  =   $this->s_year->store($request->toArray());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'AÑO REGISTRADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /*
array:4 [ // app\Http\Controllers\Tenant\WorkShop\YearController.php:113
  "_token" => "whjxe4Khd8ttu83I4cdp4MshzLG5d1HbDuXliTWt"
  "description_edit" => "2023"
  "model_id_edit" => "2"
  "_method" => "PUT"
]
*/
    public function update(YearUpdateRequest $request, int $id)
    {
        DB::beginTransaction();
        try {

            $year  =   $this->s_year->update($id, $request->toArray());

            DB::commit();

            return response()->json(['success' => true, 'message' => 'AÑO ACTUALIZADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }


    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {

            $year  =   $this->s_year->destroy($id);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'AÑO ELIMINADO CON ÉXITO']);
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
