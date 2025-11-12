<?php

namespace App\Http\Services\Tenant\Maintenance\Company;

use App\Models\Company;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;

class CompanyManager
{

    protected CompanyService $s_company;

    public function __construct() {
        $this->s_company    =   new CompanyService();
    }

    public function startInvoicing(int $company_id,string $type_sale_code){
        $this->s_company->startInvoicing($company_id,$type_sale_code);
    }

}
