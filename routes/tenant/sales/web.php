<?php

use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\SaleController;
use App\Http\Controllers\Tenant\Sales\PaymentMethodController;
use Illuminate\Support\Facades\Route;


Route::group(["prefix" => "ventas"], function () {

    Route::group(["prefix" => "ventas"], function () {
        Route::get('index', [SaleController::class, 'index'])->name('tenant.ventas.comprobante_venta')->middleware('validar.plan:ventas');
        Route::get('create', [SaleController::class, 'create'])->name('tenant.ventas.comprobante_venta.create')->middleware('validar.plan:ventas');
        Route::get('getProductos', [SaleController::class, 'getProductos'])->name('tenant.ventas.comprobante_venta.getProductos')->middleware('validar.plan:ventas');
        Route::get('validateStock', [SaleController::class, 'validateStock'])->name('tenant.ventas.comprobante_venta.validateStock')->middleware('validar.plan:ventas');
        Route::post('store', [SaleController::class, 'store'])->name('tenant.ventas.comprobante_venta.store')->middleware('validar.plan:ventas');
        Route::post('send_sunat', [SaleController::class, 'send_sunat'])->name('tenant.ventas.comprobante_venta.send_sunat')->middleware('validar.plan:ventas');
        Route::get('getSales', [SaleController::class, 'getSales'])->name('tenant.ventas.comprobante_venta.getSales')->middleware('validar.plan:ventas');
        Route::get('pdf_voucher/{id}/{size?}', [SaleController::class, 'pdf_voucher'])->name('tenant.ventas.comprobante_venta.pdf_voucher')->middleware('validar.plan:ventas');
        Route::get('downloadXml/{id}', [SaleController::class, 'downloadXml'])->name('tenant.ventas.comprobante_venta.downloadXml')->middleware('validar.plan:ventas');
        Route::get('downloadCdr/{id}', [SaleController::class, 'downloadCdr'])->name('tenant.ventas.comprobante_venta.downloadCdr')->middleware('validar.plan:ventas');

        Route::get('comprobante-electronico', [SaleController::class, 'electronicReceipt'])->name('tenant.ventas.comprobante_electronico');
        Route::get('cotizacion', [SaleController::class, 'quotation'])->name('tenant.ventas.cotizacion');
    });

    Route::group(["prefix" => "clientes"], function () {
        Route::get('cliente', [CustomerController::class, 'index'])->name('tenant.ventas.cliente');
        Route::get('nuevo-cliente/registrar', [CustomerController::class, 'create'])->name('tenant.ventas.cliente.create');
        Route::post('nuevo-cliente/guardar', [CustomerController::class, 'store'])->name('tenant.ventas.cliente.store');
        Route::get('editar-cliente/{id}/editar', [CustomerController::class, 'edit'])->name('tenant.ventas.cliente.edit');
        Route::put('editar-cliente/{id}/actualizar', [CustomerController::class, 'update'])->name('tenant.ventas.cliente.update');
        Route::delete('cliente/{id}/eliminar', [CustomerController::class, 'destroy'])->name('tenant.ventas.cliente.delete');
        Route::get('consult_document', [CustomerController::class, 'consult_document'])->name('tenant.ventas.cliente.consult_document');
        Route::get('getListCustomers', [CustomerController::class, 'getListCustomers'])->name('tenant.ventas.cliente.getListCustomers');
    });

    Route::group(["prefix" => "metodos_pago"], function () {
        //======= MÉTODOS DE PAGO =======
        Route::get('metodo_pago/index', [PaymentMethodController::class, 'index'])->name('tenant.ventas.metodo_pago');
        Route::post('metodo_pago/store', [PaymentMethodController::class, 'store'])->name('tenant.ventas.metodo_pago.store');
        Route::put('metodo_pago/update/{id}', [PaymentMethodController::class, 'update'])->name('tenant.ventas.metodo_pago.update');
        Route::get('metodo_pago/getPaymentMethods', [PaymentMethodController::class, 'getPaymentMethods'])->name('tenant.ventas.metodo_pago.getPaymentMethods');
        Route::get('assign-accounts/create/{id}', [PaymentMethodController::class, 'assignAccountsCreate'])->name('tenant.ventas.metodo_pago.assignAccountsCreate');
        Route::post('assign-accounts/store', [PaymentMethodController::class, 'assignAccountsStore'])->name('tenant.ventas.metodo_pago.assignAccountsStore');
    });
});
