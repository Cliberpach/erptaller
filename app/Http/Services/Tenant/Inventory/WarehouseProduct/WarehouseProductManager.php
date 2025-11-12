<?php

namespace App\Http\Services\Tenant\Inventory\WarehouseProduct;

use App\Models\Tenant\NoteIncome;

class WarehouseProductManager
{
    protected WarehouseProductService $s_warehouse_product;

    public function __construct()
    {
        $this->s_warehouse_product    =   new WarehouseProductService();
    }

    public function increaseStock(int $warehouse_id, int $product_id, float $quantity)
    {
        $this->s_warehouse_product->increaseStock($warehouse_id, $product_id, $quantity);
    }

    public function decreaseStock(int $warehouse_id, int $product_id, float $quantity)
    {
        $this->s_warehouse_product->decreaseStock($warehouse_id, $product_id, $quantity);
    }

    public function getProductStock(int $warehouse_id, int $product_id)
    {
        return $this->s_warehouse_product->getProductStock($warehouse_id, $product_id);
    }
}
