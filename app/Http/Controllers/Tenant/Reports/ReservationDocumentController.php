<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\QRController;
use App\Models\Company;
use App\Models\Landlord\Customer;
use App\Models\Tenant\ReservationDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReservationDocumentController extends Controller
{
    public function index(){
        return view('reports.reservation_documents.index');
    }

    public function getReservationDocuments(Request $request){

        $reservation_documents  =   DB::table('reservation_documents as rd')
                                    ->select(
                                        'rd.id', 
                                        'rd.created_at as fecha_registro',
                                        'rd.customer_name',
                                        'rd.serie',
                                        'rd.correlative',
                                        DB::raw("CONCAT(rd.serie, '-', rd.correlative) AS doc"),
                                        'rd.type_sale_name',
                                        DB::raw("FORMAT(rd.total, 2) AS total"),
                                        'rd.estado',
                                        'rd.type_sale_code',
                                        'rd.ruta_xml',
                                        'rd.ruta_cdr'
                                    )
                                    ->where('rd.estado','!=','ANULADO');
                     
        if ($request->get('date_start')) {
            $reservation_documents = $reservation_documents->whereRaw('DATE(rd.created_at) >= ?', [$request->get('date_start')]);
        }
                            
        if ($request->get('date_end')) {
            $reservation_documents = $reservation_documents->whereRaw('DATE(rd.created_at) <= ?', [$request->get('date_end')]);
        }

        $reservation_documents  =   $reservation_documents->get();
    
        return DataTables::of($reservation_documents)->make(true);
    
    }

/*
    sale_document_id:1
*/ 
    public function send_sunat(Request $request){

        try {

            $sale_document_id   =   $request->get('sale_document_id');

            if(!$sale_document_id){
                throw new Exception("NO SE ENCONTRÓ EL ID DEL COMPROBANTE DE PAGO");
            }
    
            $sale_document = ReservationDocument::find($sale_document_id);
    
            if(!$sale_document){
                throw new Exception("COMPROBANTE DE RESERVA NO ENCONTRADO EN LA BD");
            }
        
        } catch (\Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }

        $request->merge([
            'type' => "RESERVATION_DOCUMENT"
        ]);
        $res    =   InvoiceController::send_sunat($request);
        return $res;

    }

    public function pdf_voucher($sale_id){
        try {

            $company                =   Company::find(1);
            $sale_document          =   ReservationDocument::find($sale_id);
            $sale_document_detail   =   DB::select('select * 
                                        from reservation_documents_detail as rdd
                                        where rdd.reservation_document_id = ?',[$sale_id]);

            $data_qr                =   (object)['ruc_emisor'       =>  $company->ruc,
                                                'tipo_comprobante'  =>  $sale_document->type_sale_code,
                                                'serie'             =>  $sale_document->serie,
                                                'correlativo'       =>  $sale_document->correlative,
                                                'mto_total_igv'     =>  number_format($sale_document->igv_amount, 2, '.', ''),
                                                'total'             =>  number_format($sale_document->total, 2, '.', ''),
                                                'fecha_emision'     =>  \Carbon\Carbon::parse($sale_document->created_at)->format('Y-m-d'),
                                                'tipo_documento_adquiriente'    =>  $sale_document->customer_document_code,
                                                'nro_documento_adquieriente'    =>  $sale_document->customer_document_number];

            $res_qr         =   QRController::generateQr(json_encode($data_qr));
            $res_qr         =   $res_qr->getData();

            if($res_qr->success){
                $sale_document->ruta_qr =   $res_qr->data->ruta_qr;
                $sale_document->update();
            }

            $customer       =   Customer::find($sale_document->customer_id);
            
            $pdf = PDF::loadview('reports.report_fields.pdf.pdf_document', [
                    'company'               =>  $company,
                    'sale_document'         =>  $sale_document,
                    'customer'              =>  $customer,
                    'sale_document_detail'  =>  $sale_document_detail
                ])->setPaper([0, 0, 226.772, 651.95]);

            return $pdf->stream($sale_document->serie . '-' . $sale_document->correlative . '.pdf');

        } catch (\Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage(),'line'=>$th->getLine()]);
        }
    }

    public function downloadXml($sale_document_id){

        $sale_document  =   ReservationDocument::find($sale_document_id);

        $ruta_xml       =   $sale_document->ruta_xml;

        $filePath       = public_path("{$ruta_xml}");

        if (File::exists($filePath)) {
            return response()->download($filePath);
        } else {
            abort(404, 'Archivo no encontrado');
        }

    }

    public function downloadCdr($sale_document_id){
        
        $sale_document  =   ReservationDocument::find($sale_document_id);

        $ruta_cdr       =   $sale_document->ruta_cdr;

        $filePath       = public_path("{$ruta_cdr}");

        if (File::exists($filePath)) {
            return response()->download($filePath);
        } else {
            abort(404, 'Archivo no encontrado');
        }

    }


}
