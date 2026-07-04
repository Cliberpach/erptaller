<?php

namespace App\Http\Controllers\LandLord;

use App\Http\Controllers\Controller;
use App\Http\Services\Landlord\Dashboard\DashboardManager;
use Illuminate\View\View;

class ModuleController extends Controller
{
    private DashboardManager $s_manager;

    public function __construct()
    {
        $this->middleware('auth');
        $this->s_manager = new DashboardManager();
    }

    public function home(): View
    {
        return view('company.dashboard', $this->s_manager->resumen());
    }
}
