<?php

namespace App\Models\Tenant\Sale;

use App\Models\Tenant\Maintenance\BankAccount\BankAccount;
use App\Models\Tenant\PaymentMethod;
use Illuminate\Database\Eloquent\Model;

/**
 * PASO 4 - Pago en la venta (Capa A): una línea de pago de un documento de venta.
 * método + cuenta (nullable: efectivo) + monto + n° operación.
 */
class SaleDocumentPayment extends Model
{
    protected $table = 'sales_document_payments';

    protected $fillable = [
        'sale_document_id',
        'payment_method_id',
        'bank_account_id',
        'amount',
        'operation_number',
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
}
