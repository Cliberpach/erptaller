<?php

namespace App\Http\Services\Tenant\Dashboard\Dashboard;

use Illuminate\Http\Request;

class DashboardManager
{
    public DashboardMarketService $s_dashboard_market;
    public DashboardGrifoService  $s_dashboard_grifo;

    public object $data;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->s_dashboard_market   =   new DashboardMarketService();
        $this->s_dashboard_grifo    =   new DashboardGrifoService();
    }

    public function getData(string $establecimiento,string $anio,string $mes):object{

        if($establecimiento === 'MARKET'){
            $this->data     =   $this->s_dashboard_market->getData($anio,$mes);
        }

        if($establecimiento === 'GRIFO'){
            $this->data     =   $this->s_dashboard_grifo->getData($anio,$mes);
        }

        return $this->data;
    }

    public function getStockMin(Request $request){

        $establecimiento    =   $request->get('establecimiento');

        if($establecimiento === 'MARKET'){
            $this->data     =   $this->s_dashboard_market->getStockMin($request);
        }

        if($establecimiento === 'GRIFO'){
            $this->data     =   $this->s_dashboard_grifo->getStockMin($request);
        }

        return $this->data;
    }
}
