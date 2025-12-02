<?php

namespace App\Http\Services\Tenant\Inventory\Product;

use App\Http\Services\Tenant\Inventory\NoteIncome\NoteIncomeService;
use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductService;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductService
{
    private NoteIncomeService $s_note_income;
    private WarehouseProductService $s_warehouse_product;
    private ProductRepository $s_repository;

    public function __construct() {
        $this->s_note_income        =   new NoteIncomeService();
        $this->s_warehouse_product  =   new WarehouseProductService();
        $this->s_repository         =   new ProductRepository();
    }

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

    public function store(array $data):Product
    {
        //======== REGISTRAR PRODUCTO =======
        $product    =   $this->s_repository->insertProduct($data);

        //======= CREAR NOTA INGRESO O REGISTRAR PRODUCTO CON STOCK 0 ======
        if($product->stock == 0){
            $this->s_warehouse_product->increaseStock(1,$product->id,$product->stock);
        }else{
            $this->s_note_income->storeFromProduct($product);
        }

        //====== GUARDAR IMG =======
        $this->saveImagePublic($data['image']??null, $product);

        return $product;
    }

    public function saveImagePublic($file_img, $product)
    {
        if ($file_img) {

            $files_route        =   Company::first()->files_route;
            $path_destiny       =   public_path("storage/{$files_route}/products");

            //======== VERIFICAR DESTINO ========
            if (!File::exists($path_destiny)) {
                File::makeDirectory($path_destiny, 0755, true);
            }

            //======= ELIMINAR IMG PREVIA ========
            $this->deleteImg($product);

            $extension          =   $file_img->getClientOriginalExtension();
            $name_img           =   'product_' . uniqid() . '.' . $extension;
            $name_file          =   $name_img;

            $file_img->move($path_destiny, $name_file);

            $product->img_route =  "storage/{$files_route}/products/" . $name_img;
            $product->img_name  =   $name_img;
            $product->update();
        }
    }

    public function deleteImg(Product $product)
    {
        if ($product->img_route && file_exists(public_path($product->img_route))) {
            unlink(public_path($product->img_route));
            $product->img_route =   null;
            $product->img_name  =   null;
            $product->update();
        }
    }
}
