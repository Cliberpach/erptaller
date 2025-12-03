<?php

namespace App\Http\Controllers\Tenant\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Configuration\ConfigurationRequest;
use App\Models\Tenant\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ConfigurationController extends Controller
{
    public function index()
    {
        $configuration  =   Configuration::all();
        return view('maintenance.configuration.index', compact('configuration'));
    }

    /*
array:1 [ // app\Http\Controllers\Tenant\Maintenance\ConfigurationController.php:26
  "configuration_1" => "on"
]
*/
    public function store(ConfigurationRequest $request)
    {
        DB::beginTransaction();
        try {

            $data   =   $request->all();
            $configurations =   Configuration::all();
            foreach ($configurations as $key => $configuration) {
                $option_exists  =   array_key_exists('configuration_' . $configuration->id, $data);
                $configuration_bd   =   Configuration::findOrFail($configuration->id);
                if (!$option_exists) {
                    $configuration_bd->property =   '0';
                } else {
                    $configuration_bd->property =   '1';
                }
                $configuration_bd->save();
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'CONFIGURACIÓN GUARDADA']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
