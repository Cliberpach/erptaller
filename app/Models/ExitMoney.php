<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExitMoney extends Model
{
    use HasFactory;
    protected $table = 'exit_money';
    protected $guarded = [''];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function proofPayment()
    {
        return $this->belongsTo(ProofPayment::class, 'proof_payment_id');
    }
}
