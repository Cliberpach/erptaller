<?php

namespace App\Http\Services\Tenant\Inventory\NoteIncome;

use App\Models\Product;
use App\Models\Tenant\NoteIncome;

class NoteIncomeManager
{
    protected NoteIncomeService $s_note_income;

     public function __construct()
    {
        $this->s_note_income    =   new NoteIncomeService();
    }

    public function store(array $data){
        $this->s_note_income->store($data);
    }

    public function storeFromProduct(Product $product){
        $this->s_note_income->storeFromProduct($product);
    }
}
