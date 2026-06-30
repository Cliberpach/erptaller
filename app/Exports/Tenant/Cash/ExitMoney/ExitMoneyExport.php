<?php

namespace App\Exports\Tenant\Cash\ExitMoney;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExitMoneyExport implements FromView
{
    protected $data;
    protected $filters;
    protected $company;

    public function __construct($data, $filters, $company)
    {
        $this->data    =   $data;
        $this->filters =   $filters;
        $this->company =   $company;
    }

    public function view(): View
    {
        return view('cash.exit-money.reports.excel-all', [
            'data'    => $this->data,
            'filters' => $this->filters,
            'company' => $this->company,
        ]);
    }
}
