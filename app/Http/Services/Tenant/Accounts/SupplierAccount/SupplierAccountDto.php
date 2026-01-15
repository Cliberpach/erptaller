<?php

namespace App\Http\Services\Tenant\Accounts\SupplierAccount;

use App\Models\Company;
use App\Models\Tenant\Accounts\SupplierAccount\SupplierAccount;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\PurchaseDocument;
use App\Models\Tenant\Sale;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use Illuminate\Http\UploadedFile;

class SupplierAccountDto
{
    private SupplierAccountRepository $s_repository;

    public function __construct($_s_repository)
    {
        $this->s_repository =   $_s_repository;
    }

    public function getDtoFromPurchase($data): array
    {
        $dto    =   [];

        $purchase               =   PurchaseDocument::findOrFail($data['purchase_id']);

        $dto['purchase_id']     =   $purchase->id;
        $dto['document_number'] =   'DC-' . $purchase->id;
        $dto['document_date']   =   $purchase->created_at;
        $dto['amount']          =   $purchase->total;
        $dto['balance']         =   $purchase->total;
        $dto['paid']            =   0;

        return $dto;
    }

    public function getDtoPay($data)
    {

        $dto    =   [];
        $carpet_company =   Company::findOrFail(1)->files_route;
        $payment_method =   PaymentMethod::findOrFail($data['metodo_pago']);
        $account        =   SupplierAccount::findOrFail($data['id']);

        $dto['supplier_account_id'] =   $account->id;
        $dto['petty_cash_book_id']  =   $data['petty_cash_book_id'];
        $dto['date']                =   $data['fecha'];
        $dto['observation']         =   $data['observacion'];
        $dto['total']               =   $data['cantidad'];
        $dto['payment_method_id']   =   $payment_method->id;
        $dto['payment_method_name'] =   $payment_method->description;
        $dto['cash']                =   $data['efectivo_venta'];
        $dto['amount']              =   $data['importe_venta'];
        $dto['balance']             =   $data['balance'];
        $dto['paid']                =   $account->paid;

        $file   =   $data['img_pago'] ?? null;
        if ($file instanceof UploadedFile && $file->isValid()) {
            $extension = $file->getClientOriginalExtension();
            $next_id_pay                =   $this->s_repository->getNexIdPay($dto['supplier_account_id']);
            $filename                   =   $dto['supplier_account_id'] . '_' . $next_id_pay . '.' . $extension;
            $dto['img_route']           =   "storage/{$carpet_company}/supplier_accounts/images/{$filename}";
            $dto['img_name']            =   $filename;
        }

        return $dto;
    }
}
