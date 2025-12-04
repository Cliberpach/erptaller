<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Http\Services\Tenant\Inventory\Kardex\KardexManager;
use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductManager;
use App\Models\Company;
use App\Models\Product;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleDetail;
use App\Models\Tenant\SaleDetailBooking;
use Exception;
use Illuminate\Support\Facades\DB;

class SaleDetailService
{

    private WarehouseProductManager $s_warehouse_product;
    private KardexManager $s_kardex;

    public function __construct()
    {
        $this->s_warehouse_product  =   new WarehouseProductManager();
        $this->s_kardex             =   new KardexManager();
    }

    public function storeDetail(Sale $sale, object $validated_data)
    {
        foreach ($validated_data->lstSale as $product) {

            //========= VALIDANDO PRODUCTO ========
            $product_exists =   $this->s_warehouse_product->getProductStock(1, $product->id);

            if (!$product_exists) {
                throw new Exception("EL PRODUCTO NO EXISTE EN EL ALMACÉN!!!");
            }

            if ($product_exists->stock < $product->cant) {
                throw new Exception("EL STOCK (" . $product_exists->stock . "), " . "ES MENOR A LA CANTIDAD (" . $product->cant . ")");
            }


            //======= GRABAR DETALLE DE VENTA =======
            $detail                     =   new SaleDetail();
            $detail->sale_document_id   =   $sale->id;
            $detail->warehouse_id       =   1;
            $detail->product_id         =   $product->id;
            $detail->product_name       =   $product_exists->product_name;
            $detail->brand_id           =   $product_exists->brand_id;
            $detail->brand_name         =   $product_exists->brand_name;
            $detail->category_id        =   $product_exists->category_id;
            $detail->category_name      =   $product_exists->category_name;
            $detail->quantity           =   $product->cant;
            $detail->price_sale         =   $product_exists->sale_price;
            $detail->amount             =   $product->cant * $product_exists->sale_price;

            /*====== SUNAT =====*/
            $detail->product_code           =   'P-' . $product_exists->id . $product_exists->category_id . $product_exists->brand_id;
            $detail->product_unit           =   'NIU';
            $detail->product_description    =   $product_exists->brand_name . '-' . $product_exists->product_name;
            $detail->mto_valor_unitario     =   (float)($product_exists->sale_price / 1.18);
            $detail->mto_valor_venta        =   (float)($detail->amount / 1.18);
            $detail->mto_base_igv           =   (float)($detail->amount / 1.18);
            $detail->porcentaje_igv         =   $validated_data->igv_percentage;
            $detail->igv                    =   (float)($detail->amount) - (float)($detail->amount / 1.18);
            $detail->tip_afe_igv            =   10;
            $detail->total_impuestos        =   (float)($detail->amount) - (float)($detail->amount / 1.18);
            $detail->mto_precio_unitario    =   (float)($product_exists->sale_price);
            $detail->save();

            //======= RESTAR STOCK ======
            $this->s_warehouse_product->decreaseStock(1, $product->id, $product->cant);

            //====== GUARDANDO KARDEX DEL DETALLE ======
            $this->s_kardex->store($sale, $detail, 'OUT', 'SALE');

        }
    }

    public function storeDetailReservations(Sale $sale,object $validated_data){
        foreach ($validated_data->lstSale as $booking) {

            //======= GRABAR DETALLE DE VENTA =======
            $detail                     =   new SaleDetailBooking();
            $detail->sale_document_id   =   $sale->id;
            $detail->booking_id         =   $booking->id;

            $detail->field_id           =   $booking->field_id;
            $detail->quantity           =   $booking->cant;
            $detail->price_sale         =   $booking->sale_price;
            $detail->amount             =   $booking->cant * $booking->sale_price;

            /*====== SUNAT =====*/
            $detail->product_code           =   'R-' . $booking->id;
            $detail->product_unit           =   'NIU';
            $detail->product_description    =   'R-' . $booking->id;
            $detail->mto_valor_unitario     =   (float)($booking->sale_price / 1.18);
            $detail->mto_valor_venta        =   (float)($detail->amount / 1.18);
            $detail->mto_base_igv           =   (float)($detail->amount / 1.18);
            $detail->porcentaje_igv         =   $validated_data->igv_percentage;
            $detail->igv                    =   (float)($detail->amount) - (float)($detail->amount / 1.18);
            $detail->tip_afe_igv            =   10;
            $detail->total_impuestos        =   (float)($detail->amount) - (float)($detail->amount / 1.18);
            $detail->mto_precio_unitario    =   (float)($booking->sale_price);
            $detail->save();

        }
    }
}
