<?php

namespace App\Http\Services\Tenant\Sale\Sale;

use App\Http\Controllers\Tenant\NumberToLettersController;
use App\Http\Services\Tenant\Accounts\CustomerAccount\CustomerAccountService;
use App\Http\Services\Tenant\Inventory\Kardex\KardexService;
use App\Http\Services\Tenant\Maintenance\Company\CompanyManager;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use App\Models\Tenant\DocumentSerialization;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Sale\SaleDocumentPayment;
use App\Support\PrecioVenta;
use Exception;

class SaleService
{
    private ValidationsService $s_validations;
    private CalculationsService $s_calculations;
    private CorrelativeService  $s_correlative;
    private SaleDetailService $s_detail;
    private SaleRepository $s_repository;
    private SaleDto $s_dto;
    private CompanyManager $s_company;
    private CustomerAccountService $s_customer_account;
    private KardexService $s_kardex;

    public function __construct()
    {
        $this->s_validations        =   new ValidationsService();
        $this->s_calculations       =   new CalculationsService();
        $this->s_correlative        =   new CorrelativeService();
        $this->s_detail             =   new SaleDetailService();
        $this->s_company            =   new CompanyManager();
        $this->s_repository         =   new SaleRepository();
        $this->s_dto                =   new SaleDto();
        $this->s_customer_account   =   new CustomerAccountService();
        $this->s_kardex             =   new KardexService();
    }

    public function store(array $data): Sale
    {
        //======== DOC VENTA ACTIVO ========
        $this->isActiveTypeSale($data['type_sale']);

        //======= VALIDACIÓN COMPLEJA =======
        $validated_data         =       $this->s_validations->validationStore($data);

        //======= ENFORCEMENT PRECIO (flag VEP): ventas+flag=No -> fuerza products.sale_price =======
        //======= ANTES de calcular montos, para que los totales usen el precio real, no el enviado =======
        $validated_data->lstSale =      PrecioVenta::forzarPrecios($validated_data->lstSale);

        //======= OBTENIENDO MONTOS GLOBALES =======
        $amounts                =       $this->s_calculations->calculateAmounts($validated_data->lstSale, $validated_data->igv_percentage);

        $lstPays                =       json_decode($data['lstPays']);
        $validated_pays         =       $this->s_validations->validationLstPays($lstPays, $amounts);

        //========= OBTENIENDO CORRELATIVO Y SERIE =========
        $data_correlative       =       $this->s_correlative->getCorrelative($validated_data->type_sale_id);

        //====== LEGENDA ========
        $legend                 =       NumberToLettersController::numberToLetters($amounts->total);

        //======= GUARDAR MAESTRO VENTA =======
        $dto                    =       $this->s_dto->getDtoStore($validated_data, $amounts, $legend, $validated_pays, $data_correlative);
        $sale                   =       $this->s_repository->insertSale($dto);

        //======= PASO 4: N LÍNEAS DE PAGO (tabla sales_document_payments) =======
        $this->storePayments($sale, $validated_pays);

        //========= REGISTRAR DETALLE TYPE PRODUCTOS =======
        if ($validated_data->type === 'PRODUCTOS') {
            $this->s_detail->storeDetail($sale, $validated_data);
        }

        //======= INICIAR FACTURACIÓN =======
        $this->s_company->startInvoicing(1, $validated_data->type_sale_id);

        if ($validated_data->payment_condition->type === 'CREDITO') {
            $data_account   =   ['sale_id' => $sale->id];
            $this->s_customer_account->store($data_account);
        }

        $this->s_kardex->storeFromSale($sale);

        return $sale;
    }

    /**
     * PASO 4 (Capa B): inserta las N líneas de pago. Salta el pad (method_pay null) que
     * agrega validationLstPays. Corre DENTRO de la transacción de la venta.
     */
    private function storePayments(Sale $sale, array $validated_pays): void
    {
        foreach ($validated_pays as $pay) {
            if (is_null($pay->method_pay)) {
                continue;
            }
            SaleDocumentPayment::create([
                'sale_document_id'  => $sale->id,
                'payment_method_id' => $pay->method_pay,
                'bank_account_id'   => $pay->bank_account_id ?? null,
                'amount'            => $pay->amount,
                'operation_number'  => $pay->operation_number ?? null,
            ]);
        }
    }

    public static function isActiveTypeSale($type_sale)
    {
        $invoice_type   =   GeneralTableDetail::findOrFail($type_sale);

        $is_active  =   DocumentSerialization::where('document_type_id', $type_sale)
            ->where('company_id', 1)
            ->first();

        if (!$is_active) {
            throw new Exception($invoice_type->name . ", NO ESTÁ ACTIVO EN LA EMPRESA");
        }
    }

    public function calculateAmounts($data)
    {
        $lst_products   =   $data['lst_products'];
        $lst_services   =   $data['lst_services'];

        $subtotal   =   0;
        $igv_amount =   0;
        $total      =   0;

        foreach ($lst_products as $item) {
            $total  +=  (float)$item->quantity * (float)$item->sale_price;
        }
        foreach ($lst_services as $item) {
            $total  +=  (float)$item->quantity * (float)$item->sale_price;
        }

        $subtotal       =   $total / ((100 + (float)$data['igv_percentage']) / 100);
        $igv_amount     =   $total - $subtotal;

        $data['subtotal']       =   $subtotal;
        $data['igv_amount']     =   $igv_amount;
        $data['total']          =   $total;

        return $data;
    }

    public function storeFromOrder($data): Sale
    {
        $this->isActiveTypeSale($data['invoice_type']);
        $data   =   $this->s_validations->validationStoreFromOrder($data);
        $data   =   $this->calculateAmounts($data);

        $dto    =   $this->s_dto->getDtoStoreFromOrder($data);
        $sale   =   $this->s_repository->insertSale($dto);

        //======= PASO 4: 1 línea de pago efectivo (OT→venta) =======
        SaleDocumentPayment::create([
            'sale_document_id'  => $sale->id,
            'payment_method_id' => 1, // EFECTIVO
            'bank_account_id'   => null,
            'amount'            => $sale->total,
            'operation_number'  => null,
        ]);

        $dto_services   =   $this->s_dto->getDtoServices($data['lst_services'], $sale);
        $this->s_repository->insertSaleService($dto_services);

        $dto_products   =   $this->s_dto->getDtoProducts($data['lst_products'], $sale);
        $this->s_repository->insertSaleProduct($dto_products);

        return $sale;
    }
}
