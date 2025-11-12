<?php

namespace App\Http\Services\Tenant\Queries;

use App\Models\Company;
use App\Models\Product;
use App\Models\Tenant\Sale;
use Exception;
use Illuminate\Support\Facades\DB;

class QueriesManager
{

    protected QueriesService $s_queries;

    public function __construct() {
        $this->s_queries    =   new QueriesService();
    }

    public function generarDocumento(array $data):Sale{
        return $this->s_queries->generarDocumento($data);
    }

}
