<?php

namespace App\Http\Controllers\Tenant\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Configuration\ConfigurationRequest;
use App\Models\Tenant\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfigurationController extends Controller
{
    public function index(){
        $configuration  =   Configuration::all();
        return view('maintenance.configuration.index',compact('configuration'));
    }

/*
array:1 [ // app\Http\Controllers\Tenant\Maintenance\ConfigurationController.php:17
  "configuration_1" => "19:00:00"
]
*/
    public function store(ConfigurationRequest $request){
        DB::beginTransaction();
        try {

            //===== CONFIGURACIÓN 1 =======
            $configuration_1            =   Configuration::find(1);
            $configuration_1->property  =   $request->get('configuration_1');
            $configuration_1->update();

            DB::commit();

            return response()->json(['success'=>true,'message'=>'CONFIGURACIÓN GUARDADA']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }
    }
}
