<?php

namespace App\Http\Services\Tenant\Cash\PettyCashBook;

use App\Http\Services\Tenant\Cash\PettyCash\CashService;
use App\Models\Company;
use App\Models\ExitMoney;
use App\Models\Tenant\Accounts\CustomerAccountDetail;
use App\Models\Tenant\Cash\PettyCashBook;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\Sale;
use App\Models\Tenant\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PettyCashBookService
{
    private PettyCashBookRepository $s_repository;
    private PettyCashBookDto $s_dto;
    private PettyCashBookValidation $s_validation;
    private CashService $s_cash;

    public function __construct()
    {
        $this->s_repository =   new PettyCashBookRepository();
        $this->s_dto        =   new PettyCashBookDto();
        $this->s_validation =   new PettyCashBookValidation($this->s_repository);
        $this->s_cash       =   new CashService();
    }

    public function openPettyCash(array $data): PettyCashBook
    {
        $this->s_validation->validateOpenCash($data);
        $dto    =   $this->s_dto->getDtoStore($data);
        $petty_cash_book   =   $this->s_repository->insertPettyCashBook($dto);
        $this->s_cash->setStatus($dto['petty_cash_id'], 'ABIERTO');

        // CANDADO 3 (Cajas Capa C): ancla la sede activa a la sede de la caja abierta
        // ('sede_activa_id' = HasSedeActiva::SESSION_KEY).
        $caja = \App\Models\Tenant\Cash\PettyCash::find($dto['petty_cash_id']);
        if ($caja && $caja->sede_id) {
            \Illuminate\Support\Facades\Session::put('sede_activa_id', (int) $caja->sede_id);
        }

        return $petty_cash_book;
    }

    public function getPdfOne(array $data)
    {
        $id =   $data['id'];

        //====== OBTENER MOVIMIENTO =======
        $petty_cash_book    =   $this->s_repository->getPettyCashBook($id);
        $cajero             =   User::findOrFail($petty_cash_book->user_id);
        $payment_methods    =   PaymentMethod::where('estado', 'ACTIVO')->get();

        // SEDE del turno: la caja cuelga de una sede (petty_cashes.sede_id -> sedes.nombre).
        $sede_nombre        =   DB::table('petty_cashes as pc')
            ->join('sedes as s', 's.id', '=', 'pc.sede_id')
            ->where('pc.id', $petty_cash_book->petty_cash_id)
            ->value('s.nombre');

        $consolidated       =   $this->getConsolidated($id);

        //========= EGRESOS ===========
        $exit_moneys            =   ExitMoney::where('petty_cash_book_id', $id)->where('status', true)->get();

        //======= OBTENER DATOS DE LA EMPRESA ========
        $company = Company::first();

        //========= OBTENER DOCUMENTOS DE VENTA ======
        // Reporte de caja: la tabla de ventas cobradas muestra solo CONTADO (cuadra con
        // report_sales). El crédito va en su tabla informativa (consolidated.report_credit_sales).
        $sale_documents     =   Sale::where('petty_cash_book_id', $id)
            ->where('payment_condition_name', 'CONTADO')
            ->with('payments')->get();

        $customer_pays      =   CustomerAccountDetail::from('customer_accounts_details as cad')
            ->join('customer_accounts as ca', 'ca.id', 'cad.customer_account_id')
            ->leftJoin('work_orders as wo', 'wo.id', '=', 'ca.work_order_id')
            ->leftJoin('sales_documents as sd', 'sd.id', '=', 'ca.sale_id')
            ->where('cad.petty_cash_book_id',$id)
            ->select(
                'ca.document_number',
                DB::raw("
                    CASE
                        WHEN ca.work_order_id IS NOT NULL THEN wo.customer_name
                        WHEN ca.sale_id IS NOT NULL THEN sd.customer_name
                        ELSE NULL
                    END AS customer_name
                "),
                'cad.cash',
                'cad.amount',
                'cad.total',
                'cad.payment_method_id',
                'cad.created_at'
            )->get();

        //====== VISTA PDF ==========
        $pdf = Pdf::loadView(
            'cash.petty-cash-book.reports.pdf-one',
            compact(
                'petty_cash_book',
                'sede_nombre',
                'company',
                'sale_documents',
                'payment_methods',
                'cajero',

                'exit_moneys',
                'consolidated',
                'customer_pays'
            )
        );

        //========= PAGINACIÓN 1/n =========
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $font   = $dompdf->getFontMetrics()->get_font("helvetica", "bold");
        $dompdf->get_canvas()->page_text(530, 800, "{PAGE_NUM} / {PAGE_COUNT}", $font, 10, array(0, 0, 0));

        //======= VISUALIZAR PDF ==========
        return $pdf->stream('caja_movimiento' . $petty_cash_book->id . '.pdf');
    }

    function totalEgresosPorMetodoPago($paymentMethods, $exitMoneys): array
    {
        $totales = [];

        foreach ($paymentMethods as $method) {
            $methodId = $method->id;
            $methodName = $method->description;

            $egresosPorMetodo = $exitMoneys->filter(function ($egreso) use ($methodId) {
                return $egreso->payment_method_id == $methodId;
            });

            $total = $egresosPorMetodo->sum('total');

            $totales[] = [
                'payment_method_id' => $methodId,
                'payment_method_name' => $methodName,
                'total' => $total,
            ];
        }

        return $totales;
    }

    public function getCashBookUser(int $user_id)
    {
        return $this->s_repository->getCashBookUser($user_id);
    }

    public function getConsolidated(int $id)
    {
        $payment_methods            =   PaymentMethod::where('estado', 'ACTIVO')->get();

        $report_sales               =   $this->getReportSales($payment_methods, $id);
        $report_expenses            =   $this->getReportExpenses($payment_methods, $id);
        $report_customer_accounts   =   $this->getReportCustomerAccounts($payment_methods, $id);
        $report_credit_sales        =   $this->getReportCreditSales($id); // informativo, NO suma
        $petty_cash_book            =   $this->s_repository->getPettyCashBookInfo($id);
        // Cuadre: solo ventas CONTADO + cobranzas - egresos + saldo inicial. El crédito NO suma.
        $amount_close               =   $report_customer_accounts['total'] + $report_sales['total'] - $report_expenses['total'] + $petty_cash_book->initial_amount;

        return [
            'report_sales'              =>  $report_sales,
            'report_expenses'           =>  $report_expenses,
            'report_customer_accounts'  =>  $report_customer_accounts,
            'report_credit_sales'       =>  $report_credit_sales,
            'petty_cash_book'           =>  $petty_cash_book,
            'amount_close'              =>  $amount_close
        ];
    }

    public function getReportSales($payment_methods, int $id)
    {
        // PASO 4 (Capa C): montos por método desde sales_document_payments (join a la venta
        // por petty_cash_book_id). GROUP BY método suma TODAS las líneas, incl. N del mismo
        // método (2 Yapes -> suma las dos). Reemplaza la lectura de amount_pay_1/2.
        // Reporte de caja: solo VENTAS AL CONTADO suman al cuadre (lo realmente cobrado).
        // Las ventas a CRÉDITO entran como informativo aparte (su dinero llega vía Cobranzas
        // de CxC, que ya tiene su sección -> evita doble conteo). Filtro por CONDICIÓN
        // (payment_condition_name), no por estado (un crédito cobrado pasa a PAGADO).
        $pagos = DB::table('sales_document_payments as sdp')
            ->join('sales_documents as sd', 'sd.id', '=', 'sdp.sale_document_id')
            ->where('sd.petty_cash_book_id', $id)
            ->where('sd.status', '<>', 'ANULADO')
            ->where('sd.payment_condition_name', 'CONTADO')
            ->groupBy('sdp.payment_method_id')
            ->select('sdp.payment_method_id', DB::raw('SUM(sdp.amount) as amount'))
            ->get()
            ->pluck('amount', 'payment_method_id'); // [method_id => monto]

        $report_sales = [];
        foreach ($payment_methods as $payment_method) {
            $report_sales[] = [
                'payment_method_id'   => $payment_method->id,
                'payment_method_name' => $payment_method->description,
                'amount'              => (float) ($pagos[$payment_method->id] ?? 0),
            ];
        }

        $total = (float) DB::table('sales_document_payments as sdp')
            ->join('sales_documents as sd', 'sd.id', '=', 'sdp.sale_document_id')
            ->where('sd.petty_cash_book_id', $id)
            ->where('sd.status', '<>', 'ANULADO')
            ->where('sd.payment_condition_name', 'CONTADO')
            ->sum('sdp.amount');

        return ['total' => $total, 'report' => $report_sales];
    }

    /**
     * Reporte de caja: ventas a CRÉDITO del turno (informativo, NO suman al cuadre).
     * Partición null-safe: todo lo que NO es CONTADO (incl. NULL/'') cae acá -> ninguna
     * venta del turno queda en limbo.
     */
    public function getReportCreditSales(int $id)
    {
        return DB::table('sales_documents as sd')
            ->where('sd.petty_cash_book_id', $id)
            ->where('sd.status', '<>', 'ANULADO')
            ->whereRaw("NOT (sd.payment_condition_name <=> 'CONTADO')")
            ->select('sd.id', 'sd.serie', 'sd.correlative', 'sd.customer_name', 'sd.total', 'sd.payment_condition_name')
            ->orderBy('sd.id')
            ->get();
    }

    public function getReportExpenses($payment_methods, int $id)
    {
        $expenses = ExitMoney::where('petty_cash_book_id', $id)
            ->where('status', '1')
            ->get();

        $report_expenses = [];

        foreach ($payment_methods as $payment_method) {

            $amount = $expenses
                ->where('payment_method_id', $payment_method->id)
                ->sum('total');

            $report_expenses[] = [
                'payment_method_id'   => $payment_method->id,
                'payment_method_name' => $payment_method->description,
                'amount'              => $amount
            ];
        }

        $total = $expenses->sum('total');

        return [
            'total'  => $total,
            'report' => $report_expenses
        ];
    }

    public function getReportCustomerAccounts($payment_methods, int $id)
    {
        $customer_pays              =   CustomerAccountDetail::where('petty_cash_book_id', $id)->get();
        $report_customer_accounts   =   [];
        foreach ($payment_methods as $payment_method) {
            $item   =   [];
            $amount =   0;

            if ($payment_method->id === 1) {
                $amount   +=   $customer_pays->sum('cash');
            } else {
                $amount   =   $customer_pays->where('payment_method_id', $payment_method->id)->sum('amount');
            }


            $item       =   [
                'payment_method_id' =>  $payment_method->id,
                'payment_method_name' => $payment_method->description,
                'amount'            =>  $amount
            ];

            $report_customer_accounts[] =   $item;
        }

        $total  =   $customer_pays->sum('total');

        return ['total' => $total, 'report' => $report_customer_accounts];
    }


    public function closePettyCash(array $data)
    {
        $this->s_validation->validationClosePettyCash($data);
        $consolidated   =   $this->getConsolidated($data['id']);

        $petty_cash_book                    =   $this->s_repository->getPettyCashBook($data['id']);
        $petty_cash_book->status            =   'CERRADO';
        $petty_cash_book->closing_amount    =   $consolidated['amount_close'];
        $petty_cash_book->final_date        =   now();
        $petty_cash_book->save();

        $this->s_cash->setStatus($petty_cash_book->petty_cash_id, 'CERRADO');

        return $petty_cash_book;
    }
}
