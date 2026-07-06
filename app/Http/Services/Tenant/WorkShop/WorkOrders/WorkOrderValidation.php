<?php

namespace App\Http\Services\Tenant\WorkShop\WorkOrders;

use App\Http\Services\Tenant\Inventory\WarehouseProduct\WarehouseProductService;
use App\Models\Tenant\Accounts\CustomerAccount;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\WorkShop\Quote\Quote;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrder;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderProduct;
use App\Models\Tenant\WorkShop\WorkOrder\WorkOrderService;
use Exception;

class WorkOrderValidation
{
    private WarehouseProductService $s_warehouse_product;

    public function __construct()
    {
        $this->s_warehouse_product  =   new WarehouseProductService();
    }

    public function validationStore(array $data): array
    {
        $lst_products       =   json_decode($data['lst_products']);
        $lst_services       =   json_decode($data['lst_services']);
        $lst_technicians    =   json_decode($data['lst_technicians']) ?? [];

        if (empty($data['vehicle_id'])) {
            throw new Exception("DEBE SELECCIONAR UN VEHÍCULO");
        }

        if (count($lst_products) === 0 && count($lst_services) === 0) {
            throw new Exception("DEBE INGRESAR POR LO MENOS UN PRODUCTO O SERVICIO A LA ORDEN DE TRABAJO");
        }

        foreach (array_merge($lst_products, $lst_services) as $item) {
            if (isset($item->description) && mb_strlen($item->description) > 500) {
                throw new Exception("LA OBSERVACIÓN DE '{$item->name}' NO PUEDE SUPERAR LOS 500 CARACTERES");
            }
        }

        $quote_id   =   $data['quote_id'] ?? null;
        if ($quote_id) {
            $quote  =   Quote::findOrFail($quote_id);
            if ($quote->order_id) {
                throw new Exception("LA COTIZACIÓN YA FUE CONVERTIDA EN LA ORDEN OT-" . $quote->order_id);
            }
        }

        if (count($lst_technicians) > 3) {
            throw new Exception("SOLO SE PERMITEN MÁXIMO 3 TÉCNICOS POR ORDEN DE TRABAJO");
        }

        // Kilometraje (opcional): si viene, debe ser dígitos puros (entero >= 0).
        if (isset($data['mileage']) && trim((string) $data['mileage']) !== '' && ! ctype_digit(trim((string) $data['mileage']))) {
            throw new Exception("El kilometraje debe ser un número entero.");
        }

        $data['lst_products']       =   $lst_products;
        $data['lst_services']       =   $lst_services;
        $data['lst_technicians']    =   $lst_technicians;
        $data['validation_stock']   =   Configuration::findOrFail(2)->property === '1' ? true : false;

        return $data;
    }

    public function validationUpdate(array $data, int $id): array
    {
        $data = $this->validationStore($data);

        //========== VALIDATE ORDER STATUS ========
        $order  =   WorkOrder::findOrFail($id);
        if ($order->status === 'ANULADO') {
            throw new Exception("NO SE PUEDE MODIFICAR UNA ORDEN DE TRABAJO ANULADA");
        }

        if ($order->status === 'EXPIRADO') {
            throw new Exception("NO SE PUEDE MODIFICAR UNA ORDEN DE TRABAJO EXPIRADA");
        }

        //========= ACCOUNT STATUS =========
        $account    =   CustomerAccount::where('work_order_id', $order->id)->first();

        if ($account->status !== 'PENDIENTE') {
            throw new Exception("ESTA ORDEN TIENE UNA CUENTA CON ESTADO: " . $account->status . ", NO SE PERMITE EDITAR");
        }

        //========= FACTURACIÓN: no bajar cantidad ni quitar líneas ya facturadas =========
        $this->validationInvoicedLines($data['lst_products'], $data['lst_services'], $id);

        $data['validation_stock']           =   Configuration::findOrFail(2)->property === '1' ? true : false;
        $data['validation_stock_preview']   =   $order->validation_stock ? true : false;

        return $data;
    }

