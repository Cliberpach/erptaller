<?php

namespace App\Http\Controllers\Tenant\Consultas;

use App\Exports\CreditosExport;
use App\Http\Controllers\Controller;
use App\Http\Services\Tenant\Queries\QueriesManager;
use App\Models\Company;
use App\Models\DocumentType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class QueryReservationController extends Controller
{
    protected QueriesManager $s_queries;

    public function __construct(){
        $this->s_queries    =   new QueriesManager();
    }

    public function index()
    {
        $company        =   Company::first();
        $document_types =   DocumentType::where('status',1)->whereIn('id',[1,3])->get();

        return view('consultas.reservas.index',compact('company','document_types'));
    }

    /*
  "search_type" => "dni|ruc|nombre|razon_social"
  "search_input" => "75608753"
  "start_date" => "2025-06-01"
  "end_date" => "2025-06-30"
  "search_estado" => "PENDIENTE"
*/
    public function data(Request $request)
    {
        $type       =   $request->get('search_type');
        $value      =   $request->get('search_input');
        $start_date =   $request->get('start_date');
        $end_date   =   $request->get('end_date');
        $status_pay =   $request->get('search_estado');

        $books  =   DB::table('bookings as b')
            ->join('fields as f', 'f.id', 'b.field_id')
            ->join('schedules as s', 's.id', 'b.schedule_id')
            ->select(
                'b.id',
                'b.customer_name',
                'b.customer_type_document_name',
                'b.customer_document_number',
                'b.customer_phone',
                'f.field as field_name',
                's.start_time',
                's.end_time',
                's.description as schedule',
                'b.date',
                'b.nro_hours as total_hours',
                'b.ball',
                'b.vest',
                'b.dni',
                'b.total as amount',
                'b.payment_status',
                'b.facturado'
            );

        if ($value) {
            if ($type === 'dni' || $type === 'ruc') {
                $books  =   $books->where('b.customer_document_number', $value);
            }

            if ($type === 'nombre' || $type === 'razon_social') {
                $books = $books->where('b.customer_name', 'like', '%' . $value . '%');
            }
        }

        if ($start_date) {
            $books  =   $books->where('b.date', '>=', $start_date);
        }

        if ($end_date) {
            $books  =   $books->where('b.date', '<=', $end_date);
        }

        if ($status_pay) {
            $books  =   $books->where('b.payment_status', '=', $status_pay);
        }


        return DataTables::of($books)->make(true);
    }

    public function exportExcel(Request $request)
    {
        $filters = [
            'search_type' => $request->query('search_type'),
            'search_input' => $request->query('search_input'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'search_estado' => $request->query('search_estado'),
        ];
        return Excel::download(new CreditosExport($filters), 'creditos.xlsx');
    }



    public function generateCreditPDF(Request $request)
    {
        $searchType = $request->query('search_type');
        $searchInput = $request->query('search_input');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $estado = $request->query('search_estado', 'PENDIENTE');

        $credits = DB::table('credits')
            ->select(
                'customer_name',
                'customer_document_number',
                'customer_phone',
                'field_name',
                'start_time',
                'end_time',
                'date',
                'total_hours',
                'ball',
                'vest',
                'dni',
                'ruc_number',
                'razon_social',
                'amount'
            )
            ->where('estado', $estado);

        if ($searchType === 'dni') {
            $credits->where('customer_document_number', $searchInput);
        } elseif ($searchType === 'ruc') {
            $credits->where('ruc_number', $searchInput);
        } elseif ($searchType === 'nombre') {
            $credits->where(function ($query) use ($searchInput) {
                $query->where('customer_name', 'like', '%' . $searchInput . '%')
                    ->orWhere('razon_social', 'like', '%' . $searchInput . '%');
            });
        }

        if ($startDate && $endDate) {
            $credits->whereBetween('date', [$startDate, $endDate]);
        }

        $credits = $credits->get();

        $company = Company::first();

        $pdf = PDF::loadView('consultas.creditos.pdf.creditos', [
            'credits' => $credits,
            'company' => $company,
            'search_type' => $searchType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'search_estado' => $estado
        ]);

        return $pdf->stream('creditos_pendientes.pdf');
    }


/*
array:3 [ // app\Http\Controllers\Tenant\Consultas\QueryReservation.php:169
  "lstReservations" => "[{"id":1,"customer_name":"LUIS DANIEL ALVA LUJAN","customer_type_document_name":"DNI","customer_document_number":"12345678","customer_phone":"919191911","field_name":"CAMPO 1","start_time":"13:30:00","end_time":"14:00:00","schedule":"13:30 - 14:00 pm","date":"2025-06-11","total_hours":"1.00","ball":1,"vest":0,"dni":0,"amount":10,"payment_status":"TOTAL","facturado":"NO"},{"id":2,"customer_name":"LUIS DANIEL ALVA LUJAN","customer_type_document_name":"DNI","customer_document_number":"12345678","customer_phone":"919191911","field_name":"CAMPO 1","start_time":"16:00:00","end_time":"16:30:00","schedule":"16:00 - 16:30 pm","date":"2025-06-11","total_hours":"1.00","ball":0,"vest":0,"dni":0,"amount":10,"payment_status":"TOTAL","facturado":"NO"}]"
  "nro_document"    => null
  "type_document"   => "3"
]
*/
    public function generarDocumento(Request $request)
    {
        try {

            DB::beginTransaction();
            $sale   =   $this->s_queries->generarDocumento($request->toArray());

            DB::commit();

            $url_pdf = route('tenant.ventas.comprobante_venta.pdf_voucher', ['id' => $sale->id]);

            return response()->json([
                'success' => true,
                'message' => 'Documento generado correctamente.',
                'url_pdf' => $url_pdf
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }


    private function obtenerClientePorDocumento($tipo, $numero)
    {
        $tipoAbrev = $tipo === 'boleta' ? 'DNI' : 'RUC';
        $isBoleta  = $tipo === 'boleta';

        $query = DB::connection('landlord')->table('customers');

        // Buscar por documento según tipo
        if ($isBoleta) {
            $cliente = $query
                ->where('document_number', $numero)
                ->where('type_document_abbreviation', 'DNI')
                ->first();
        } else {
            $cliente = $query
                ->where('ruc_number', $numero)
                ->where('type_document_abbreviation', 'RUC')
                ->first();
        }

        // Si ya existe, retornarlo
        if ($cliente) {
            return $cliente;
        }

        // Si no existe, crear con los datos del input
        $nombreInput = request('nombre_razon_social');
        $telefono    = request('telefono') ?? null;

        $id = DB::connection('landlord')->table('customers')->insertGetId([
            'document_number'             => $numero,
            'ruc_number'                  => $isBoleta ? null : $numero,
            'name'                        => $nombreInput,
            'razon_social'               => $isBoleta ? null : $nombreInput,
            'phone'                       => $telefono,
            'type_identity_document_id'  => $isBoleta ? 1 : 3,
            'type_document_name'         => $isBoleta ? 'DOCUMENTO NACIONAL DE IDENTIDAD' : 'REGISTRO ÚNICO DE CONTRIBUYENTE',
            'type_document_abbreviation' => $tipoAbrev,
            'type_document_code'         => $isBoleta ? '01' : '06',
            'address'                    => null,
            'email'                      => null,
            'department_id'              => null,
            'province_id'                => null,
            'district_id'                => null,
            'department_name'           => null,
            'province_name'             => null,
            'district_name'             => null,
            'zone'                       => null,
            'ubigeo'                     => null,
            'created_at'                 => now(),
            'updated_at'                 => now(),
        ]);

        return DB::connection('landlord')->table('customers')->where('id', $id)->first();
    }
}
