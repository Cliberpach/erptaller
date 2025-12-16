<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales_documents';

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_type_document',
        'customer_document_number',
        'customer_document_code',
        'customer_phone',

        'user_recorder_id',
        'user_recorder_name',

        'petty_cash_id',
        'petty_cash_name',
        'petty_cash_book_id',

        'type_sale_id',
        'type_sale_code',
        'type_sale_name',

        'igv_percentage',
        'subtotal',
        'igv_amount',
        'total',

        'legend',

        'method_pay_id_1',
        'amount_pay_1',
        'method_pay_id_2',
        'amount_pay_2',

        'correlative',
        'serie',

        'estado',

        'response_cdrZip',
        'response_success',
        'response_error_code',
        'response_error_message',

        'cdr_response_id',
        'cdr_response_code',
        'cdr_response_description',
        'cdr_response_notes',
        'cdr_response_reference',

        'ruta_cdr',
        'ruta_xml',
        'ruta_qr',

        'type',
        'work_order_id'
    ];
}
