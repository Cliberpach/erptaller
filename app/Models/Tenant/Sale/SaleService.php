<?php

namespace App\Models\Tenant\Sale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleService extends Model
{
    use HasFactory;

    protected $table = 'sales_documents_services';

    protected $fillable = [
        'sale_document_id',
        'service_id',

        'service_code',
        'service_unit',
        'service_description',
        'service_name',

        'quantity',
        'price_sale',
        'amount',

        // ===== SUNAT =====
        'mto_valor_unitario',
        'mto_valor_venta',
        'mto_base_igv',
        'porcentaje_igv',
        'igv',
        'tip_afe_igv',
        'total_impuestos',
        'mto_precio_unitario',

        'estado',
    ];
}
