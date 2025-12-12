<?php

namespace App\Http\Services\Tenant\Accounts\CustomerAccount;

use App\Http\Services\Tenant\Accounts\CustomerAccount\CustomerAccountDto;
use App\Models\Company;
use App\Models\Landlord\Customer;
use App\Models\Tenant\Accounts\CustomerAccount;
use App\Models\Tenant\Accounts\CustomerAccountDetail;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class CustomerAccountService
{
    private CustomerAccountDto $s_dto;
    private CustomerAccountRepository $s_repository;
    private CustomerAccountValidation $s_validation;

    public function __construct()
    {
        $this->s_repository =   new CustomerAccountRepository();
        $this->s_dto        =   new CustomerAccountDto($this->s_repository);
        $this->s_validation =   new CustomerAccountValidation($this->s_repository);
    }

    public function store(array $data): CustomerAccount
    {
        $dto   =    $this->s_dto->getDtoFromWorkOrder($data);
        $customer_account   =   $this->s_repository->insertCustomerAccount($dto);

        return $customer_account;
    }

    public function storePago(array $data): CustomerAccountDetail
    {
        $data   =   $this->s_validation->validationPayStore($data);

        $customer_account   =   $data['customer_account'];
        $balance            =   round($customer_account->balance, 2);
        $amount_pay         =   round($data['cantidad'], 2);
        $new_balance        =   $balance - $amount_pay;
        $new_status         =   $new_balance == 0 ? 'PAGADO' : 'PENDIENTE';

        $dto_account        =   ['balance' => $new_balance, 'status' => $new_status];
        $this->s_repository->updateCustomerAccount($data['id'], $dto_account);
        $data['balance']    =   $new_balance;

        $dto    =   $this->s_dto->getDtoPay($data);
        $pay    =   $this->s_repository->insertPay($dto);

        $carpet_company =   Company::findOrFail(1)->files_route;
        $path = public_path("storage/{$carpet_company}/customer_accounts/images/");
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $file   =   $data['imagen']??null;

        if ($file instanceof UploadedFile && $file->isValid()) {
            $file->move($path, $pay->img_name);
        }

        return $pay;
    }

    public function pdfOne(int $id)
    {
        $cuenta = CustomerAccount::findOrFail($id);
        $documento =   null;
        $cliente    =   null;

        if ($cuenta->work_order_id) {
            $work_order =   WorkOrder::findOrFail($cuenta->work_order_id);
            $cliente    =   Customer::findOrFail($work_order->customer_id);
            $documento  =   'OT-' . $work_order->id;
        }

        $company            = Company::first();
        $detalle    =   CustomerAccountDetail::where('customer_account_id', $id)
            ->orderByDesc('id')
            ->get();

        $pdf = Pdf::loadview('accounts.customer_accounts.reports.pdf-one', [
            'cuenta' => $cuenta,
            'detalles' => $cuenta->detalles,
            'cliente' => $cliente,
            'company' => $company,
            'documento' => $documento,
            'detalle'   => $detalle
        ])->setPaper('a4');
        return $pdf->stream('CUENTA-' . $cuenta->id . '.pdf');
    }
}
