<?php

namespace App\Http\Services\Tenant\Accounts\SupplierAccount;

use App\Http\Services\Tenant\Accounts\CustomerAccount\CustomerAccountDto;
use App\Models\Company;
use App\Models\Landlord\Customer;
use App\Models\Tenant\Accounts\CustomerAccount;
use App\Models\Tenant\Accounts\CustomerAccountDetail;
use App\Models\Tenant\Accounts\SupplierAccount\SupplierAccount;
use App\Models\Tenant\Accounts\SupplierAccount\SupplierAccountDetail;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class SupplierAccountService
{
    private SupplierAccountDto $s_dto;
    private SupplierAccountRepository $s_repository;
    private SupplierAccountValidation $s_validation;

    public function __construct()
    {
        $this->s_repository =   new SupplierAccountRepository();
        $this->s_dto        =   new SupplierAccountDto($this->s_repository);
        $this->s_validation =   new SupplierAccountValidation($this->s_repository);
    }

    public function store(array $data): SupplierAccount
    {
        $dto    =   [];

        if (isset($data['purchase_id'])) {
            $dto                =    $this->s_dto->getDtoFromPurchase($data);
        }

        $item   =    $this->s_repository->store($dto);

        return $item;
    }

    public function storePago(array $data): SupplierAccountDetail
    {

        $data   =   $this->s_validation->validationPayStore($data);

        $supplier_account   =   $data['supplier_account'];
        $balance            =   round($supplier_account->balance, 2);
        $paid               =   round($supplier_account->paid, 2);
        $amount_pay         =   round($data['cantidad'], 2);
        $new_balance        =   $balance - $amount_pay;
        $new_paid           =   $paid + $amount_pay;
        $new_status         =   $new_balance == 0 ? 'PAGADO' : 'PENDIENTE';

        $dto_account        =   ['balance' => $new_balance, 'status' => $new_status, 'paid' => $new_paid];
        $account            =   $this->s_repository->updateAccount($data['id'], $dto_account);

        $data['balance']    =   $new_balance;
        $data['paid']       =   $new_paid;
        $dto                =   $this->s_dto->getDtoPay($data);
        $pay                =   $this->s_repository->insertPay($dto);

        $this->s_repository->setPaymentStatus($data['id']);

        //============ STORE IMG ===========
        $carpet_company =   Company::findOrFail(1)->files_route;
        $path           =   public_path("storage/{$carpet_company}/supplier_accounts/images/");
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $file   =   $data['img_pago'] ?? null;

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

    public function updateFromWorkOrder(array $dto)
    {
        $account        =   $this->s_repository->findByWorkOrder($dto['work_order_id']);
        $work_order     =   WorkOrder::findOrFail($dto['work_order_id']);

        $total_order    =   round($work_order->total, 2);
        $amount         =   round($account->amount, 2);
        $paid           =   round($account->paid, 2);
        $new_amount     =   round($account->amount, 2);
        $new_balance    =   round($account->balance, 2);
        $new_status     =   $account->status;

        if ($total_order === $amount) {
            return;
        }

        if ($total_order > $amount) {
            $amount         =   round($account->amount, 2);
            $new_amount     =   $total_order;
            $new_balance    =   round($new_amount - $account->paid, 2);
            $new_status     =   'PENDIENTE';
        }

        if ($total_order < $amount) {
            if ($total_order < $paid) {
                throw new Exception(
                    "EL TOTAL DE LA ORDEN DEBE SER MAYOR A LO PAGADO EN LA CUENTA: " .
                        number_format($total_order, 2, ',', '.') .
                        " - " .
                        number_format($paid, 2, ',', '.')
                );
            } else {
                $new_amount     =   $total_order;
                $new_balance    =   round($new_amount - $account->paid, 2);
            }
        }

        if ($new_balance == 0) {
            $new_status =   'PAGADO';
        }

        $dto    =   [
            'amount'    =>  $new_amount,
            'balance'   =>  $new_balance,
            'status'    =>  $new_status
        ];

        return $this->s_repository->updateCustomerAccount($account->id, $dto);
    }
}
