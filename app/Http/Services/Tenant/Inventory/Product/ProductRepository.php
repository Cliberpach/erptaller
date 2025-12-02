<?php

namespace App\Http\Services\Tenant\Inventory\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductRepository
{

    public function __construct() {}

    public function getProduct(int $product_id)
    {
        $product    =   DB::select('SELECT
                        p.id,
                        p.name,
                        br.name AS brand_name,
                        c.name AS category_name,
                        br.id AS brand_id,
                        c.id AS category_id
                        FROM products as p
                        INNER JOIN brands AS br ON br.id = p.brand_id
                        INNER JOIN categories AS c ON c.id = p.category_id
                        WHERE p.id = ?', [$product_id]);

        return $product;
    }

    public function insertProduct(array $data)
    {
        $product    =   Product::create($data);
        $product->load(['brand', 'category']);
        return $product;
    }
}
