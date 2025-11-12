<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExitMoneyDetail extends Model
{
    use HasFactory;
    protected $table = 'exit_money_detail';
    protected $guarded = [''];

    public function exitMoney()
    {
        return $this->belongsTo(ExitMoney::class, 'exit_money_id');
    }
}
