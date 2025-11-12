<?php

namespace App\Http\Services\Tenant\Inventory\NoteIncome;

use App\Http\Services\Tenant\Inventory\Kardex\KardexManager;
use App\Http\Services\Tenant\Inventory\Product\ProductRepository;
use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductManager;
use App\Models\Tenant\NoteIncome;
use App\Models\Tenant\NoteIncomeDetail;
use Exception;
use Illuminate\Support\Facades\DB;

class NoteIncomeDetailService
{
    private ProductRepository $r_product;
    private WarehouseProductManager $s_warehouse_product;
    private KardexManager $s_kardex;
    public function __construct()
    {
        $this->r_product            =   new ProductRepository();
        $this->s_warehouse_product  =   new WarehouseProductManager();
        $this->s_kardex             =   new KardexManager();
    }

    public function storeDetail(array $data, NoteIncome $note_income)
    {

        $lst_detail =   json_decode($data['lstNoteIncome']);

        foreach ($lst_detail as  $item) {

            //======= VALIDANDO CANTIDAD =======
            if (!is_numeric($item->quantity) || $item->quantity <= 0) {
                throw new Exception("LA CANTIDAD DEL PRODUCTO NO ES VÁLIDA!!!");
            }

            //======= VALIDAR EXISTENCIA DEL PRODUCTO =======
            $product_exists =   $this->r_product->getProduct($item->product_id);
            if (count($product_exists) === 0) {
                throw new Exception("EL PRODUCTO " . $item->product_name . " NO EXISTE EN LA BD!!!");
            }

            //==== ALMACÉN ====
            $warehouse                      =   DB::select('SELECT *
                                                FROM warehouses AS w
                                                WHERE w.id = 1')[0];


            $note_detail                    =   new NoteIncomeDetail();
            $note_detail->note_income_id    =   $note_income->id;
            $note_detail->product_id        =   $item->product_id;
            $note_detail->brand_id          =   $product_exists[0]->brand_id;
            $note_detail->category_id       =   $product_exists[0]->category_id;

            $note_detail->product_name      =   $product_exists[0]->name;
            $note_detail->brand_name        =   $product_exists[0]->brand_name;
            $note_detail->category_name     =   $product_exists[0]->category_name;

            $note_detail->warehouse_id      =   $warehouse->id;
            $note_detail->warehouse_name    =   $warehouse->descripcion;

            $note_detail->quantity          =   $item->quantity;
            $note_detail->save();


            //====== INSERTANDO STOCK =====
            $this->s_warehouse_product->increaseStock(1,$item->product_id,$item->quantity);

            //===== GRABANDO EN KARDEX ========
            $this->s_kardex->store($note_income,$note_detail,'IN','NOTE INCOME');

        }
    }
}
