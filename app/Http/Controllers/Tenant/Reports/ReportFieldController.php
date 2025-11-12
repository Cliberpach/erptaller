<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Exports\Tenant\ReportFieldExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\NumberToLettersController;
use App\Http\Controllers\Tenant\QRController;
use App\Http\Controllers\Tenant\SaleController;
use App\Http\Requests\Tenant\ReservationDocument\ReservationDocumentStoreRequest;
use App\Models\Booking;
use App\Models\Company;
use App\Models\DocumentType;
use App\Models\Field;
use App\Models\Landlord\Customer;
use App\Models\Tenant\ReservationDocument;
use App\Models\Tenant\ReservationDocumentDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportFieldController extends Controller
{
    public function index(){
        return view('reports.report_fields.index');
    }

    public function getReporteCampos(Request $request){

        $report_fields =   $this->queryReporteCampos($request);


        return DataTables::of($report_fields)->make(true);
    
    }

    public function queryReporteCampos(Request $request)
    {
        $report_fields  =   DB::connection('tenant')->table('bookings as b')
                            ->join('booking_detail as bd', 'b.id', '=', 'bd.booking_id')
                            ->join('fields as f', 'f.id', '=', 'b.field_id')
                            ->join('schedules as s', 's.id', '=', 'b.schedule_id')
                            ->leftJoin('reservation_documents as rd','rd.id','b.reservation_document_id')
                            ->select(
                                'b.id',
                                'b.created_at',
                                'b.date',
                                'f.field as field_name',
                                'b.customer_id', 
                                's.description as schedule_description',
                                DB::raw('SUM(bd.payment) as amount'),
                                'b.status',
                                'b.reservation_document_id',
                                DB::raw('CONCAT(rd.serie,"-",rd.correlative) as reservation_document_nro') 
                            )
                            ->groupBy('b.id','b.created_at','b.date','f.field','b.customer_id',
                            's.description','b.status','b.reservation_document_id','rd.serie','rd.correlative')
                            ->orderByDesc('b.created_at');

        if ($request->get('date_start')) {
            $report_fields = $report_fields->whereRaw('DATE(b.created_at) >= ?', [$request->get('date_start')]);
        }

        if ($request->get('date_end')) {
            $report_fields = $report_fields->whereRaw('DATE(b.created_at) <= ?', [$request->get('date_end')]);
        }

        $fields         = $report_fields->get();

        $customer_ids   = $fields->pluck('customer_id');

        $customers  =   DB::connection('landlord')->table('customers as c')
                        ->whereIn('c.id', $customer_ids)
                        ->get();

        $fields = $fields->map(function ($field) use ($customers) {

            $customer = $customers->firstWhere('id', $field->customer_id);
                
            $orderedField = [
                'id'                    =>  $field->id,
                'created_at'            =>  $field->created_at,
                'date'                  =>  $field->date,
                'field_name'            =>  $field->field_name,
                'customer_name'         =>  $customer ? $customer->name : null,
                'schedule_description'  =>  $field->schedule_description,
                'amount'                =>  $field->amount,
                'status'                =>  $field->status,
                'reservation_document_id'       => $field->reservation_document_id,
                'reservation_document_nro'      => $field->reservation_document_nro
            ];
                
            return (object) $orderedField;
        });
            

        return $fields;
    }

    public function excel(Request $request){

        $report_fields =   $this->queryReporteCampos($request);

        $report_fields->transform(function ($item) {
            unset($item->customer_id); 
            return $item;
        });

        return Excel::download(new ReportFieldExport($report_fields,$request), 
        'reporte_campos_' . Carbon::now()->format('Y_m_d_H_i_s') . '.xlsx');

    }

    public function pdf(Request $request){

        $company                =   Company::find(1);
        
        $report_fields          =   $this->queryReporteCampos($request);

        $report_fields->transform(function ($item) {
            unset($item->customer_id); 
            return $item;
        });

        
        $pdf = Pdf::loadview('reports.report_fields.pdf.pdf', [
                'company'               =>  $company,
                'report_fields'         =>  $report_fields,
                'filters'               =>  $request
              
            ])->setPaper('a4', 'portrait');


        return $pdf->stream('reporte_campos_' . Carbon::now()->format('Y_m_d_H_i_s') .'.pdf');
    }

    public function generateDocumentCreate($id){
        
        $reservation    =   DB::connection('tenant')->table('bookings as b')
                            ->join('booking_detail as bd', 'b.id', '=', 'bd.booking_id')
                            ->join('fields as f', 'f.id', '=', 'b.field_id')
                            ->join('schedules as s', 's.id', '=', 'b.schedule_id')
                            ->select(
                                'b.id',
                                'b.created_at',
                                'b.date',
                                'f.field as field_name',
                                'b.customer_id', 
                                's.description as schedule_description',
                                DB::raw('SUM(bd.payment) as amount'),
                                'b.status',
                                'b.reservation_document_id'
                            )
                            ->where('b.id',$id)
                            ->groupBy('b.id','b.created_at','b.date','f.field','b.customer_id','s.description','b.status','b.reservation_document_id')
                            ->orderByDesc('b.created_at')
                            ->get();

        if(count($reservation) === 0){
            throw new Exception("NO EXISTE LA RESERVA EN LA BD!!!");
        }

        $customer   =   DB::connection('landlord')->table('customers as c')
                        ->where('c.id', $reservation[0]->customer_id)
                        ->get();

        if(count($customer) === 0){
            throw new Exception("NO EXISTE LA RESERVA EN LA BD!!!");
        }

        $reservation    =   $reservation[0];
        $customer       =   $customer[0];
        $types_identity_documents   =   DB::select('select 
                                        tid.*
                                        from types_identity_documents as tid
                                        where tid.status = "ACTIVO"');

        $document_actives   =   DB::select('select * from 
                                document_serializations as ds
                                where ds.document_type_id = ?
                                or ds.document_type_id = ?',[1,3]);
        
        return view('reports.report_fields.generate_document',
        compact('reservation','customer','types_identity_documents','document_actives'));

    }


/*
array:3 [ // app\Http\Controllers\Tenant\Reports\ReportFieldController.php:182
  "document_invoice"    => "1"
  "document_number"     => "75608753"
  "reservation_id"      => "1"
]
*/ 
    public function generateDocumentStore(ReservationDocumentStoreRequest $request){

        DB::beginTransaction();
        try {

            SaleController::isActiveTypeSale($request->get('document_invoice'));

            $validated_data         =   ReportFieldController::validationGenerateDocument($request);
      
            $data_correlative       =   SaleController::getCorrelative($request->get('document_invoice'));


            //====== GRABANDO =======
            $document                               =   new ReservationDocument();

            //======= DATOS CLIENTE ======
            $document->customer_id                  =   $validated_data->reservation->customer_id;
            $document->customer_name                =   $validated_data->customer->name;
            $document->customer_type_document       =   $validated_data->customer->type_document_abbreviation;
            $document->customer_document_number     =   $validated_data->customer->document_number;
            $document->customer_document_code       =   $validated_data->customer->type_document_code;
            $document->customer_phone               =   $validated_data->customer->phone;

            //====== USUARIO REGISTRADOR ====
            $document->user_recorder_id             =   Auth::user()->id;
            $document->user_recorder_name           =   Auth::user()->name;

            //======== DATOS TIPO VENTA ====
            $document->type_sale_code               =   $request->get('document_invoice');
            $document->type_sale_name               =   $validated_data->document_type->description;

            //====== MONTOS ======
            $document->igv_percentage           =   $validated_data->company->igv;
            $document->subtotal                 =   $validated_data->reservation->total / (floatval(100 + $validated_data->company->igv)/100);
            $document->igv_amount               =   $validated_data->reservation->total - $document->subtotal;
            $document->total                    =   $validated_data->reservation->total;
            $document->legend                   =   NumberToLettersController::numberToLetters($validated_data->reservation->total);

            //======= PAGOS =====
            $document->method_pay_id_1          =   1;
            $document->amount_pay_1             =   $validated_data->reservation->total;
              
            $document->method_pay_id_2          =   null;
            $document->amount_pay_2             =   null;
  
            //======== CORRELATIVO Y SERIE =======
            $document->correlative              =   $data_correlative->correlative;
            $document->serie                    =   $data_correlative->serie;
            $document->save();

            //========= GRABANDO DETALLE ======
            $detail                             =   new ReservationDocumentDetail();
            $detail->reservation_document_id    =   $document->id;
            $detail->product_code               =   'CAMPO-'.$validated_data->field->id;
            $detail->product_unit               =   'NIU';
            $detail->product_description        =   $validated_data->field->field;
            $detail->product_name               =   $validated_data->field->field;
            $detail->quantity                   =   1;
            $detail->price_sale                 =   $validated_data->reservation->total;
            $detail->amount                     =   $validated_data->reservation->total;
            $detail->mto_valor_unitario         =   (float)($validated_data->reservation->total / 1.18);
            $detail->mto_valor_venta            =   (float)($detail->amount / 1.18);
            $detail->mto_base_igv               =   (float)($detail->amount / 1.18);
            $detail->porcentaje_igv             =   $validated_data->company->igv;
            $detail->igv                        =   (float)($detail->amount) - (float)($detail->amount / 1.18);
            $detail->tip_afe_igv                =   10;
            $detail->total_impuestos            =   (float)($detail->amount) - (float)($detail->amount / 1.18);
            $detail->mto_precio_unitario        =   (float)($detail->amount) - (float)($detail->amount / 1.18);

            $detail->mto_valor_venta        =   (float)($detail->amount / 1.18);
            $detail->mto_base_igv           =   (float)($detail->amount / 1.18);
            $detail->porcentaje_igv         =   $validated_data->company->igv;
            $detail->igv                    =   (float)($detail->amount) - (float)($detail->amount / 1.18);
            $detail->tip_afe_igv            =   10;
            $detail->total_impuestos        =   (float)($detail->amount) - (float)($detail->amount / 1.18);
            $detail->mto_precio_unitario    =   (float)($validated_data->reservation->total);
            $detail->save();

            //========= MARCAR FACTURACIÓN COMO INICIADA =======
            DB::table('document_serializations')
            ->where('company_id', Company::find(1)->id) 
            ->where('document_type_id', $request->get('document_invoice')) 
            ->where('initiated', 'NO') 
            ->update([
                'initiated'     => 'SI',
                'updated_at'    => Carbon::now()
            ]);

            //======== ASOCIANDO DOCUMENTO A LA RESERVA =======
            $validated_data->reservation->reservation_document_id   =   $document->id;
            $validated_data->reservation->update();

            DB::commit();
            return response()->json(['success'=>true,'message'=>'COMPROBANTE GENERADO','data'=>(object)['sale_id'=>$document->id]]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>$th->getMessage(),'line'=>$th->getLine()]);
        }

    }


    public static function validationGenerateDocument($request){

        $reservation    =   Booking::find($request->get('reservation_id'));

        if(!$reservation){
            throw new Exception("LA RESERVA NO EXISTE EN LA BD!!!");
        }

        if($reservation->status !== 'ALQUILADO'){
            throw new Exception("LA RESERVA DEBE TENER ESTADO 'ALQUILADO' !!!");
        }

        if($reservation->reservation_document_id){
            throw new Exception("LA RESERVA YA CUENTA CON UN COMPROBANTE!!!");
        }

        //========== OBTENIENDO DATA ======
        $customer   =   DB::connection('landlord')
                        ->table('customers')
                        ->where('id', $reservation->customer_id)
                        ->first();

        if(!$customer){
            throw new Exception("EL CLIENTE NO EXISTE EN LA BD!!!");
        }

        $document_type  =   DocumentType::where('id', $request->get('document_invoice'))->first();

        if(!$document_type){
            throw new Exception("NO EXISTE EL TIPO DE DOCUMENTO EN LA BD!!");
        }

        $company        =   Company::find(1);
        if(!$company){
            throw new Exception("NO EXISTE LA EMPRESA EN LA BD!!!");
        }

        $field          =   Field::find($reservation->field_id);
        if(!$field){
            throw new Exception("NO EXISTE EL CAMPO EN LA BD!!!");
        }

        $validated_data =   (object)["reservation"  =>  $reservation,
                            "customer"      =>  $customer,
                            "document_type" =>  $document_type,
                            "company"       =>  $company,
                            "field"         =>  $field];

        return  $validated_data;
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

}
