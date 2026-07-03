<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Models\Tenant\Sale;
use App\Models\Tenant\Sale\SaleService;
use App\Models\Tenant\SaleDetail;
use Illuminate\Support\Facades\DB;

class SaleRepository
{
    public function insertSale(array $dto): Sale
    {
        return Sale::create($dto);
    }

    public function insertSaleService(array $dto)
    {
        SaleService::insert($dto);
    }

    public function insertSaleProduct(array $dto)
    {
        SaleDetail::insert($dto);
    }

    public function findSale(int $sale_id): Sale
    {
        return Sale::findOrFail($sale_id);
    }

    /**
     * Líneas de producto de la venta, normalizadas al shape que espera InvoiceService
     * (code/unit/description + montos SUNAT).
     */
    public function getDetailProducts(int $sale_id): array
    {
        return DB::table('sales_documents_details')
            ->select(
                'product_code as code',
                'product_unit as unit',
                'product_description as description',
                'quantity',
                'mto_valor_unitario',
                'mto_valor_venta',
                'mto_base_igv',
                'porcentaje_igv',
                'igv',
                'tip_afe_igv',
                'total_impuestos',
                'mto_precio_unitario'
            )
            ->where('sale_document_id', $sale_id)
            ->where('estado', 'ACTIVO')
            ->get()
            ->all();
    }

    /**
     * Líneas de servicio (mano de obra) de la venta, mismo shape normalizado que los productos.
     */
    public function getDetailServices(int $sale_id): array
    {
        return DB::table('sales_documents_services')
            ->select(
                'service_code as code',
                'service_unit as unit',
                'service_description as description',
                'quantity',
                'mto_valor_unitario',
                'mto_valor_venta',
                'mto_base_igv',
                'porcentaje_igv',
                'igv',
                'tip_afe_igv',
                'total_impuestos',
                'mto_precio_unitario'
            )
            ->where('sale_document_id', $sale_id)
            ->where('estado', 'ACTIVO')
            ->get()
            ->all();
    }

    /**
     * Persiste la respuesta de SUNAT. Usa sunat_status (columna real) — NO 'estado'
     * (columna fantasma que rompía el update: ver bug documentado en sales_facturacion.md).
     */
    public function saveSunatData(array $data, Sale $sale): Sale
    {
        $sale->response_success         = $data['response_success'];
        $sale->response_cdrZip          = $data['response_cdrZip'];
        $sale->response_error_code      = $data['response_error_code'];
        $sale->response_error_message   = $data['response_error_message'];
        $sale->cdr_response_id          = $data['cdr_response_id'];
        $sale->cdr_response_code        = $data['cdr_response_code'];
        $sale->cdr_response_description = $data['cdr_response_description'];
        $sale->cdr_response_notes       = $data['cdr_response_notes'];
        $sale->cdr_response_reference   = $data['cdr_response_reference'];
        $sale->ruta_xml                 = $data['ruta_xml'];
        $sale->ruta_cdr                 = $data['ruta_cdr'];
        $sale->sunat_status             = $data['sunat_status'];
        $sale->last_send_message        = $data['message'];
        $sale->save();

        return $sale;
    }
}
