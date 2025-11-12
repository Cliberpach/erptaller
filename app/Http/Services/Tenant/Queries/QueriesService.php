<?php

namespace App\Http\Services\Tenant\Queries;

use App\Http\Services\Tenant\Sale\Sale\SaleManager;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Product;
use App\Models\Tenant\Sale;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QueriesService
{
    private SaleManager $s_sale;

    public function __construct() {
        $this->s_sale   =   new SaleManager();
    }

    public function generarDocumento(array $data):Sale{

        $customer_id        =   null;
        $lstReservations    =   json_decode($data['lstReservations']);

        if(count($lstReservations) === 0){
            throw new Exception("EL DETALLE DE RESERVACIONES ESTÁ VACÍO");
        }

        //======== OBTENIENDO CUSTOMER ID =======
        $reservation    =   Booking::findOrFail($lstReservations[0]->id);
        $customer_id    =   $reservation->customer_id;

        //====== DEMAS PARÁMETROS ========
        $total_amount   =   0;
        $lstSale        =   [];

        foreach ($lstReservations as $item) {
            $booking        =   Booking::findOrFail($item->id);
            if($booking->facturado === 'SI'){
                throw new Exception("LA RESERVA ".$booking->id." YA FUE FACTURADA");
            }
            $total_amount   +=  (float)$booking->total;
            $lstSale[]      =   (object)['id'=>$booking->id,'field_id'=>$booking->field_id,'cant'=>1,'sale_price'=>$booking->total];
        }

        $data_sale  =   [
            'type'              =>  'RESERVAS',
            'lstSale'           =>  json_encode($lstSale),
            'type_sale'         =>  $data['type_document'],
            'customer_id'       =>  $customer_id,
            'user_recorder_id'  =>  Auth::user()->id,
            'igv_percentage'    =>  Company::findOrFail(1)->igv,
            'lstPays'           =>  json_encode([(object)['method_pay'=>1,'amount'=>$total_amount]])
        ];

        $sale   =   $this->s_sale->store($data_sale);

        //======= CAMBIANDO ESTADO DE LAS RESERVAS ========
        foreach ($lstReservations as $item) {
            $booking                        =   Booking::findOrFail($item->id);
            $booking->facturado             =   'SI';
            $booking->sale_document_serie   =   $sale->serie.'-'.$sale->correlative;
            $booking->sale_document_id      =   $sale->id;
            $booking->update();
        }

        return $sale;

    }

}
