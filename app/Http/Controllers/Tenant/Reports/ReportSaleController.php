<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Exports\Tenant\ReportSaleExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportSaleController extends Controller
{
    public function index(){
        return view('reports.report_sales.index');
    }

    public function getReporteVenta(Request $request){

        $kardex =   $this->queryReporteVenta($request);

        return DataTables::of($kardex)->make(true);
    
    }

    public function queryReporteVenta(Request $request){

        $report_sale    =   DB::table('sales_documents_details as sdd')
                            ->join('sales_documents as sd','sd.id','sdd.sale_document_id')
                            ->select(
                                'sdd.sale_document_id',
                                DB::raw("CONCAT(sd.serie, '-', sd.correlative) as document"), 
                                'sdd.product_name',
                                'sdd.category_name',
                                'sdd.brand_name',
                                'sdd.quantity',
                                'sdd.price_sale',
                                'sdd.amount',
                            )
                            ->orderByDesc('sdd.created_at');

        if($request->get('date_start')){
            $report_sale = $report_sale->whereRaw('DATE(sdd.created_at) >= ?', [$request->get('date_start')]);
        }
                        
        if($request->get('date_end')){
            $report_sale = $report_sale->whereRaw('DATE(sdd.created_at) <= ?', [$request->get('date_end')]);
        }

        $report_sale =   $report_sale->get();

        return $report_sale;
    }

    public function excel(Request $request){

        $report_sale =   $this->queryReporteVenta($request);

        $report_sale->transform(function ($item) {
            unset($item->sale_document_id); 
            return $item;
        });

        return Excel::download(new ReportSaleExport($report_sale,$request), 
        'reporte_ventas_' . Carbon::now()->format('Y_m_d_H_i_s') . '.xlsx');

    }

    public function pdf(Request $request){

        $company                =   Company::find(1);
        
        $report_sale            =   $this->queryReporteVenta($request);

        $report_sale->transform(function ($item) {
            unset($item->sale_document_id); 
            return $item;
        });

        
        $pdf = Pdf::loadview('reports.report_sales.pdf.pdf', [
                'company'               =>  $company,
                'report_sale'           =>  $report_sale,
                'filters'               =>  $request
              
            ])->setPaper('a4', 'portrait');


        return $pdf->stream('reporte_ventas_' . Carbon::now()->format('Y_m_d_H_i_s') .'.pdf');
    }
}
