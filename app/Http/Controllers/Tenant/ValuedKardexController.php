<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ValuedKardexController extends Controller
{
    public function index(){
        return view('inventory.valued_kardex.index');
    }

    public function getValuedKardex(Request $request){

        $valued_kardex  =   $this->queryValuedKardex($request);

        return DataTables::of($valued_kardex)->make(true);
    
    }

    public static function queryValuedKardex($request){

        $valued_kardex  =    DB::table('products as p')
                            ->join('categories as c','c.id','p.category_id')
                            ->join('brands as b','b.id','p.brand_id')
                            ->leftJoin('warehouse_products as wp','wp.product_id','p.id')
                            ->select(
                                'p.id',
                                'p.name as product_name',
                                'c.name as category_name',
                                'b.name as brand_name',
                                'p.stock_min',
                                'wp.stock as current_stock',
                                'p.sale_price',
                                'p.purchase_price',
                                DB::raw('ROUND(wp.stock * p.sale_price, 2) AS value')                         
                            )
                            ->where('wp.warehouse_id','1')
                            ->where('p.estado','!=','ANULADO')
                            ->orderByDesc('p.created_at');

        if($request->get('date_start')){
            $valued_kardex = $valued_kardex->whereRaw('DATE(p.created_at) >= ?', [$request->get('date_start')]);
        }
            
        if($request->get('date_end')){
            $valued_kardex = $valued_kardex->whereRaw('DATE(p.created_at) <= ?', [$request->get('date_end')]);
        }

        $valued_kardex =   $valued_kardex->get();

        return $valued_kardex;
    }

    public function pdf(Request $request){

        $company                =   Company::find(1);
        
        $report_kardex_value          =   $this->queryValuedKardex($request);

        $report_kardex_value->transform(function ($item) {
            unset($item->id); 
            return $item;
        });

        
        $pdf = Pdf::loadview('inventory.valued_kardex.pdf.pdf', [
                'company'               =>  $company,
                'report_kardex_value'   =>  $report_kardex_value,
                'filters'               =>  $request
              
            ])->setPaper('a4', 'landscape');


        return $pdf->stream('reporte_kardex_valorizado' . Carbon::now()->format('Y_m_d_H_i_s') .'.pdf');
    }
}
