<?php

namespace App\Http\Services\Landlord\Dashboard;

class DashboardManager
{
    protected DashboardService $s_dashboard;

    public function __construct()
    {
        $this->s_dashboard = new DashboardService();
    }

    public function resumen(): array
    {
        return $this->s_dashboard->resumen();
    }
}
