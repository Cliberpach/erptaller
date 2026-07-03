<?php

namespace App\Http\Controllers\Tenant\Sale;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\QRController;
use App\Http\Services\Tenant\Sale\CreditNote\CreditNoteService;
use App\Models\Company;
use App\Models\Tenant\CreditNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class CreditNoteController extends Controller
{
    private CreditNoteService $s_credit_note;

    public function __construct()
    {
        $this->s_credit_note = new CreditNoteService();
    }

    public function index(Request $request)
    {
        return view('sales.credit_note.index', [
            'sale_id' => $request->get('sale_id'),
        ]);
    }

    public function getAll(Request $request)
    {
        $customer_id = $request->get('customer_id');
        $start_date  = $request->get('start_date');
        $end_date    = $request->get('end_date');
        $status      = $request->get('status');
        $sale_id     = $request->get('sale_id');

        // credit_notes no guarda customer_id (solo el snapshot customer_name/document_*):
        // el filtro por cliente sale vía join a la venta origen.
        $notes = DB::table('credit_notes as cn')
            ->join('sales_documents as sd', 'sd.id', '=', 'cn.sale_id')
            ->select(
                'cn.id',
                'cn.created_at as fecha_registro',
                'cn.creator_user_name',
                'cn.customer_name',
                DB::raw("CONCAT(cn.serie, '-', cn.correlative) AS doc"),
                'cn.tipDocAfectado',
                'cn.numDocfectado',
                'cn.codMotivo',
                'cn.desMotivo',
                DB::raw("FORMAT(cn.total, 2) AS total"),
                'cn.sunat_status',
                'cn.ruta_xml',
                'cn.ruta_cdr',
                'cn.sale_id'
            );

        if ($customer_id) {
            $notes->where('sd.customer_id', $customer_id);
        }
        if ($start_date) {
            $notes->whereDate('cn.created_at', '>=', $start_date);
        }
        if ($end_date) {
            $notes->whereDate('cn.created_at', '<=', $end_date);
        }
        if ($status) {
            $notes->where('cn.sunat_status', $status);
        }
        if ($sale_id) {
            $notes->where('cn.sale_id', $sale_id);
        }

        return DataTables::of($notes)->make(true);
    }

    /** Envía la NC a SUNAT. */
    public function sendSunat(Request $request)
    {
        try {
            $credit_note_id = $request->get('credit_note_id');

            if (!$credit_note_id) {
                throw new Exception('NO SE ENCONTRÓ EL ID DE LA NOTA DE CRÉDITO');
            }

            $nc = $this->s_credit_note->sendSunat((int) $credit_note_id);

            return response()->json(['success' => true, 'message' => $nc->last_send_message]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    public function downloadXml($id)
    {
        $credit_note = CreditNote::findOrFail($id);
        $filePath    = public_path($credit_note->ruta_xml);

        if (File::exists($filePath)) {
            return response()->download($filePath);
        }

        abort(404, 'Archivo no encontrado');
    }

    public function downloadCdr($id)
    {
        $credit_note = CreditNote::findOrFail($id);
        $filePath    = public_path($credit_note->ruta_cdr);

        if (File::exists($filePath)) {
            return response()->download($filePath);
        }

        abort(404, 'Archivo no encontrado');
    }

    /** Datos para el modal (venta fiscal + sus líneas). */
    public function data($id)
    {
        try {
            return response()->json(['success' => true] + $this->s_credit_note->dataForCreditNote((int) $id));
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /** Emite la NC (parcial/total). Sin SUNAT/stock/caja en esta capa. */
    public function store(Request $request)
    {
        try {
            $data          = $request->all();
            $data['lines'] = json_decode($request->input('lines', '[]'), true) ?: [];

            $nc = $this->s_credit_note->emit($data);

            return response()->json([
                'success' => true,
                'message' => 'NOTA DE CRÉDITO EMITIDA',
                'id'      => $nc->id,
                'doc'     => $nc->serie . '-' . $nc->correlative,
            ]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /** PDF (ticket) de la NC. */
    public function pdf($id)
    {
        $company     = Company::find(1);
        $credit_note = CreditNote::with('details')->findOrFail($id);

        if (!$credit_note->ruta_qr) {
            $data_qr = (object) [
                'ruc_emisor'                    => $company->ruc,
                'tipo_comprobante'              => $credit_note->type_sale_code,
                'serie'                         => $credit_note->serie,
                'correlativo'                   => $credit_note->correlative,
                'mto_total_igv'                 => number_format($credit_note->igv_amount, 2, '.', ''),
                'total'                         => number_format($credit_note->total, 2, '.', ''),
                'fecha_emision'                 => Carbon::parse($credit_note->fechaEmision ?? $credit_note->created_at)->format('Y-m-d'),
                'tipo_documento_adquiriente'    => $credit_note->customer_document_code,
                'nro_documento_adquieriente'    => $credit_note->customer_document_number,
            ];

            $res_qr = QRController::generateQr(json_encode($data_qr));
            $res_qr = $res_qr->getData();

            if ($res_qr->success) {
                $credit_note->ruta_qr = $res_qr->data->ruta_qr;
                $credit_note->save();
            }
        }

        $pdf = Pdf::loadView('sales.credit_note.pdf.pdf', [
            'company'     => $company,
            'credit_note' => $credit_note,
        ])->setPaper([0, 0, 226.772, 651.95], 'portrait');

        return $pdf->stream('nota_credito_' . $credit_note->serie . '-' . $credit_note->correlative . '.pdf');
    }
}
