<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Models\Company;
use App\Models\Landlord\Customer;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Product;
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

        //======= VALIDANDO USUARIO ACTUAL DEBE ESTAR EN UNA CAJA APERTURADA =======
        $user_in_petty_cash =   DB::select(
            'SELECT
                                pc.name as petty_cash_name,
                                pcb.petty_cash_id,
                                pcb.id as petty_cash_book_id,
                                pcb.status
                                from petty_cash_books as pcb
                                inner join petty_cashes as pc on pc.id = pcb.petty_cash_id
                                where
                                pcb.user_id = ?
                                and pcb.status = "ABIERTO"',
            [$user_recorder->id]
        );

        if (count($user_in_petty_cash) === 0) {
            throw new Exception("EL USUARIO NO SE ENCUENTRA EN UNA CAJA APERTURADA!!!");
        }

        //======= VALIDACION TIPO DE VENTA Y CLIENTE =========
        $type_sale      =   $data['type_sale'];
        $type_sale_name =   null;
        $customer_id    =   $data['customer_id'];

        $customer       =   DB::connection('landlord')->select('select
                            c.id,
                            c.document_number,
                            c.name,
                            c.phone,
                            c.type_document_abbreviation,
                            c.type_document_code as type_document_code
                            from customers as c
                            where c.id = ?', [$customer_id]);

        //======== RUC Y BOLETA ======
        if ($customer[0]->type_document_abbreviation === 'RUC' && $type_sale === '3') {
            throw new Exception("NO SE PERMITEN BOLETAS DE VENTA CON RUC!!!");
        }

        //======== DNI Y FACTURA ======
        if ($customer[0]->type_document_abbreviation === 'DNI' && $type_sale === '1') {
            throw new Exception("NO SE PERMITEN FACTURAS DE VENTA CON DNI!!!");
        }

        if ($type_sale === '80') {
            $type_sale_name =   'NOTA DE VENTA';
        }
        if ($type_sale === '3') {
            $type_sale_name =   'BOLETA DE VENTA ELECTRÓNICA';
        }
        if ($type_sale === '1') {
            $type_sale_name =   'FACTURA ELECTRÓNICA';
        }

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

        return (object)[
            'customer'          =>  $customer[0],
            'user_recorder'     =>  $user_recorder,
            'petty_cash'        =>  $user_in_petty_cash[0],
            'type_sale_code'    =>  $type_sale,
            'type_sale_name'    =>  $type_sale_name,
            'igv_percentage'    =>  $data['igv_percentage'],
            'lstSale'           =>  $lstSale,
            'type'              =>  'PRODUCTOS'
        ];
    }

    public static function validationLstPays(array $lstPays, object $amounts):array
    {

        $methodPays =   array_column($lstPays, 'method_pay');

        if (count($lstPays) === 0) {
            throw new Exception("El listado de pagos está vacío!!!");
        }

        if (count($lstPays) > 2) {
            throw new Exception("Solo se aceptan 2 pagos como máximo!!!");
        }

        if (count($methodPays) !== count(array_unique($methodPays))) {
            throw new Exception("Los métodos de pago no pueden repetirse");
        }

        $totalAmount    =   0;
        $indexPay       =   0;
        foreach ($lstPays as $pay) {
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

        $lstPays[]  =   (object)['method_pay' => null, 'amount' => null];

        return $lstPays;
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
