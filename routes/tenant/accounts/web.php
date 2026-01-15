<?php

use App\Http\Controllers\Tenant\Accounts\CustomerAccountController;
use App\Http\Controllers\Tenant\Accounts\SupplierAccountController;
use Illuminate\Support\Facades\Route;


Route::group(["prefix" => "cuentas"], function () {

    Route::group(["prefix" => "cliente"], function () {
        Route::get('index', [CustomerAccountController::class, 'index'])->name('tenant.cuentas.cliente.index');
        Route::get('getCustomerAccounts', [CustomerAccountController::class, 'getCustomerAccounts'])->name('tenant.cuentas.cliente.getCustomerAccounts');
        Route::get('getCustomerAccount/{id}', [CustomerAccountController::class, 'getCustomerAccount'])->name('tenant.cuentas.cliente.getCustomerAccount');
        Route::post('store-pago', [CustomerAccountController::class, 'storePago'])->name('tenant.cuentas.cliente.storePago');
        Route::get('pdf-one/{id}', [CustomerAccountController::class, 'pdfOne'])->name('tenant.cuentas.cliente.pdfOne');
    });

    Route::group(["prefix" => "proveedor"], function () {
        Route::get('index', [SupplierAccountController::class, 'index'])->name('tenant.cuentas.proveedor.index');
        Route::get('/getAll', [SupplierAccountController::class, 'getAll'])->name('tenant.cuentas.proveedor.getAll');
        Route::get('/getSupplierAccount/{id}', [SupplierAccountController::class, 'getSupplierAccount'])->name('tenant.cuentas.proveedor.getSupplierAccount');
        Route::post('/store-pago', [SupplierAccountController::class, 'storePago'])->name('tenant.cuentas.proveedor.storePago');
        Route::get('/pdf-one', [SupplierAccountController::class, 'pdfOne'])->name('tenant.cuentas.proveedor.pdfOne');
        Route::get('/reporteExcel', [SupplierAccountController::class, 'reporteExcel'])->name('grifo.contabilidad.cuentas_pagar.reporteExcel');
        Route::get('/reportePdf', [SupplierAccountController::class, 'reportePdf'])->name('grifo.contabilidad.cuentas_pagar.reportePdf');
    });
});
