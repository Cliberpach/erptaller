<?php

namespace App\Http\Controllers\LandLord;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    
    public function apiRuc($ruc)
    {
        $url = "https://apiperu.dev/api/ruc/" . $ruc;
        $client = new \GuzzleHttp\Client(['verify' => false]);
        $token = 'c36358c49922c564f035d4dc2ff3492fbcfd31ee561866960f75b79f7d645d7d';
        $response = $client->get($url, [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ],
        ]);
   
        $estado = $response->getStatusCode();
        $data = $response->getBody()->getContents();
        return $data;
    }


/*
    TRUE DNI:
    {#2048 // app\Http\Controllers\Tenant\CustomerController.php:143
        +"success": true
        +"data": {#2044
            +"numero": "75608753"
            +"nombre_completo": "ALVA LUJAN, LUIS DANIEL"
            +"nombres": "LUIS DANIEL"
            +"apellido_paterno": "ALVA"
            +"apellido_materno": "LUJAN"
            +"codigo_verificacion": 9
            +"ubigeo_sunat": ""
            +"ubigeo": array:3 [
            0 => null
            1 => null
            2 => null
            ]
            +"direccion": ""
        }
        +"time": 0.046104907989502
        +"source": "apiperu.dev"
    }  

    FALSE DNI: 
    {#2048 // app\Http\Controllers\Tenant\CustomerController.php:143
        +"success": false
        +"message": "No se encontraron registros"
        +"time": 0.23091197013855
        +"source": "apiperu.dev"
    }

TRUE RUC:
{
data:{
    direccion: "CAR. PANAMERICANA SUR NRO. 241 PANAMERICANA SUR",…}
    condicion:"HABIDO"
    departamento:"ICA"
    direccion:"CAR. PANAMERICANA SUR NRO. 241  PANAMERICANA SUR"
    direccion_completa:"CAR. PANAMERICANA SUR NRO. 241  PANAMERICANA SUR, ICA - PISCO - PARACAS"
    distrito:"PARACAS"
    es_agente_de_percepcion:"SI"
    es_agente_de_percepcion_combustible:"NO"
    es_agente_de_retencion:"SI"
    es_buen_contribuyente:"NO"
    estado:"ACTIVO"
    nombre_o_razon_social:"CORPORACION ACEROS AREQUIPA S.A."
    provincia:"PISCO"
    ruc:"20370146994"
    ubigeo:["11", "1105", "110505"]
    ubigeo_sunat: "110505"
}
message:"OPERACIÓN COMPLETADA"
success: true
}
*/ 
    public function apiDni($dni)
    {

        $url = "https://apiperu.dev/api/dni/" . $dni;
        $client = new \GuzzleHttp\Client(['verify' => false]);
        $token = 'c36358c49922c564f035d4dc2ff3492fbcfd31ee561866960f75b79f7d645d7d';
        $response = $client->get($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ],
        ]);
        $estado = $response->getStatusCode();
        $data = $response->getBody()->getContents();

        return $data;
    }
}
