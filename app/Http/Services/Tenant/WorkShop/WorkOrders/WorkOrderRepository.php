<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductService;
use App\Models\Tenant\Accounts\CustomerAccount;
use App\Models\Tenant\Sale;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderImage;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderInventory;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderProduct;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderService;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderTechnical;

class WorkOrderRepository
{
    private WorkOrderDto $s_dto;
    private WorkOrderValidation $s_validation;
    private WarehouseProductService $s_warehouse_product;

    public function __construct()
    {
        $this->s_dto    =   new WorkOrderDto();
        $this->s_validation =   new WorkOrderValidation();
        $this->s_warehouse_product  =   new WarehouseProductService();
    }

    public function insertWorkOrder(array $dto): WorkOrder
    {
        return WorkOrder::create($dto);
    }

    public function findWorkOrder(int $id)
    {
        return WorkOrder::findOrFail($id);
    }

    public function insertWorkOrderDetail(array $lst_products, array $lst_services, WorkOrder $work_order)
    {
        foreach ($lst_products as $item) {

            $this->s_validation->validationProduct($item, $work_order->validation_stock);

            if ($work_order->validation_stock) {
                $this->s_warehouse_product->decreaseStock($item->warehouse_id, $item->id, $item->quantity);
            }

            $dto_item = $this->s_dto->getDtoOrderProduct($item, $work_order);
            WorkOrderProduct::create($dto_item);
        }
        foreach ($lst_services as $item) {
            $dto_item = $this->s_dto->getDtoOrderService($item, $work_order);
            WorkOrderService::create($dto_item);
        }
    }

    /**
     * Sincroniza el detalle en un EDIT: a diferencia de insertWorkOrderDetail
     * (OT nueva, nada que preservar), acá cada línea existente se actualiza
     * IN-PLACE (nunca se borra+recrea) para no perder invoiced_quantity. El
     * stock se ajusta solo por el DELTA de cantidad, no por el total.
     * WorkOrderValidation::validationInvoicedLines() ya garantizó que ninguna
     * línea facturada baja de cantidad ni desaparece -> acá no se vuelve a
     * chequear eso.
     */
    public function upsertWorkOrderDetail(array $lst_products, array $lst_services, WorkOrder $work_order): void
    {
        $existentesProductos = WorkOrderProduct::where('work_order_id', $work_order->id)->get()->keyBy('product_id');
        $idsNuevosProductos   = collect($lst_products)->pluck('id')->all();

        foreach ($lst_products as $item) {
            $existente = $existentesProductos->get($item->id);

            if ($existente) {
                $delta = round((float) $item->quantity - (float) $existente->quantity, 6);

                if ($work_order->validation_stock && $delta > 0) {
                    $this->s_validation->validationProduct((object) [
                        'id'           => $item->id,
                        'name'         => $item->name,
                        'warehouse_id' => $item->warehouse_id,
                        'quantity'     => $delta,
                    ], $work_order->validation_stock);
                    $this->s_warehouse_product->decreaseStock($item->warehouse_id, $item->id, $delta);
                } elseif ($work_order->validation_stock && $delta < 0) {
                    $this->s_warehouse_product->increaseStock($item->warehouse_id, $item->id, abs($delta));
                }

                $dto_item = $this->s_dto->getDtoOrderProduct($item, $work_order);
                $existente->update($dto_item);
            } else {
                $this->s_validation->validationProduct($item, $work_order->validation_stock);

                if ($work_order->validation_stock) {
                    $this->s_warehouse_product->decreaseStock($item->warehouse_id, $item->id, $item->quantity);
                }

                $dto_item = $this->s_dto->getDtoOrderProduct($item, $work_order);
                WorkOrderProduct::create($dto_item);
            }
        }

        foreach ($existentesProductos as $productId => $existente) {
            if (! in_array($productId, $idsNuevosProductos)) {
                if ($work_order->validation_stock) {
                    $this->s_warehouse_product->increaseStock($existente->warehouse_id, $existente->product_id, $existente->quantity);
                }
                $existente->delete();
            }
        }

        $existentesServicios = WorkOrderService::where('work_order_id', $work_order->id)->get()->keyBy('service_id');
        $idsNuevosServicios   = collect($lst_services)->pluck('id')->all();

        foreach ($lst_services as $item) {
            $existente = $existentesServicios->get($item->id);
            $dto_item  = $this->s_dto->getDtoOrderService($item, $work_order);

            if ($existente) {
                $existente->update($dto_item);
            } else {
                WorkOrderService::create($dto_item);
            }
        }

        foreach ($existentesServicios as $serviceId => $existente) {
            if (! in_array($serviceId, $idsNuevosServicios)) {
                $existente->delete();
            }
        }
    }

    public function insertWorkInventory(array $dto)
    {
        WorkOrderInventory::insert($dto);
    }

