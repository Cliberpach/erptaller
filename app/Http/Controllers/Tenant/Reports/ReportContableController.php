<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Exports\Tenant\ReportContableExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DocumentType;
use App\Models\Tenant\DocumentSerialization;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportContableController extends Controller
{
    public function index(){

        $documents = DocumentType::whereIn('id', [1, 3, 6, 7, 8, 9, 80])->get();
        return view('reports.report_contable.index',compact('documents'));
    }

/*
"date_start"        => null
"date_end"          => null
"filter_document"   => null
*/
    public function getReporteContable(Request $request){

        $report_contable =   $this->queryReporteContable($request);

        return DataTables::of($report_contable)->make(true);
    }

    public function queryReporteContable(Request $request){

        $report_contable    =   [];
        if($request->get('filter_document') == '1' || $request->get('filter_document') == '3' || $request->get('filter_document') == '80'){
            
            $report_contable    =   DB::table('sales_documents_details as sdd')
                                    ->join('sales_documents as sd','sd.id','sdd.sale_document_id')
                                    ->select(
                                        'sd.customer_name',
                                        'sd.type_sale_name',
                                        DB::raw("CONCAT(sd.serie, '-', sd.correlative) as document"), 
                                        'sd.created_at',
                                        DB::raw("FORMAT(sd.subtotal, 2) as subtotal"), 
                                        DB::raw("FORMAT(sd.igv_amount, 2) as igv_amount"), 
                                        DB::raw("FORMAT(sd.igv_percentage, 2) as igv_percentage"), 
                                        DB::raw("FORMAT(sd.total, 2) as total"), 
                                        'sdd.product_name',
                                        'sdd.category_name',
                                        'sd.estado',
                                    )
                                    ->orderByDesc('sd.created_at');

            $report_contable = $report_contable->whereRaw('sd.type_sale_code = ?', [$request->get('filter_document')]);
        }

        if($request->get('filter_document') == 'VENTAS'){
            
            $report_contable    =   DB::table('sales_documents_details as sdd')
                                    ->join('sales_documents as sd','sd.id','sdd.sale_document_id')
                                    ->select(
                                        'sd.customer_name',
                                        'sd.type_sale_name',
                                        DB::raw("CONCAT(sd.serie, '-', sd.correlative) as document"), 
                                        'sd.created_at',
                                        DB::raw("FORMAT(sd.subtotal, 2) as subtotal"), 
                                        DB::raw("FORMAT(sd.igv_amount, 2) as igv_amount"), 
                                        DB::raw("FORMAT(sd.igv_percentage, 2) as igv_percentage"), 
                                        DB::raw("FORMAT(sd.total, 2) as total"), 
                                        'sdd.product_name',
                                        'sdd.category_name',
                                        'sd.estado',
                                    )
                                    ->orderByDesc('sd.created_at');

            $report_contable = $report_contable->whereRaw('sd.type_sale_code != ?', ['80']);

        }
       

        if($request->get('date_start')){
            $report_contable = $report_contable->whereRaw('DATE(sdd.created_at) >= ?', [$request->get('date_start')]);
        }
                        
        if($request->get('date_end')){
            $report_contable = $report_contable->whereRaw('DATE(sdd.created_at) <= ?', [$request->get('date_end')]);
        }

     
        if($request->get('filter_document') == '1' || $request->get('filter_document') == '3' || $request->get('filter_document') == '80' || $request->get('filter_document') == 'VENTAS'){
            $report_contable =   $report_contable->get();
        }

        return $report_contable;
    }

    public function excel(Request $request){

        $report_contable =   $this->queryReporteContable($request);

        $report_contable->transform(function ($item) {
            return $item;
        });

        return Excel::download(new ReportContableExport($report_contable,$request), 
        'reporte_contable_' . Carbon::now()->format('Y_m_d_H_i_s') . '.xlsx');

    }

    public function pdf(Request $request){

        $company                =   Company::find(1);
        
        $report_contable          =   $this->queryReporteContable($request);

        // $report_contable->transform(function ($item) {
        //     return $item;
        // });
        
        $pdf = Pdf::loadview('reports.report_contable.pdf.pdf', [
                'company'               =>  $company,
                'report_contable'       =>  $report_contable,
                'filters'               =>  $request
              
                ])->setPaper('a4', 'landscape');


        return $pdf->stream('reporte_campos_' . Carbon::now()->format('Y_m_d_H_i_s') .'.pdf');
    }


}
