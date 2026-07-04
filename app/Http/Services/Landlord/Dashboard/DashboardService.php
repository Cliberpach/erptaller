<?php

namespace App\Http\Services\Landlord\Dashboard;

use App\Models\Landlord\Company;

class DashboardService
{
    public function resumen(): array
    {
        $totalEmpresas    = Company::where('status', '1')->count();
        $empresasActivas  = Company::where('status', '1')->where('block_account', 0)->count();

        return [
            'totalEmpresas'      => $totalEmpresas,
            'empresasActivas'    => $empresasActivas,
            'empresasBloqueadas' => $totalEmpresas - $empresasActivas,
        ];
    }
}
