<?php

namespace App\Http\Controllers\Tenant;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\GDLibRenderer;

class QRController extends Controller
{
    public static function generateQr($data){

        try {
            
            $company    =   Company::find(1);

            $renderer   =   new GDLibRenderer(400); 

            $writer     =   new Writer($renderer);
            
            $directory  =   public_path('storage/'.$company->files_route.'/qr/');

            if (!file_exists($directory)) {
                mkdir($directory, 0777, true); 
            }

            $data_object    = json_decode($data);
            $data_array     = [];

            foreach ($data_object as $key => $value) {
                $data_array[] = $value;
            }

            $data_string    =   implode('|', $data_array);
            $data_string    .=  '|';
            
            $qr_name        =   $data_object->serie.'-'.$data_object->correlativo.'.png';
            $path           =   $directory . $qr_name; 

            $writer->writeFile($data_string, $path);

            return response()->json(['success'=>true,
                                    'data'=>['ruta_qr'=>'storage/'.$company->files_route.'/qr/'.$qr_name]
            ]);

        } catch (\Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }

    }
}
