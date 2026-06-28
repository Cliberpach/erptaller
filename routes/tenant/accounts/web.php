<?php

use App\Http\Controllers\Tenant\Accounts\CustomerAccountController;
use App\Http\Controllers\Tenant\Accounts\SupplierAccountController;
use Illuminate\Support\Facades\Route;


Route::group(["prefix" => "cuentas"], function () {

    // Permisos P3: Cuentas por cobrar (cliente) y por pagar (proveedor) separadas.
    Route::group(["prefix" => "cliente", 'middleware' => 'can:cuentas.cxc.ver'], function () {
        Route::get('index', [CustomerAccountController::class, 'index'])->name('tenant.cuentas.cliente.index');
        Route::get('getCustomerAccounts', [CustomerAccountController::class, 'getCustomerAccounts'])->name('tenant.cuentas.cliente.getCustomerAccounts');
        Route::get('getCustomerAccount/{id}', [CustomerAccountController::class, 'getCustomerAccount'])->name('tenant.cuentas.cliente.getCustomerAccount');
        Route::post('store-pago', [CustomerAccountController::class, 'storePago'])->name('tenant.cuentas.cliente.storePago');
        Route::get('pdf-one/{id}', [CustomerAccountController::class, 'pdfOne'])->name('tenant.cuentas.cliente.pdfOne');
        Route::get('/pdf-all', [CustomerAccountController::class, 'pdfAll'])->name('tenant.cuentas.cliente.pdfAll');
        Route::get('/excel-all', [CustomerAccountController::class, 'excelAll'])->name('tenant.cuentas.cliente.excelAll');
    });

    Route::group(["prefix" => "proveedor", 'middleware' => 'can:cuentas.cxp.ver'], function () {
        Route::get('index', [SupplierAccountController::class, 'index'])->name('tenant.cuentas.proveedor.index');
        Route::get('/getAll', [SupplierAccountController::class, 'getAll'])->name('tenant.cuentas.proveedor.getAll');
        Route::get('/getSupplierAccount/{id}', [SupplierAccountController::class, 'getSupplierAccount'])->name('tenant.cuentas.proveedor.getSupplierAccount');
        Route::post('/store-pago', [SupplierAccountController::class, 'storePago'])->name('tenant.cuentas.proveedor.storePago');
        Route::get('/pdf-one', [SupplierAccountController::class, 'pdfOne'])->name('tenant.cuentas.proveedor.pdfOne');
        Route::get('/pdf-all', [SupplierAccountController::class, 'pdfAll'])->name('tenant.cuentas.proveedor.pdfAll');
        Route::get('/excel-all', [SupplierAccountController::class, 'excelAll'])->name('tenant.cuentas.proveedor.excelAll');
    });
});
