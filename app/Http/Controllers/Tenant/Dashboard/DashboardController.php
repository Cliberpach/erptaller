<?php

namespace App\Http\Controllers\Tenant\Dashboard;

use App\Exports\Tenant\Dashboard\ProductoStockMinExport;
use App\Http\Controllers\Controller;
use App\Http\Services\Tenant\Dashboard\Dashboard\DashboardManager;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\View\View as View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class DashboardController extends Controller
{
    private   DashboardManager $s_dashboard;

    public function __construct()
    {
        $this->s_dashboard       =   new DashboardManager();
    }

    public function index():View{
        return view('dashboard.dashboard.index');
    }

/*
array:3 [ // app\Http\Controllers\General\PanelControl\Dashboard\DashboardController.php:16
  "establecimiento" => "MARKET"
  "anio" => "2025"
  "mes" => "4"
]
*/
    public function getData(Request $request){
        try {
            $establecimiento    =   $request->get('establecimiento');
            $anio               =   $request->get('anio');
            $mes                =   $request->get('mes');

            $res                =   $this->s_dashboard->getData($establecimiento,$anio,$mes);

            return response()->json(['success'=>true,'message'=>'DATOS OBTENIDOS','data'=>$res]);
        } catch (Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage(),'line'=>$th->getLine(),'file'=>$th->getFile()]);
        }
    }


    public function getStockMin(Request $request){

        $productos  =   $this->s_dashboard->getStockMin($request);

        return DataTables::of($productos)->make(true);
    }

    public function excelProductosStockMin(Request $request){

        $data       =   $this->s_dashboard->getStockMin($request);
        $company    =   Company::findOrFail(1);
        return Excel::download(new ProductoStockMinExport($data, $request,$company), 'stock_minimo_' . Carbon::now()->format('Y-m-d') . '.xlsx');

    }
}