    /**
     * Bloquea el edit si intenta bajar la cantidad de una línea por debajo de
     * lo ya facturado (invoiced_quantity), o quitarla de la lista por
     * completo. Subir cantidad siempre está permitido (amplía el pendiente
     * por facturar). Ver docs/PLAN_OT_INVOICE.md.
     */
    private function validationInvoicedLines(array $lst_products, array $lst_services, int $work_order_id): void
    {
        $productos_nuevos = collect($lst_products)->keyBy('id');

        foreach (WorkOrderProduct::where('work_order_id', $work_order_id)->get() as $existente) {
            if ((float) $existente->invoiced_quantity <= 0) {
                continue;
            }

            $nuevo = $productos_nuevos->get($existente->product_id);

            if (! $nuevo) {
                throw new Exception("EL PRODUCTO '{$existente->product_name}' YA TIENE {$existente->invoiced_quantity} UNIDADES FACTURADAS, NO SE PUEDE ELIMINAR DE LA ORDEN.");
            }

            if ((float) $nuevo->quantity < (float) $existente->invoiced_quantity) {
                throw new Exception("EL PRODUCTO '{$existente->product_name}' YA TIENE {$existente->invoiced_quantity} UNIDADES FACTURADAS, NO SE PUEDE BAJAR LA CANTIDAD A {$nuevo->quantity}.");
            }
        }

        $servicios_nuevos = collect($lst_services)->keyBy('id');

        foreach (WorkOrderService::where('work_order_id', $work_order_id)->get() as $existente) {
            if ((float) $existente->invoiced_quantity <= 0) {
                continue;
            }

            $nuevo = $servicios_nuevos->get($existente->service_id);

            if (! $nuevo) {
                throw new Exception("EL SERVICIO '{$existente->service_name}' YA TIENE {$existente->invoiced_quantity} UNIDADES FACTURADAS, NO SE PUEDE ELIMINAR DE LA ORDEN.");
            }

            if ((float) $nuevo->quantity < (float) $existente->invoiced_quantity) {
                throw new Exception("EL SERVICIO '{$existente->service_name}' YA TIENE {$existente->invoiced_quantity} UNIDADES FACTURADAS, NO SE PUEDE BAJAR LA CANTIDAD A {$nuevo->quantity}.");
            }
        }
    }

    public function validationProduct($item, $validation_stock)
    {
        if ($validation_stock == '1') {
            $product_bd =   $this->s_warehouse_product->getProductStock($item->warehouse_id, $item->id);

            if (!$product_bd) {
                throw new Exception($item->name . ', PRODUCTO NO ENCONTRADO EN BD');
            }

            if ((float)$item->quantity > (float)$product_bd->stock) {
                throw new Exception(
                    "⚠ Stock insuficiente para: {$item->name}\n"
                        . "Stock disponible: " . round($product_bd->stock, 2) . "\n"
                        . "Cantidad solicitada: " . round($item->quantity, 2)
                );
            }
        }
    }

    public function validationInvoice(array $data)
    {
        $work_order_id  =   $data['work_order_id'];
        $lst_products   =   json_decode($data['lst_products']);
        $lst_services   =   json_decode($data['lst_services']);

        if (count($lst_products) === 0 && count($lst_services) === 0) {
            throw new Exception("LA ORDEN DE TRABAJO ESTÁ VACÍA");
        }

        foreach ($lst_products as $item) {
            $linea = WorkOrderProduct::where('work_order_id', $work_order_id)
                ->where('product_id', $item->id)
                ->first();

            $pendiente = $linea ? round((float) $linea->quantity - (float) $linea->invoiced_quantity, 6) : 0;

            if ((float) $item->quantity > $pendiente + 1e-6) {
                throw new Exception("{$item->name}: SOLO QUEDAN {$pendiente} UNIDADES PENDIENTES POR FACTURAR EN ESTA ORDEN (OT-{$work_order_id}).");
            }
        }

        foreach ($lst_services as $item) {
            $linea = WorkOrderService::where('work_order_id', $work_order_id)
                ->where('service_id', $item->id)
                ->first();

            $pendiente = $linea ? round((float) $linea->quantity - (float) $linea->invoiced_quantity, 6) : 0;

            if ((float) $item->quantity > $pendiente + 1e-6) {
                throw new Exception("{$item->name}: SOLO QUEDAN {$pendiente} UNIDADES PENDIENTES POR FACTURAR EN ESTA ORDEN (OT-{$work_order_id}).");
            }
        }

        $data['lst_products']   =   $lst_products;
        $data['lst_services']   =   $lst_services;

        return $data;
    }
}
