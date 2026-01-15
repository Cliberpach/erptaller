<?php

namespace App\Exports\Tenant\Accounts\CustomerAccount;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CustomerAccountExport implements FromView
{
    protected $data;
    protected $filters;
    protected $company;

    public function __construct($data,$filters,$company)
    {
        $this->data         =   $data;
        $this->filters      =   $filters;
        $this->company      =   $company;
    }

    public function view(): View
    {
        return view('accounts.customer_accounts.reports.excel-all', [
            'data'                 =>  $this->data,
            'filters'              =>  $this->filters,
            'company'              =>  $this->company
        ]);
    }
}
