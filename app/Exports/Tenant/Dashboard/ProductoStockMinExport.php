<?php

namespace App\Exports\Tenant\Dashboard;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductoStockMinExport implements FromView, WithTitle
{
    protected $data;
    protected $filters;
    protected $empresa;

    public function __construct($data,$filters,$empresa)
    {
        $this->data     =   $data;
        $this->filters  =   $filters;
        $this->empresa  =   $empresa;
    }

    public function title(): string
    {
        return 'Stock Mínimo';
    }

    public function view(): View
    {
        return view('dashboard.dashboard.excel.excel', [
            'data'                      =>  $this->data,
            'filters'                   =>  $this->filters,
            'company'                   =>  $this->empresa
        ]);
    }
}
