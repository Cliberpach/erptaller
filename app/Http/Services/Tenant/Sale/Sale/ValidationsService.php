<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Http\Services\Tenant\Cash\PettyCashBook\PettyCashBookService;
use App\Models\Company;
use App\Models\Landlord\Customer;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Tenant\Sale\PaymentCondition\PaymentCondition;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class ValidationsService
{

    public function __construct() {}

    //====== RESPUESTA =======
    /*
   {#1987 // app\Http\Controllers\Tenant\SaleController.php:136
        +"customer": {#2022
            +"id": 1
            +"document_number": "99999999"
            +"name": "VARIOS"
            +"phone": "99999999"
            +"type_document_abbreviation": "DNI"
        }
        +"user_recorder": {#2024
            +"id": 1
            +"name": "SUPERADMIN"
        }
        +"petty_cash": {#2023
            +"petty_cash_name": "CAJA PRINCIPAL"
            +"petty_cash_id": 1
            +"petty_cash_book_id": 1
            +"status": "open"
        }
        +"type_sale_code": "127"
        +"type_sale_name": "NOTA DE VENTA"
    }
    */
    public static function validationStore($data): object
    {
        //====== VALIDANDO USUARIO REGISTRADOR DEBE EXISTIR ======
        $user_recorder  =   User::findOrFail($data['user_recorder_id']);

        if (!$user_recorder) {
            throw new Exception("EL USUARIO REGISTRADOR NO EXISTE EN LA BD!!!");
        }

        $cash_service   =   new PettyCashBookService();
        $petty_cash     =   $cash_service->getCashBookUser($user_recorder->id);

        if (!$petty_cash) {
            throw new Exception("EL USUARIO NO SE ENCUENTRA EN UNA CAJA APERTURADA!!!");
        }

        //======= VALIDACION TIPO DE VENTA Y CLIENTE =========
        $type_sale      =   GeneralTableDetail::findOrFail($data['type_sale']);
        $type_sale_name =   null;

        $customer       =   Customer::findOrFail($data['customer_id']);

        //======== RUC Y BOLETA ======
        if ($customer->type_document_abbreviation === 'RUC' && $type_sale->id == '3') {
            throw new Exception("NO SE PERMITEN BOLETAS DE VENTA CON RUC!!!");
        }

        //======== DNI Y FACTURA ======
        if ($customer->type_document_abbreviation === 'DNI' && $type_sale->id == '1') {
            throw new Exception("NO SE PERMITEN FACTURAS DE VENTA CON DNI!!!");
        }


        $type_sale_name =   $type_sale->name;

        //======= VALIDANDO DETALLE DE LA VENTA =======
        $lstSale    =   json_decode($data['lstSale']);
        if (count($lstSale) === 0) {
            throw new Exception("EL DETALLE DE LA VENTA ESTÁ VACÍO!!!");
        }

        //====== VALIDANDO IGV PORCENTAJE DE LA COMPAÑIA =====
        $company    =   Company::find(1);
        if ($data['igv_percentage'] != $company->igv) {
            throw new Exception("EL PORCENTAJE DE IGV DEL DOCUMENTO DE VENTA NO CORRESPONDE AL DE LA EMPRESA!!!");
        }

        //========= DATES =========
        $registration_date      =   now();
        $payment_condition      =   PaymentCondition::findOrFail($data['payment_condition_id']);
        $nro_days               =   (int) $payment_condition->nro_days;
        $expiration_date        =   $registration_date->copy()->addDays($nro_days);


        $lst_pays               =   json_decode($data['lstPays']);


        return (object)[
            'customer'          =>  $customer,
            'user_recorder'     =>  $user_recorder,
            'petty_cash'        =>  $petty_cash,
            'type_sale_id'      =>  $type_sale->id,
            'type_sale_code'    =>  $type_sale->symbol,
            'type_sale_name'    =>  $type_sale_name,
            'igv_percentage'    =>  $data['igv_percentage'],
            'lstSale'           =>  $lstSale,
            'type'              =>  'PRODUCTOS',

            'expiration_date'   =>  $expiration_date,
            'registration_date' =>  $registration_date,
            'payment_condition' =>  $payment_condition,

            'vehicle_id'        =>  $data['vehicle_id'],
            'plate'             =>  $data['plate'],
            'lst_pays'          =>  $lst_pays
        ];
    }

    public static function validationLstPays(object $data, object $amounts): object
    {
        $lst_pays               =   $data->lst_pays;
        $payment_condition_id   =   $data->payment_condition->id;

        $methodPays             =   array_column($lst_pays, 'method_pay');

        if ($payment_condition_id !== 1) {
            $lst_pays  =   [
                (object)['method_pay' => 1, 'amount' => 0],
                (object)['method_pay' => 1, 'amount' => 0]
            ];
            $data->lst_pays =   $lst_pays;
            return $data;
        }

        if (count($lst_pays) === 0) {
            throw new Exception("El listado de pagos está vacío!!!");
        }

        if (count($lst_pays) > 2) {
            throw new Exception("Solo se aceptan 2 pagos como máximo!!!");
        }

        if (count($methodPays) !== count(array_unique($methodPays))) {
            throw new Exception("Los métodos de pago no pueden repetirse");
        }

        $totalAmount    =   0;
        $indexPay       =   0;
        foreach ($lst_pays as $pay) {
            $indexPay++;
            $existsPaymentMethod = DB::table('payment_methods')->where('id', $pay->method_pay)->exists();
            if (!$existsPaymentMethod) {
                throw new Exception("NO EXISTE EL " . $indexPay . '° MÉTODO DE PAGO EN LA BD!!!');
            }

            if ((float) $pay->amount <= 0 || !filter_var($pay->amount, FILTER_VALIDATE_FLOAT)) {
                throw new Exception("Los montos deben ser valores enteros,decimales mayores a 0");
            }
            $totalAmount += (float) $pay->amount;
        }

        if (round($totalAmount, 2) !== round((float) $amounts->total, 2)) {
            throw new Exception("La suma de los pagos no coincide con el total.");
        }

        $lst_pays[]  =   (object)['method_pay' => null, 'amount' => null];
        $data->lst_pays =   $lst_pays;
        return $data;
    }

    public static function validationStoreFromOrder($data): array
    {
        //======= VALIDACION TIPO DE VENTA Y CLIENTE =========
        $type_sale      =   $data['invoice_type'];
        $customer_id    =   $data['client_id'];

        $customer       =   Customer::findOrFail($customer_id);
        $invoice_tpye   =   GeneralTableDetail::findOrFail($type_sale);

        //======== RUC Y BOLETA ======
        if ($customer->type_document_abbreviation === 'RUC' && $invoice_tpye->parameter === 'B') {
            throw new Exception("NO SE PERMITEN BOLETAS DE VENTA CON RUC!!!");
        }

        //======== DNI Y FACTURA ======
        if ($customer->type_document_abbreviation === 'DNI' && $invoice_tpye->parameter === 'F') {
            throw new Exception("NO SE PERMITEN FACTURAS DE VENTA CON DNI!!!");
        }

        //======= VALIDANDO DETALLE DE LA VENTA =======
        $lst_products   =   $data['lst_products'];
        $lst_services   =   $data['lst_services'];

        if (count($lst_products) === 0 && count($lst_services) === 0) {
            throw new Exception("EL DETALLE DE LA VENTA ESTÁ VACÍO!!!");
        }

        //====== VALIDANDO IGV PORCENTAJE DE LA COMPAÑIA =====
        $company    =   Company::find(1);

        $data['customer']       =   $customer;
        $data['invoice_type']   =   $invoice_tpye;
        $data['igv_percentage'] =   $company->igv;
        $data['lst_products']   =   $lst_products;
        $data['lst_services']   =   $lst_services;
        $data['type']           =   'PRODUCTOS';

        return $data;
    }
}
