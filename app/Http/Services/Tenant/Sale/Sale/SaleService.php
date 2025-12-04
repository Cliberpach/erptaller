<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Http\Controllers\Tenant\NumberToLettersController;
use App\Http\Services\Tenant\Maintenance\Company\CompanyManager;
use App\Models\Company;
use App\Models\Tenant\Sale;
use Exception;
use Illuminate\Support\Facades\DB;

class SaleService
{
    private ValidationsService $s_validations;
    private CalculationsService $s_calculations;
    private CorrelativeService  $s_correlative;
    private SaleDetailService $s_detail;
    private CompanyManager $s_company;

    public function __construct()
    {
        $this->s_validations    =   new ValidationsService();
        $this->s_calculations   =   new CalculationsService();
        $this->s_correlative    =   new CorrelativeService();
        $this->s_detail         =   new SaleDetailService();
        $this->s_company        =   new CompanyManager();
    }

    public function store(array $data): Sale
    {
        //======== DOC VENTA ACTIVO ========
        $this->isActiveTypeSale($data['type_sale']);

        //======= VALIDACIÓN COMPLEJA =======
        $validated_data         =       $this->s_validations->validationStore($data);

        //======= OBTENIENDO MONTOS GLOBALES =======
        $amounts                =       $this->s_calculations->calculateAmounts($validated_data->lstSale, $validated_data->igv_percentage);

        $lstPays                =       json_decode($data['lstPays']);
        $validated_pays         =       $this->s_validations->validationLstPays($lstPays, $amounts);

        //========= OBTENIENDO CORRELATIVO Y SERIE =========
        $data_correlative       =       $this->s_correlative->getCorrelative($validated_data->type_sale_code);

        //====== LEGENDA ========
        $legend                 =       NumberToLettersController::numberToLetters($amounts->total);

        //======= GUARDAR MAESTRO VENTA =======
        $sale   =   $this->saveSale($validated_data, $amounts, $legend, $validated_pays, $data_correlative);

        //========= REGISTRAR DETALLE TYPE PRODUCTOS =======
        if ($validated_data->type === 'PRODUCTOS') {
            $this->s_detail->storeDetail($sale, $validated_data);
        }

        //========= REGISTRAR DETALLE TYPE RESERVAS =======
        /*if($validated_data->type === 'RESERVAS'){
            $this->s_detail->storeDetailReservations($sale,$validated_data);
        }*/

        //======= INICIAR FACTURACIÓN =======
        $this->s_company->startInvoicing(1, $validated_data->type_sale_code);

        return $sale;
    }

    public function saveSale(object $validated_data, object $amounts, $legend, array $validated_pays, object $data_correlative): Sale
    {
        $sale                           =   new Sale();

        //======= GUARDANDO CLIENTE =======
        $sale->customer_id              =   $validated_data->customer->id;
        $sale->customer_name            =   $validated_data->customer->name;
        $sale->customer_type_document   =   $validated_data->customer->type_document_abbreviation;
        $sale->customer_document_number =   $validated_data->customer->document_number;
        $sale->customer_document_code   =   $validated_data->customer->type_document_code;
        $sale->customer_phone           =   $validated_data->customer->phone;

        //======= GUARDANDO USUARIO REGISTRADOR =======
        $sale->user_recorder_id         =   $validated_data->user_recorder->id;
        $sale->user_recorder_name       =   $validated_data->user_recorder->name;

        //====== GUARDANDO DATOS DE LA CAJA Y MOVIMIENTO DEL USUARIO =====
        $sale->petty_cash_id            =   $validated_data->petty_cash->petty_cash_id;
        $sale->petty_cash_name          =   $validated_data->petty_cash->petty_cash_name;
        $sale->petty_cash_book_id       =   $validated_data->petty_cash->petty_cash_book_id;

        //======== TIPO DE VENTA ======
        $sale->type_sale_code           =   $validated_data->type_sale_code;
        $sale->type_sale_name           =   $validated_data->type_sale_name;

        //====== MONTOS ======
        $sale->igv_percentage           =   $validated_data->igv_percentage;
        $sale->subtotal                 =   $amounts->subtotal;
        $sale->igv_amount               =   $amounts->igv_amount;
        $sale->total                    =   $amounts->total;
        $sale->legend                   =   $legend;

        //======= PAGOS =====
        $sale->method_pay_id_1          =   $validated_pays[0]->method_pay;
        $sale->amount_pay_1             =   $validated_pays[0]->amount;

        $sale->method_pay_id_2          =   $validated_pays[1]->method_pay;
        $sale->amount_pay_2             =   $validated_pays[1]->amount;

        //======== CORRELATIVO Y SERIE =======
        $sale->correlative              =   $data_correlative->correlative;
        $sale->serie                    =   $data_correlative->serie;
        $sale->save();

        return $sale;
    }

    public static function isActiveTypeSale($type_sale)
    {

        $isActive   =   DB::select(
            'SELECT
                            ds.*
                            FROM document_serializations AS ds
                            WHERE
                            ds.company_id = ?
                            AND ds.document_type_id = ?',
            [Company::find(1)->id, $type_sale]
        );

        if (count($isActive) === 0) {
            throw new Exception("EL TIPO DE VENTA NO ESTÁ ACTIVO EN LA EMPRESA!!!");
        }
    }
}
