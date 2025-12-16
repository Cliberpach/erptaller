<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Http\Controllers\FormatController;
use App\Http\Controllers\UtilController;
use App\Http\Services\Tenant\Accounts\CustomerAccount\CustomerAccountService;
use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductService;
use App\Http\Services\Tenant\Sale\Sale\SaleService;
use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderDto;
use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderRepository;
use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderValidation;
use App\Models\Company;
use App\Models\CompanyInvoice;
use App\Models\Department;
use App\Models\District;
use App\Models\Landlord\Color;
use App\Models\Province;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Warehouse;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class WorkOrderService
{
    private WorkOrderRepository $s_repository;
    private WorkOrderDto $s_dto;
    private WorkOrderValidation $s_validation;
    private WarehouseProductService $s_warehouse_product;
    private CustomerAccountService $s_customer_account;
    private SaleService $s_sale;

    public function __construct()
    {
        $this->s_repository         =   new WorkOrderRepository();
        $this->s_dto                =   new WorkOrderDto();
        $this->s_validation         =   new WorkOrderValidation();
        $this->s_warehouse_product  =   new WarehouseProductService();
        $this->s_customer_account   =   new CustomerAccountService();
        $this->s_sale               =   new SaleService();
    }

    public function store(array $data): WorkOrder
    {
        $data           =   $this->s_validation->validationStore($data);
        $dto            =   $this->s_dto->getDtoStore($data);

        $work_order     =   $this->s_repository->insertWorkOrder($dto);

        $dto_inventory      =   $this->s_dto->getDtoInventory($data['inventory_items'] ?? [], $work_order);
        $dto_technicians    =   $this->s_dto->getDtoTechnicians($data['technicians'] ?? [], $work_order);

        $this->s_repository->insertWorkOrderDetail($data['lst_products'], $data['lst_services'], $work_order);
        $this->s_repository->insertWorkInventory($dto_inventory);
        $this->s_repository->insertWorkTechnicians($dto_technicians);

        //======= CUENTA CLIENTE =======
        $this->s_customer_account->store(['work_order_id' => $work_order->id]);

        $dto_images =   $this->s_dto->getDtoOrderImages($data['vehicle_images'] ?? [], $work_order);
        $this->s_repository->insertWorkImages($dto_images);
        return $work_order;
    }

    public function update(array $data, int $id): WorkOrder
    {
        $data       =   $this->s_validation->validationUpdate($data, $id);

        $dto        =   $this->s_dto->getDtoStore($data);

        $work_order =   $this->s_repository->updateWorkOrder($dto, $id);

        if ($data['validation_stock_preview']) {
            $products_preview   =   $this->s_repository->getWorkProducts($id);
            foreach ($products_preview as $item) {
                $this->s_warehouse_product->increaseStock($item->warehouse_id, $item->product_id, $item->quantity);
            }
        }


        $this->s_repository->deleteDetailProduct($id);
        $this->s_repository->deleteDetailService($id);
        $this->s_repository->insertWorkOrderDetail($data['lst_products'], $data['lst_services'], $work_order);

        $this->s_repository->deleteDetailInventory($id);
        $dto_inventory      =   $this->s_dto->getDtoInventory($data['inventory_items'] ?? [], $work_order);
        $this->s_repository->insertWorkInventory($dto_inventory);

        $this->s_repository->deleteDetailTechnical($id);
        $dto_technicians    =   $this->s_dto->getDtoTechnicians($data['technicians'] ?? [], $work_order);
        $this->s_repository->insertWorkTechnicians($dto_technicians);

        $this->updateWorkImages($id, $data['vehicle_images'] ?? []);

        return $work_order;
    }

    public function destroy(int $id): WorkOrder
    {
        return $this->s_repository->destroy($id);
    }

    public function finish(int $id): WorkOrder
    {
        $work_order =   $this->s_repository->findWorkOrder($id);

        if (!$work_order->validation_stock) {
            $products   =   $this->s_repository->getWorkProducts($id);
            foreach ($products as $item) {
                $this->s_warehouse_product->decreaseStock($item->warehouse_id, $item->product_id, $item->quantity);
            }
        }

        return $this->s_repository->finish($id);
    }

    public function getWorkOrder(int $id): array
    {
        return $this->s_repository->getWorkOrder($id);
    }

    public function updateWorkImages(int $id, array $lst_images)
    {
        $images_bd  =   $this->s_repository->getWorkImageDetail($id);
        $bd_names   =   $images_bd->pluck('img_name')->toArray();

        $identify   =   $this->identifyImages($bd_names, $lst_images);

        $this->deleteImages($identify['deleted'], $images_bd);
        $this->registerNewImages($id, $identify['added'], $lst_images);
    }

    public function identifyImages(array $bd_names, array $lst_images)
    {
        $newFiles = [];
        $new_names = [];

        foreach ($lst_images as $file) {
            if ($file instanceof UploadedFile) {
                $newFiles[] = $file;
                $new_names[] = $file->getClientOriginalName();
            }
        }

        $keep = array_intersect($bd_names, $new_names);
        $deleted = array_diff($bd_names, $new_names);
        $added = array_diff($new_names, $bd_names);

        return ['keep' => $keep, 'deleted' => $deleted, 'added' => $added];
    }

    function deleteImages(array $lst_images, $images_bd)
    {
        foreach ($lst_images as $imgName) {

            $img = $images_bd->firstWhere('img_name', $imgName);

            if ($img) {
                $path = public_path($img->img_route);

                if (file_exists($path)) {
                    unlink($path);
                }

                $img->delete();
            }
        }
    }

    public function registerNewImages(int $id, array $add_names, array $lst_images)
    {
        $carpet_company =   Company::findOrFail(1)->files_route;
        $path = public_path("storage/{$carpet_company}/work_orders/images/");
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $currentCount = $this->s_repository->countWorkImage($id);

        foreach ($lst_images as $file) {

            if ($file instanceof UploadedFile && in_array($file->getClientOriginalName(), $add_names)) {

                $ext   = $file->getClientOriginalExtension();
                $name  = "{$id}_{$currentCount}." . $ext;
                $route = "storage/{$carpet_company}/work_orders/images/{$name}";

                $file->move($path, $name);

                $data   =   [
                    'work_order_id' => $id,
                    'img_name'      => $name,
                    'img_route'     => $route,
                ];

                $this->s_repository->insertWorkImage($data);

                $currentCount++;
            }
        }
    }

    public function pdfOne(int $id)
    {
        $data_order =   $this->getWorkOrder($id);
        $company    =   Company::findOrFail(1);

        return $pdf = Pdf::loadView('workshop.work_orders.reports.pdf_order', compact('data_order', 'company'));
    }

    public function invoiceCreate(int $id)
    {
        $igv                        =   round(Company::find(1)->igv, 2);
        $warehouses                 =   Warehouse::where('estado', 'ACTIVO')->get();

        $invoice_types              =   UtilController::getInvoiceTypes();
        $types_identity_documents   =   UtilController::getIdentityDocuments();
        $departments                =   Department::all();
        $districts                  =   District::all();
        $provinces                  =   Province::all();
        $company_invoice            =   CompanyInvoice::find(1);
        $years                      =   UtilController::getYears();
        $colors                     =   Color::where('status', 'ACTIVE')->get();
        $categories                 =   UtilController::getCategoriesProducts();
        $brands                     =   UtilController::getBrandsProducts();
        $configuration              =   Configuration::findOrFail(2);

        $order                      =   $this->getWorkOrder($id);
        $work_order                 =   $order['order'];
        $order['products']          =   $order['products']->where('invoiced',false);
        $order['services']          =   $order['services']->where('invoiced',false);

        $customer_formatted         =   FormatController::getFormatInitialCustomer($work_order->customer_id);
        $vehicle_formatted          =   FormatController::getFormatInitialVehicle($work_order->vehicle_id);

        $work_order                 =   $order['order'];
        $lst_products               =   FormatController::formatLstProducts($order['products']->toArray());
        $lst_services               =   FormatController::formatLstServices($order['services']->toArray());

        return view('sales.sale_document.create-ot', compact(
            'igv',
            'invoice_types',
            'warehouses',
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
            'configuration',
            'lst_products',
            'lst_services',
            'work_order'
        ));
    }


    /*
array:17 [ // app\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderService.php:264
  "_token" => "YqcvEqq5grlzzcaGuNm4u9CTRIp7Y9ntQzwa1yUI"
  "_method" => "POST"
  "warehouse_id" => "1"
  "invoice_type" => "65"
  "client_id" => "3"
  "vehicle_id" => "7"
  "product_id" => null
  "product_stock" => null
  "product_quantity" => null
  "product_price" => null
  "dt-orders-products_length" => "10"
  "service_id" => null
  "service_quantity" => null
  "service_price" => null
  "dt-orders-services_length" => "10"
  "lst_products" => "[{"warehouse_id":1,"id":1,"name":"BUJÍA 20 MM","category_name":"BUJÍAS","brand_name":"ASUS","sale_price":"14.990000","quantity":"2.000000","total":"29.980000"},{"warehouse_id":1,"id":13,"name":"ARRANCADOR Z","category_name":"LLAVES","brand_name":"NASCAR","sale_price":"1.000000","quantity":"1.000000","total":"1.000000"}]"
  "lst_services" => "[{"id":3,"name":"RENOVACIÓN DE MOTOR","sale_price":"2000.000000","quantity":"2.000000","total":"4000.000000"}]"
  "work_order_id" => "37"
]
*/
    public function invoiceStore($data):Sale
    {
        $data   =   $this->s_validation->validationInvoice($data);
        $sale   =   $this->s_sale->storeFromOrder($data);
        $this->s_repository->setInvoicedWorkProducts($data['work_order_id'], $sale, $data['lst_products']);
        $this->s_repository->setInvoicedWorkServices($data['work_order_id'], $sale, $data['lst_services']);

        $cant_products_not_invoiced  =   $this->s_repository->getWorkProducts($data['work_order_id'])->where('invoiced',false)->count();
        $cant_services_not_invoiced  =   $this->s_repository->getWorkServices($data['work_order_id'])->where('invoiced',false)->count();

        if($cant_products_not_invoiced + $cant_services_not_invoiced === 0 ){
            $this->s_repository->setWorkStatusInvoice($data['work_order_id'],'FACTURADO');
        }else{
            $this->s_repository->setWorkStatusInvoice($data['work_order_id'],'FACTURADO PARCIAL');
        }

        return $sale;
    }
}
