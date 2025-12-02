<?php

namespace App\Http\Services\Tenant\Inventory\Product;

use App\Models\Product;

class ProductManager
{
    protected ProductService $s_product;

    public function __construct() {
        $this->s_product   =   new ProductService();
    }

    public function getProduct(int $producto_id){
        return $this->s_product->getProduct($producto_id);
    }

    public function store(array $data):Product{
        return $this->s_product->store($data);
    }
}
