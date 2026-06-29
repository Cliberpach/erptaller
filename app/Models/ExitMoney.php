<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExitMoney extends Model
{
    use HasFactory;
    protected $table = 'exit_money';

    protected $fillable = [
        'proof_payment_id',
        'supplier_id',
        'user_id',
        'number',
        'date',
        'reason',
        'total',
        'status',
        'payment_method_id',
        'bank_account_id',
        'operation_number',
        'petty_cash_book_id'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function proofPayment()
    {
        return $this->belongsTo(ProofPayment::class, 'proof_payment_id');
    }

    // PASO 4: método + cuenta de origen del egreso (para PDF y reporte de caja).
    public function paymentMethod()
    {
        return $this->belongsTo(\App\Models\Tenant\PaymentMethod::class, 'payment_method_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(\App\Models\Tenant\Maintenance\BankAccount\BankAccount::class, 'bank_account_id');
    }
}
