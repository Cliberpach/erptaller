<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Models\Company;
use App\Models\Product;
use App\Models\Tenant\DocumentSerialization;
use Exception;
use Illuminate\Support\Facades\DB;

class CorrelativeService
{

    public function __construct() {}

/*
{#2181 // app\Http\Services\Tenant\Sale\Sale\SaleService.php:41
  +"correlative": "1"
  +"serie": "NV01"
}
*/
    public static function getCorrelative($type_sale):object
    {
        $correlative        =   null;
        $serie              =   null;

        //======= CONTABILIZANDO SI HAY DOCUMENTOS DE VENTA EMITIDOS PARA EL TYPE SALE ======
        $sales_documents    =   DB::select('SELECT
                                count(*) as cant
                                from sales_documents as sd
                                where sd.type_sale_code = ?', [$type_sale])[0];

        $document_serialization =   DocumentSerialization::where('company_id',1)->where('document_type_id',$type_sale)->first();

        //==== SI LA CANT ES 0 =====
        if ($sales_documents->cant === 0) {

            //====== INICIAR DESDE EL STARTING NUMBER =======
            $correlative        =   $document_serialization->start_number;
            $serie              =   $document_serialization->serie;
        } else {
            //======= EN CASO YA EXISTAN DOCUMENTOS DE VENTA DEL TYPE SALE ======
            $correlative        =   $sales_documents->cant  +   1;
            $serie              =   $document_serialization->serie;
        }


        return (object)['correlative' => $correlative, 'serie' => $serie];
    }
}
