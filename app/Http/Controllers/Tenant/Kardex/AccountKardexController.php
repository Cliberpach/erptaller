<?php

namespace App\Http\Controllers\Tenant\Kardex;

use App\Http\Controllers\Controller;
use App\Http\Services\Tenant\Kardex\AccountKardexService;
use App\Models\Tenant\Maintenance\BankAccount\BankAccount;
use Illuminate\Http\Request;

class AccountKardexController extends Controller
{
    private AccountKardexService $service;

    public function __construct()
    {
        $this->service = new AccountKardexService();
    }

    public function index()
    {
        return view('kardex.cuentas.index', [
            'bank_accounts' => BankAccount::where('status', 'ACTIVO')->orderBy('holder')->get(),
            // Default mes en curso (como los otros reportes).
            'fecha_inicio'  => now()->startOfMonth()->toDateString(),
            'fecha_fin'     => now()->toDateString(),
        ]);
    }

    public function data(Request $request)
    {
        $bankAccountId = (int) $request->get('bank_account_id');
        $desde = $request->get('start_date') ?: now()->startOfMonth()->toDateString();
        $hasta = $request->get('end_date') ?: now()->toDateString();

        if (! $bankAccountId) {
            return response()->json([
                'apertura' => 0, 'movimientos' => [],
                'total_ingresos' => 0, 'total_egresos' => 0, 'saldo_final' => 0,
            ]);
        }

        return response()->json($this->service->kardex($bankAccountId, $desde, $hasta));
    }
}