    public function insertWorkTechnicians(array $dto)
    {
        WorkOrderTechnical::insert($dto);
    }

    public function insertWorkImages(array $dto)
    {
        WorkOrderImage::insert($dto);
    }

    public function updateWorkOrder(array $dto, int $id): WorkOrder
    {
        $work_order    =   WorkOrder::findOrFail($id);
        $work_order->update($dto);
        return $work_order;
    }

    public function destroy(int $id): WorkOrder
    {
        $work_order            =   WorkOrder::findOrFail($id);
        $work_order->status    =   'ANULADO';
        $work_order->save();
        return $work_order;
    }

    public function finish(int $id): WorkOrder
    {
        $work_order            =   WorkOrder::findOrFail($id);
        $work_order->status    =   'FINALIZADO';
        $work_order->save();
        return $work_order;
    }

    public function deleteDetailInventory(int $id)
    {
        WorkOrderInventory::where('work_order_id', $id)->delete();
    }

    public function deleteDetailTechnical(int $id)
    {
        WorkOrderTechnical::where('work_order_id', $id)->delete();
    }

    public function deleteWorkImage(int $id)
    {

        WorkOrderImage::where('id', $id)->delete();
    }

    public function getWorkImageDetail(int $id)
    {
        return WorkOrderImage::where('work_order_id', $id)->get();
    }

    public function getWorkOrder(int $id): array
    {
        $order = WorkOrder::with([
            'vehicle.brand',
            'vehicle.model',
            'vehicle.year',
            'vehicle.color',
        ])->findOrFail($id);

        $products       =   WorkOrderProduct::where('work_order_id', $id)->get();
        $services       =   WorkOrderService::where('work_order_id', $id)->get();
        $inventory      =   WorkOrderInventory::where('work_order_id', $id)->get();
        $technicians    =   WorkOrderTechnical::where('work_order_id', $id)->get();
        $images         =   WorkOrderImage::where('work_order_id', $id)->get();

        $data   =   [
            'order' =>  $order,
            'products'  =>  $products,
            'services'  =>  $services,
            'inventory' =>  $inventory,
            'technicians'   =>  $technicians,
            'images'        =>  $images
        ];

        return $data;
    }

    public function countWorkImage(int $id)
    {
        return WorkOrderImage::where('work_order_id', $id)->count();
    }

    public function insertWorkImage(array $dto)
    {
        WorkOrderImage::create($dto);
    }

    public function getWorkProducts(int $id)
    {
        return WorkOrderProduct::where('work_order_id', $id)->get();
    }

    public function getWorkServices(int $id)
    {
        return WorkOrderService::where('work_order_id', $id)->get();
    }


    public function setInvoicedWorkProducts(int $work_order_id, Sale $sale, array $lst_products)
    {
        foreach ($lst_products as $product) {
            $linea = WorkOrderProduct::where('work_order_id', $work_order_id)
                ->where('product_id', $product->id)
                ->first();

            if (! $linea) {
                continue;
            }

            $nueva_invoiced_quantity = round((float) $linea->invoiced_quantity + (float) $product->quantity, 6);

            $linea->update([
                'invoiced_quantity' => $nueva_invoiced_quantity,
                // 'invoiced' queda como flag informativo: true cuando ya no queda pendiente.
                'invoiced' => $nueva_invoiced_quantity >= (float) $linea->quantity,
                'invoiced_sale_id' => $sale->id,
                'invoiced_sale_serie' => $sale->serie . '-' . $sale->correlative
            ]);
        }
    }

    public function setInvoicedWorkServices(int $work_order_id, Sale $sale, array $lst_services)
    {
        foreach ($lst_services as $service) {
            $linea = WorkOrderService::where('work_order_id', $work_order_id)
                ->where('service_id', $service->id)
                ->first();

            if (! $linea) {
                continue;
            }

            $nueva_invoiced_quantity = round((float) $linea->invoiced_quantity + (float) $service->quantity, 6);

            $linea->update([
                'invoiced_quantity' => $nueva_invoiced_quantity,
                'invoiced' => $nueva_invoiced_quantity >= (float) $linea->quantity,
                'invoiced_sale_id' => $sale->id,
                'invoiced_sale_serie' => $sale->serie . '-' . $sale->correlative
            ]);
        }
    }

    public function setWorkStatusInvoice(int $id, string $status): WorkOrder
    {
        $work_order                     =   WorkOrder::findOrFail($id);
        $work_order->status_invoice     =   $status;
        $work_order->save();
        return $work_order;
    }

    public function getCustomerAccount(int $work_order_id): ?CustomerAccount
    {
        return CustomerAccount::where('work_order_id', $work_order_id)->where('status', '<>', 'ANULADO')->first();
    }
}
