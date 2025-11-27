<?php

namespace App\Http\Services\Tenant\Inventory\WarehouseProduct;

use App\Models\Tenant\NoteIncome;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WarehouseProductService
{

    public function increaseStock(int $warehouse_id, int $product_id, float $quantity)
    {
        $exists =   DB::table('warehouse_products')
            ->where('warehouse_id', $warehouse_id)
            ->where('product_id', $product_id)
            ->exists();

        if ($exists) {
            DB::table('warehouse_products')
                ->where('warehouse_id', $warehouse_id)
                ->where('product_id', $product_id)
                ->update([
                    'stock' => DB::raw("stock + $quantity"),
                    'updated_at' => Carbon::now(),
                ]);
        } else {
            DB::table('warehouse_products')->insert([
                'warehouse_id' => $warehouse_id,
                'product_id' => $product_id,
                'stock' => $quantity,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    public function decreaseStock(int $warehouse_id, int $product_id, float $quantity)
    {
        DB::table('warehouse_products')
            ->where('warehouse_id', $warehouse_id)
            ->where('product_id', $product_id)
            ->update([
                'stock' => DB::raw("stock - $quantity"),
                'updated_at' => Carbon::now(),
            ]);
    }

    public function getProductStock(int $warehouse_id, int $product_id)
    {
        $product_stock  =   DB::select(
                                'SELECT
                                                p.id,
                                                p.name AS product_name,
                                                c.id AS category_id,
                                                b.id AS brand_id,
                                                c.name AS category_name,
                                                b.name AS brand_name,
                                                p.sale_price,
                                                wp.stock
                                                FROM products AS p
                                                JOIN brands AS b ON b.id = p.brand_id
                                                JOIN categories AS c ON c.id = p.category_id
                                                JOIN warehouse_products AS wp ON wp.product_id = p.id
                                                WHERE
                                                p.id = ?
                                                AND wp.warehouse_id = ?',
                                [$product_id, $warehouse_id]
                            );

        if(count($product_stock) === 0){
            return null;
        }

        return $product_stock[0];
    }
}
