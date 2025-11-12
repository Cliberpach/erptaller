<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Luecano\NumeroALetras\NumeroALetras;

class NumberToLettersController extends Controller
{
    public static function numberToLetters($amount){

        $formatter          =   new NumeroALetras();
        $montoFormateado    =   number_format($amount, 2, '.', '');
        $partes             =   explode('.', $montoFormateado);
        $parteEntera        =   $partes[0];
        $decimales          =   $partes[1] ?? '00'; 
        $legend             =   'SON ' . $formatter->toWords((int)$parteEntera) . ' CON ' . $decimales . '/100 SOLES';
        return $legend;
        
    }
}
