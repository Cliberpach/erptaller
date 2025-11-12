<?php

namespace App\Http\Services\Tenant\Maintenance\Company;

use App\Models\Company;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CompanyService
{

    public function __construct() {}

    public function startInvoicing(int $company_id,string $type_sale_code)
    {
        //====== ACTUALIZAR FACTURACIÓN A INICIADA PARA EL TYPE SALE RESPECTIVO ======
        DB::table('document_serializations')
            ->where('company_id', $company_id)
            ->where('document_type_id', $type_sale_code)
            ->where('initiated', 'NO')
            ->update([
                'initiated'     => 'SI',
                'updated_at'    => Carbon::now()
            ]);
    }
}
