<?php

use App\Http\Controllers\Tenant\Cash\ExitMoneyController;
use App\Http\Controllers\Tenant\Cash\PettyCashBookController;
use App\Http\Controllers\Tenant\Cash\PettyCashController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "cajas"], function () {

    Route::group(["prefix" => "cajas"], function () {
        Route::get('caja', [PettyCashController::class, 'index'])->name('tenant.cajas.caja');
        Route::get('getListCash', [PettyCashController::class, 'getListCash'])->name('tenant.cajas.getListCash');
        Route::get('getCash/{id}', [PettyCashController::class, 'getCash'])->name('tenant.cajas.getCash');
        Route::post('store', [PettyCashController::class, 'store'])->name('tenant.cajas.store');
        Route::put('update/{id}', [PettyCashController::class, 'update'])->name('tenant.cajas.update');
        Route::delete('destroy/{id}', [PettyCashController::class, 'destroy'])->name('tenant.cajas.destroy');
    });

    Route::group(["prefix" => "movimientos"], function () {
        Route::get('apertura-cierre', [PettyCashBookController::class, 'index'])->name('tenant.movimientos_caja.apertura_cierre');
        Route::get('pdf-one', [PettyCashBookController::class, 'showPDF'])->name('tenant.movimientos_caja.pdf');
        Route::post('open-cash', [PettyCashBookController::class, 'openPettyCash'])->name('tenant.movimientos_caja.abrirCaja');
        Route::get('getCashBooks', [PettyCashBookController::class, 'getCashBooks'])->name('tenant.cajas.getCashBooks');
        Route::get('getConsolidated', [PettyCashBookController::class, 'getConsolidated'])->name('tenant.movimientos_caja.getConsolidated');
        Route::post('close-cash', [PettyCashBookController::class, 'closePettyCash'])->name('tenant.movimientos_caja.closePettyCash');
    });

    Route::group(["prefix" => "egresos"], function () {
        Route::get('index', [ExitMoneyController::class, 'index'])->name('tenant.cajas.egreso');
        Route::get('create', [ExitMoneyController::class, 'create'])->name('tenant.egreso.create');
        Route::get('getEgresos', [ExitMoneyController::class, 'getExitMoneys'])->name('tenant.egreso.getExitMoneys');
        Route::post('store', [ExitMoneyController::class, 'store'])->name('tenant.egreso.store');
        Route::get('pdf-one/{id}', [ExitMoneyController::class, 'showPDF'])->name('tenant.egreso.pdf');
        Route::put('update/{id}', [ExitMoneyController::class, 'updateExit'])->name('tenant.egreso.update');
        Route::get('edit/{id}', [ExitMoneyController::class, 'editExit'])->name('tenant.egreso.edit');
        Route::delete('destroy/{id}', [ExitMoneyController::class, 'destroy'])->name('tenant.egreso.destroy');

        Route::post('proveedor/guardar', [PettyCashController::class, 'supplierStore'])->middleware('verificar.caja')->name('tenant.supplier.store');
        Route::post('comprobante-pago/guardar', [PettyCashController::class, 'proofPaymentStore'])->name('tenant.proof-payment.store');
    });
});
