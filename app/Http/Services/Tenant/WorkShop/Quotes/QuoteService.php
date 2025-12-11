<?php

namespace App\Http\Services\Tenant\WorkShop\Quotes;

use App\Http\Controllers\FormatController;
use App\Http\Controllers\UtilController;
use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductService;
use App\Models\Company;
use App\Models\CompanyInvoice;
use App\Models\Department;
use App\Models\District;
use App\Models\Landlord\Color;
use App\Models\Province;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Maintenance\BankAccount\BankAccount;
use App\Models\Tenant\Warehouse;
use App\Models\Tenant\WorkShop\Quote\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;

class QuoteService
{
    private QuoteRepository $s_repository;
    private QuoteDto $s_dto;
    private QuoteValidation $s_validation;
    private WarehouseProductService $s_warehouse_product;

    public function __construct()
    {
        $this->s_repository =   new QuoteRepository();
        $this->s_dto        =   new QuoteDto();
        $this->s_warehouse_product  =   new WarehouseProductService();
        $this->s_validation =   new QuoteValidation($this->s_warehouse_product);
    }

    public function store(array $data): Quote
    {
        $data           =   $this->s_validation->validationStore($data);
        $dto            =   $this->s_dto->getDtoStore($data);

        $quote      =   $this->s_repository->insertQuote($dto);
        $this->s_repository->insertQuoteDetail($data['lst_products'], $data['lst_services'], $quote);

        return $quote;
    }

    public function update(array $data, int $id): Quote
    {
        $data       =   $this->s_validation->validationUpdate($data, $id);
        $dto        =   $this->s_dto->getDtoStore($data);

        $quote      =   $this->s_repository->updateQuote($dto, $id);
        return $quote;
    }

    public function destroy(int $id): Quote
    {
        return $this->s_repository->destroy($id);
    }

    public function pdfOne(int $id)
    {
        $data_quote =   $this->getQuote($id);
        $company    =   Company::findOrFail(1);
        $bank_accounts  =   BankAccount::where('status', 'ACTIVO')->get();
        $configuration  =   Configuration::findOrFail(1);

        return $pdf = Pdf::loadView(
            'workshop.quotes.reports.pdf_quote',
            compact(
                'data_quote',
                'company',
                'bank_accounts',
                'configuration'
            )
        );
    }

    public function getQuote(int $id): array
    {
        return $this->s_repository->getQuote($id);
    }

    public function convertOrderCreate(int $id): View
    {
        $quote_data                 =   $this->s_repository->getQuote($id);
        $quote                      =   $quote_data['quote'];

        $data_validated             =   $this->s_validation->validationConvertOrderCreate($quote_data);

        $igv                        =   round(Company::find(1)->igv, 2);
        $warehouses                 =   Warehouse::where('estado', 'ACTIVO')->get();
        $checks_inventory_vehicle   =   UtilController::getInventoryVehicleChecks();
        $technicians                =   UtilController::getTechnicians();

        $lst_products               =   FormatController::formatLstProducts($data_validated['valid_products']);
        $lst_services               =   FormatController::formatLstServices($quote_data['services']->toArray());

        $customer_formatted         =   FormatController::getFormatInitialCustomer($quote->customer_id);
        $vehicle_formatted          =   FormatController::getFormatInitialVehicle($quote->vehicle_id);

        $types_identity_documents   =   UtilController::getIdentityDocuments();
        $departments                =   Department::all();
        $districts                  =   District::all();
        $provinces                  =   Province::all();
        $company_invoice            =   CompanyInvoice::find(1);
        $years                      =   UtilController::getYears();
        $colors                     =   Color::where('status', 'ACTIVE')->get();
        $categories                 =   UtilController::getCategoriesProducts();
        $brands                     =   UtilController::getBrandsProducts();
        $configuration              =   $data_validated['configuration'];

        return view(
            'workshop.work_orders.create',
            compact(
                'igv',
                'warehouses',
                'checks_inventory_vehicle',
                'technicians',
                'quote',
                'lst_products',
                'lst_services',
                'customer_formatted',
                'vehicle_formatted',
                'types_identity_documents',
                'departments',
                'provinces',
                'districts',
                'company_invoice',
                'years',
                'colors',
                'categories',
                'brands',
                'configuration'
            )
        );
    }
}
