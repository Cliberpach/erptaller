<?php

namespace App\Http\Controllers\Tenant\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupplierAccountController extends Controller
{
    public function index()
    {
        return view('accounts.supplier_accounts.index');
    }
}
