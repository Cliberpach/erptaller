<?php

namespace App\Http\Services\Tenant\Cash\PettyCashBook;

use App\Models\Company;
use App\Models\ExitMoney;
use App\Models\Tenant\Cash\PettyCash;
use App\Models\Tenant\Cash\PettyCashBook;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\Sale;
use App\Models\Tenant\User;
use Barryvdh\DomPDF\Facade\Pdf;

class PettyCashBookService
{
    private PettyCashBookRepository $s_repository;
    private PettyCashBookDto $s_dto;
    private PettyCashBookValidation $s_validation;

    public function __construct()
    {
        $this->s_repository =   new PettyCashBookRepository();
        $this->s_dto        =   new PettyCashBookDto();
        $this->s_validation =   new PettyCashBookValidation($this->s_repository);
    }

    public function openPettyCash(array $data): PettyCashBook
    {
        $this->s_validation->validateOpenCash($data);
        $dto    =   $this->s_dto->getDtoStore($data);
        $petty_cash_book   =   $this->s_repository->insertPettyCashBook($dto);
        return $petty_cash_book;
    }

    public function getPdfOne(array $data)
    {
        $id =   $data['id'];

        //====== OBTENER MOVIMIENTO =======
        $petty_cash_book    =   $this->s_repository->getPettyCashBook($id);
        $cajero             =   User::findOrFail($petty_cash_book->user_id);
        $payment_methods    =   PaymentMethod::where('estado', 'ACTIVO')->get();

        //========= EGRESOS ===========
        $exit_moneys            =   ExitMoney::where('petty_cash_book_id', $id)->where('status', true)->get();
        $amounts_exit_moneys    =   $this->totalEgresosPorMetodoPago($payment_methods, $exit_moneys);
        $total_exit_moneys      =   ExitMoney::where('petty_cash_book_id', $id)->where('status', true)->sum('total');

        //======= OBTENER DATOS DE LA EMPRESA ========
        $company = Company::first();

        //========= OBTENER DOCUMENTOS DE VENTA ======
        $sale_documents     =   Sale::where('petty_cash_book_id', $id)->get();


        //====== VISTA PDF ==========
        $pdf = Pdf::loadView(
            'cash.petty-cash-book.reports.pdf-one',
            compact(
                'petty_cash_book',
                'company',
                'sale_documents',
                'payment_methods',
                'cajero',

                'exit_moneys',
                'amounts_exit_moneys',
                'total_exit_moneys'
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
        $payment_methods    =   PaymentMethod::where('estado', 'ACTIVO')->get();

        $report_sales       =   $this->getReportSales($payment_methods, $id);
        $report_expenses    =   $this->getReportExpenses($payment_methods, $id);
        $petty_cash_book    =   $this->s_repository->getPettyCashBookInfo($id);
        $amount_close       =   $report_sales['total'] - $report_expenses['total'];

        return [
            'report_sales' =>    $report_sales,
            'report_expenses' => $report_expenses,
            'petty_cash_book'   =>  $petty_cash_book,
            'amount_close'      =>  $amount_close
        ];
    }

    public function getReportSales($payment_methods, int $id)
    {
        $sales  =   Sale::where('petty_cash_book_id', $id)->where('estado', '<>', 'ANULADO')->get();
        $report_sales   =   [];
        foreach ($payment_methods as $payment_method) {
            $item   =   [];

            $amount_1   =   $sales->where('method_pay_id_1', $payment_method->id)->sum('amount_pay_1');
            $amount_2   =   $sales->where('method_pay_id_2', $payment_method->id)->sum('amount_pay_2');

            $item       =   [
                'payment_method_id' =>  $payment_method->id,
                'payment_method_name' => $payment_method->description,
                'amount'            =>  $amount_1 + $amount_2
            ];

            $report_sales[] =   $item;
        }

        $total  =   $sales->sum('amount_pay_1') + $sales->sum('amount_pay_2');

        return ['total' => $total, 'report' => $report_sales];
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

    public function closePettyCash(array $data)
    {
        $this->s_validation->validationClosePettyCash($data);
        $consolidated   =   $this->getConsolidated($data['id']);

        $petty_cash_book    =   $this->s_repository->getPettyCashBook($data['id']);
        $petty_cash_book->status    =   'CERRADO';
        $petty_cash_book->closing_amount    =   $consolidated['amount_close'];
        $petty_cash_book->final_date        =   now();
        $petty_cash_book->save();
       
        return $petty_cash_book;
    }
}
