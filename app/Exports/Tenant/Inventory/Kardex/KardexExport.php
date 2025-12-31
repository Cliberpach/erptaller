<?php

namespace App\Exports\Tenant\Inventory\Kardex;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class KardexExport  implements FromView
{
    protected $resultado;
    protected $filters;
    protected $empresa;

    public function __construct($resultado,$filters,$empresa)
    {
        $this->resultado    =   $resultado;
        $this->filters      =   $filters;
        $this->empresa      =   $empresa;
    }

    public function view(): View
    {
        return view('inventory.kardex.excel.excel', [
            'data'                      =>  $this->resultado,
            'filters'                   =>  $this->filters,
            'company'                   =>  $this->empresa
        ]);
    }
}
