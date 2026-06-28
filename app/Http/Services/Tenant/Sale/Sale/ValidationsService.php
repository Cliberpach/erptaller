<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Models\Company;
use App\Models\Landlord\Customer;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Product;
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

        //======= VALIDANDO USUARIO ACTUAL DEBE ESTAR EN UNA CAJA APERTURADA =======
        // Cajas Capa C (cobro): la venta cae en la caja que ESTE vendedor (recorder) abrió
        // en su sede activa. Determinístico por Candado 2 (un vendedor = una sola caja abierta).
        $sede_activa_id     =   session('sede_activa_id');

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
                                and pcb.status = "ABIERTO"
                                and pc.sede_id = ?',
            [$user_recorder->id, $sede_activa_id]
        );

        if (count($user_in_petty_cash) === 0) {
            throw new Exception("No tenés una caja aperturada en esta sede.");
        }

        //======= VALIDACION TIPO DE VENTA Y CLIENTE =========
        $type_sale      =   GeneralTableDetail::findOrFail($data['type_sale']);
        $type_sale_name =   null;
        $customer_id    =   $data['customer_id'];

        $customer       =   DB::select('select
                            c.id,
                            c.document_number,
                            c.name,
                            c.phone,
                            c.type_document_abbreviation,
                            c.type_document_code as type_document_code
                            from customers as c
                            where c.id = ?', [$customer_id]);

        //======== RUC Y BOLETA ======
        if ($customer[0]->type_document_abbreviation === 'RUC' && $type_sale->id == '3') {
            throw new Exception("NO SE PERMITEN BOLETAS DE VENTA CON RUC!!!");
        }

        //======== DNI Y FACTURA ======
        if ($customer[0]->type_document_abbreviation === 'DNI' && $type_sale->id == '1') {
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

        return (object)[
            'customer'          =>  $customer[0],
            'user_recorder'     =>  $user_recorder,
            'petty_cash'        =>  $user_in_petty_cash[0],
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
            'plate'             =>  $data['plate']
        ];
    }

    public static function validationLstPays(array $lstPays, object $amounts):array
    {
        // PASO 4 (Capa B): N líneas de pago. Cada una = método + cuenta (si electrónico)
        // + monto + n° operación. Se permite mismo método con cuentas distintas (Yape a
        // Juan + Yape a María); se prohíbe repetir la COMBINACIÓN método+cuenta.
        if (count($lstPays) === 0) {
            throw new Exception("El listado de pagos está vacío!!!");
        }

        if (count($lstPays) > 10) {
            throw new Exception("Máximo 10 líneas de pago.");
        }

        $totalAmount    =   0;
        $combos         =   [];   // método+cuenta ya usados (anti-duplicado)
        $indexPay       =   0;
        foreach ($lstPays as $pay) {
            $indexPay++;
            $methodId       =   $pay->method_pay;
            $bankAccountId  =   $pay->bank_account_id ?? null;

            $metodo = DB::table('payment_methods')->where('id', $methodId)->first();
            if (!$metodo) {
                throw new Exception("NO EXISTE EL " . $indexPay . '° MÉTODO DE PAGO EN LA BD!!!');
            }

            if ((float) $pay->amount <= 0 || !filter_var($pay->amount, FILTER_VALIDATE_FLOAT)) {
                throw new Exception("Los montos deben ser valores enteros,decimales mayores a 0");
            }
            $totalAmount += (float) $pay->amount;

            // ¿El método maneja cuentas? (tiene filas en el pivote)
            $tieneCuentas = DB::table('payment_method_accounts')->where('payment_method_id', $methodId)->exists();

            if ($bankAccountId) {
                // BLINDAJE: la cuenta debe pertenecer a ESE método (pivote). No se confía en el cliente.
                $perteneceAlMetodo = DB::table('payment_method_accounts')
                    ->where('payment_method_id', $methodId)
                    ->where('bank_account_id', $bankAccountId)
                    ->exists();
                if (!$perteneceAlMetodo) {
                    throw new Exception("La cuenta seleccionada no pertenece al método de pago.");
                }
            } else {
                // Sin cuenta: solo válido si el método NO maneja cuentas (efectivo).
                if ($tieneCuentas) {
                    throw new Exception("Debe seleccionar una cuenta para el método " . $metodo->description . ".");
                }
            }

            // No repetir la combinación método+cuenta (cubre 2 efectivos = mismo método+NULL).
            $key = $methodId . '-' . ($bankAccountId ?? 'NULL');
            if (in_array($key, $combos)) {
                throw new Exception("No se puede repetir el mismo método con la misma cuenta.");
            }
            $combos[] = $key;
        }

        if (round($totalAmount, 2) !== round((float) $amounts->total, 2)) {
            throw new Exception("La suma de los pagos no coincide con el total.");
        }

        $lstPays[]  =   (object)['method_pay' => null, 'amount' => null, 'bank_account_id' => null, 'operation_number' => null];

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
