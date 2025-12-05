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

}
