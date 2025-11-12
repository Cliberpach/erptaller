<?php

namespace App\Http\Services\Tenant\Inventory\NoteIncome;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant\NoteIncome;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Facades\Auth;

class NoteIncomeService
{
    private ValidationsService $s_validations;
    private NoteIncomeDetailService $s_detail;

    public function __construct()
    {
        $this->s_validations    =   new ValidationsService();
        $this->s_detail         =   new NoteIncomeDetailService();
    }

    public function store(array $data)
    {

        //======== VALIDACIÓN COMPLEJA =======
        $this->s_validations->validationStore($data);

        //====== REGISTRAR MAESTRO =======
        $user   =   User::find(Auth::user()->id);
        $data['user_recorder_name'] =   $user->name;

        $note   =   NoteIncome::create($data);

        //======= GUARDAR DETALLE =======
        $this->s_detail->storeDetail($data, $note);
    }

    public function storeFromProduct(Product $product)
    {
        $warehouse  =   Warehouse::findOrFail(1);
        $brand      =   Brand::findOrFail($product->brand_id);
        $category   =   Category::findOrFail($product->category_id);

        $data   =   [
            'user_recorder_id' =>  Auth::user()->id,
            'user_recorder_name' =>  Auth::user()->name,
            'observation'       =>  'GENERADO AL GRABAR PRODUCTO',
            'lstNoteIncome'     =>  json_encode([
                (object)[
                    'product_id'        =>  $product->id,
                    'product_name'      =>  $product->name,
                    'brand_id'          =>  $product->brand_id,
                    'brand_name'        =>  $brand->name,
                    'category_id'       =>  $product->category_id,
                    'category_name'     =>  $category->name,
                    'quantity'          =>  $product->stock,
                    'warehouse_id'      =>  $warehouse->id,
                    'warehouse_name'    =>  $warehouse->descripcion
                ]
            ])
        ];

        $this->store($data);
    }
}
