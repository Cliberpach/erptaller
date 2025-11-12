<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashBook extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'petty_cash_books';

    protected $fillable = [
        'petty_cash_id',
        'status',
        'shift_id',
        'user_id',
        'initial_amount',
        'closing_amount',
        'initial_date',
        'final_date',
        'sale_day',
    ];

    public function pettyCash()
    {
        return $this->belongsTo(PettyCash::class, 'petty_cash_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
