<?php

namespace App\Http\Controllers\Tenant\Sale;

use App\Http\Controllers\Controller;
use App\Http\Services\Tenant\Sale\CreditNote\CreditNoteService;
use App\Models\Company;
use App\Models\Tenant\CreditNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Throwable;

class CreditNoteController extends Controller
{
    private CreditNoteService $s_credit_note;

    public function __construct()
    {
        $this->s_credit_note = new CreditNoteService();
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

        $pdf = Pdf::loadView('sales.credit_note.pdf.pdf', [
            'company'     => $company,
            'credit_note' => $credit_note,
        ])->setPaper([0, 0, 226.772, 651.95], 'portrait');

        return $pdf->stream('nota_credito_' . $credit_note->serie . '-' . $credit_note->correlative . '.pdf');
    }
}
