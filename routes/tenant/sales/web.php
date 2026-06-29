<?php

use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\SaleController;
use App\Http\Controllers\Tenant\Sales\PaymentConditionController;
use App\Http\Controllers\Tenant\Sales\PaymentMethodController;
use Illuminate\Support\Facades\Route;


Route::group(["prefix" => "ventas"], function () {

    // Permisos P3: piso del módulo ventas = can:ventas.ver; las acciones sensibles suman
    // su propio can: a nivel de ruta (doble capa: requiere ver + la acción).
    Route::group(["prefix" => "ventas", 'middleware' => 'can:ventas.ver'], function () {
        Route::get('index', [SaleController::class, 'index'])->name('tenant.ventas.comprobante_venta')->middleware('validar.plan:ventas');
        Route::get('create', [SaleController::class, 'create'])->name('tenant.ventas.comprobante_venta.create')->middleware('validar.plan:ventas');
        Route::get('getProductos', [SaleController::class, 'getProductos'])->name('tenant.ventas.comprobante_venta.getProductos')->middleware('validar.plan:ventas');
        Route::get('validateStock', [SaleController::class, 'validateStock'])->name('tenant.ventas.comprobante_venta.validateStock')->middleware('validar.plan:ventas');
        Route::post('store', [SaleController::class, 'store'])->name('tenant.ventas.comprobante_venta.store')->middleware(['validar.plan:ventas', 'can:ventas.crear']);
        Route::post('send_sunat', [SaleController::class, 'send_sunat'])->name('tenant.ventas.comprobante_venta.send_sunat')->middleware(['validar.plan:ventas', 'can:ventas.enviar_sunat']);
        Route::get('getSales', [SaleController::class, 'getSales'])->name('tenant.ventas.comprobante_venta.getSales')->middleware('validar.plan:ventas');
        Route::get('pdf_voucher/{id}/{size?}', [SaleController::class, 'pdf_voucher'])->name('tenant.ventas.comprobante_venta.pdf_voucher')->middleware('validar.plan:ventas');
        Route::get('downloadXml/{id}', [SaleController::class, 'downloadXml'])->name('tenant.ventas.comprobante_venta.downloadXml')->middleware('validar.plan:ventas');
        Route::get('downloadCdr/{id}', [SaleController::class, 'downloadCdr'])->name('tenant.ventas.comprobante_venta.downloadCdr')->middleware('validar.plan:ventas');
        Route::post('{id}/anular', [SaleController::class, 'anular'])->name('tenant.ventas.comprobante_venta.anular')->middleware(['validar.plan:ventas', 'can:ventas.anular']);
        Route::get('{id}/convert-data', [SaleController::class, 'getConvertData'])->name('tenant.ventas.comprobante_venta.convertData')->middleware('validar.plan:ventas');
        Route::post('{id}/convertir', [SaleController::class, 'convertir'])->name('tenant.ventas.comprobante_venta.convertir')->middleware(['validar.plan:ventas', 'can:ventas.crear']);

        Route::get('comprobante-electronico', [SaleController::class, 'electronicReceipt'])->name('tenant.ventas.comprobante_electronico');
        Route::get('cotizacion', [SaleController::class, 'quotation'])->name('tenant.ventas.cotizacion');
    });

    Route::group(["prefix" => "clientes", 'middleware' => 'can:ventas.clientes.gestionar'], function () {
        Route::get('index', [CustomerController::class, 'index'])->name('tenant.ventas.cliente');
        Route::get('create', [CustomerController::class, 'create'])->name('tenant.ventas.cliente.create');
        Route::post('store', [CustomerController::class, 'store'])->name('tenant.ventas.cliente.store');
        Route::get('edit/{id}', [CustomerController::class, 'edit'])->name('tenant.ventas.cliente.edit');
        Route::put('update/{id}', [CustomerController::class, 'update'])->name('tenant.ventas.cliente.update');
        Route::delete('destroy/{id}', [CustomerController::class, 'destroy'])->name('tenant.ventas.cliente.destroy');
        Route::get('consult_document', [CustomerController::class, 'consult_document'])->name('tenant.ventas.cliente.consult_document');
        Route::get('getListCustomers', [CustomerController::class, 'getListCustomers'])->name('tenant.ventas.cliente.getListCustomers');
        Route::get('getAll', [CustomerController::class, 'getAll'])->name('tenant.ventas.cliente.getAll');
    });

    // Métodos/Condiciones Pago: movidos a Mantenimiento (permiso mantenimiento.config_pago).
    // Quedan físicamente aquí (route names tenant.ventas.*) pero el candado es de mantenimiento.
    Route::group(["prefix" => "metodos_pago", 'middleware' => 'can:mantenimiento.config_pago.gestionar'], function () {
        //======= MÉTODOS DE PAGO =======
        Route::get('metodo_pago/index', [PaymentMethodController::class, 'index'])->name('tenant.ventas.metodo_pago');
        Route::post('metodo_pago/store', [PaymentMethodController::class, 'store'])->name('tenant.ventas.metodo_pago.store');
        Route::put('metodo_pago/update/{id}', [PaymentMethodController::class, 'update'])->name('tenant.ventas.metodo_pago.update');
        Route::get('metodo_pago/getPaymentMethods', [PaymentMethodController::class, 'getPaymentMethods'])->name('tenant.ventas.metodo_pago.getPaymentMethods');
        Route::get('assign-accounts/create/{id}', [PaymentMethodController::class, 'assignAccountsCreate'])->name('tenant.ventas.metodo_pago.assignAccountsCreate');
        Route::post('assign-accounts/store', [PaymentMethodController::class, 'assignAccountsStore'])->name('tenant.ventas.metodo_pago.assignAccountsStore');
    });

    Route::group(["prefix" => "condiciones_pago", 'middleware' => 'can:mantenimiento.config_pago.gestionar'], function () {
        //======= MÉTODOS DE PAGO =======
        Route::get('metodo_pago/index', [PaymentConditionController::class, 'index'])->name('tenant.ventas.condiciones_pago.index');
        Route::get('create', [PaymentConditionController::class, 'create'])->name('tenant.ventas.condiciones_pago.create');
        Route::get('edit/{id}', [PaymentConditionController::class, 'edit'])->name('tenant.ventas.condiciones_pago.edit');
        Route::post('store', [PaymentConditionController::class, 'store'])->name('tenant.ventas.condiciones_pago.store');
        Route::put('update/{id}', [PaymentConditionController::class, 'update'])->name('tenant.ventas.condiciones_pago.update');
        Route::get('getCondicionPago', [PaymentConditionController::class, 'getCondicionPago'])->name('tenant.ventas.condiciones_pago.getCondicionPago');
        Route::delete('destroy/{id}', [PaymentConditionController::class, 'destroy'])->name('tenant.ventas.condiciones_pago.destroy');
    });
});
