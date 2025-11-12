<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\Tenant\InventoryExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
{
    public function index()
    {
        $products   =   DB::select('select 
                        p.id,
                        p.name 
                        from products as p
                        where p.estado = "ACTIVO"');
        return view('inventory.inventory.index',compact('products'));
    }

    public function getInventory(Request $request){

        $inventory   =   $this->queryInventory($request);

        return DataTables::of($inventory)->make(true);
    
    }

    public static function queryInventory(Request $request){

        $inventory  =    DB::table('products as p')
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
                            'p.purchase_price'
                        )
                        ->where('wp.warehouse_id','1')
                        ->where('p.estado','!=','ANULADO')
                        ->orderByDesc('p.created_at');

        if($request->get('filter_stock') == '2'){  
            $inventory =   $inventory->where('wp.stock','0');
        }

        if($request->get('filter_stock') == '3'){  
            $inventory =   $inventory->where('wp.stock','>','0');
        }

        if($request->get('filter_stock') == '4'){  
            $inventory =   $inventory->where('wp.stock','<','p.stock_min');
        }

        $inventory =   $inventory->get();

        return $inventory;
    }

    public function excel(Request $request){

        $inventory =   $this->queryInventory($request);

        $inventory->transform(function ($item) {
            unset($item->id); 
            return $item;
        });

        return Excel::download(new InventoryExport($inventory,$request), 'inventario.xlsx');

    }

    public function pdf(Request $request){

        $company                =   Company::find(1);
        
        $report_inventory            =   $this->queryInventory($request);

        $report_inventory->transform(function ($item) {
            unset($item->id); 
            return $item;
        });

        
        $pdf = Pdf::loadview('inventory.inventory.pdf.pdf', [
                'company'               =>  $company,
                'report_inventory'      =>  $report_inventory,
                'filters'               =>  $request
              
            ])->setPaper('a4', 'portrait');


        return $pdf->stream('reporte_inventario_' . Carbon::now()->format('Y_m_d_H_i_s') .'.pdf');
    }
}
