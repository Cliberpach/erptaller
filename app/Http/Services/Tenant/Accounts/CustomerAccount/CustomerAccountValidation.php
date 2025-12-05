<?php
namespace App\Http\Services\Tenant\Accounts\CustomerAccount;

use App\Http\Services\Tenant\Cash\PettyCashBook\PettyCashBookService;
use Exception;
use Illuminate\Support\Facades\Auth;

class CustomerAccountValidation
{
    private PettyCashBookService $s_cash_book;
    private CustomerAccountRepository $s_repository;

    public function __construct($_s_repository){
        $this->s_cash_book  =   new PettyCashBookService();
        $this->s_repository =   $_s_repository;
    }

    public function validationPayStore($data){
        $cash_book  =   $this->s_cash_book->getCashBookUser(Auth::user()->id);
    
        if(!$cash_book){
            throw new Exception("NO PERTENCECES A UNA CAJA ABIERTA");
        }

        //============ VALIDANDO SALDO =========
        $customer_account   =   $this->s_repository->findCustomerAccount($data['id']);
        $balance            =   round($customer_account->balance,2);
        $amount_pay         =   round($data['cantidad'],2);

        if($amount_pay > $balance){
            throw new Exception("MONTO PAGO (".$amount_pay.") EXCEDE AL SALDO(".$balance.")");
        }

        $data['petty_cash_book_id'] =   $cash_book->petty_cash_book_id;
        $data['customer_account']   =   $customer_account;
        return $data;
    }

}