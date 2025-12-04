<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Http\Services\Tenant\Accounts\CustomerAccount\CustomerAccountService;
use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductService;
use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderDto;
use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderRepository;
use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderValidation;
use App\Models\Company;
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

    public function __construct()
    {
        $this->s_repository =   new WorkOrderRepository();
        $this->s_dto        =   new WorkOrderDto();
        $this->s_validation =   new WorkOrderValidation();
        $this->s_warehouse_product  =   new WarehouseProductService();
        $this->s_customer_account   =   new CustomerAccountService();
    }

    public function store(array $data): WorkOrder
    {
        $data           =   $this->s_validation->validationStore($data);
        $dto            =   $this->s_dto->getDtoStore($data);

        $work_order     =   $this->s_repository->insertWorkOrder($dto);

        $dto_inventory      =   $this->s_dto->getDtoInventory($data['inventory_items']??[], $work_order);
        $dto_technicians    =   $this->s_dto->getDtoTechnicians($data['technicians']??[], $work_order);

        $this->s_repository->insertWorkOrderDetail($data['lst_products'], $data['lst_services'], $work_order);
        $this->s_repository->insertWorkInventory($dto_inventory);
        $this->s_repository->insertWorkTechnicians($dto_technicians);

        //======= CUENTA CLIENTE =======
        $this->s_customer_account->store(['work_order_id'=>$work_order->id]);

        $dto_images =   $this->s_dto->getDtoOrderImages($data['vehicle_images']??[], $work_order);
        $this->s_repository->insertWorkImages($dto_images);
        return $work_order;
    }

    public function update(array $data, int $id): WorkOrder
    {
        $data       =   $this->s_validation->validationUpdate($data, $id);
        $dto        =   $this->s_dto->getDtoStore($data);

        $work_order =   $this->s_repository->updateWorkOrder($dto, $id);

        $products_preview   =   $this->s_repository->getWorkProducts($id);
        foreach ($products_preview as $item) {
            $this->s_warehouse_product->increaseStock($item->warehouse_id,$item->product_id,$item->quantity);
        }

        $this->s_repository->deleteDetailProduct($id);
        $this->s_repository->deleteDetailService($id);
        $this->s_repository->insertWorkOrderDetail($data['lst_products'], $data['lst_services'], $work_order);

        $this->s_repository->deleteDetailInventory($id);
        $dto_inventory      =   $this->s_dto->getDtoInventory($data['inventory_items']??[], $work_order);
        $this->s_repository->insertWorkInventory($dto_inventory);

        $this->s_repository->deleteDetailTechnical($id);
        $dto_technicians    =   $this->s_dto->getDtoTechnicians($data['technicians']??[], $work_order);
        $this->s_repository->insertWorkTechnicians($dto_technicians);

        $this->updateWorkImages($id, $data['vehicle_images']??[]);

        return $work_order;
    }

    public function destroy(int $id): WorkOrder
    {
        return $this->s_repository->destroy($id);
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
}
