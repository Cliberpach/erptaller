<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCash extends Model
{
    use HasFactory;

    protected $guarded = [''];
    protected $table = 'petty_cashes';

    protected $fillable = [
        'name',
        'status'
    ];

    public function pettyCashBooks()
    {
        return $this->hasMany(PettyCashBook::class, 'petty_cash_id');
    }
}
