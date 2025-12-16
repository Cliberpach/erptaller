<?php

use App\Http\Controllers\Tenant\WorkShop\BrandController;
use App\Http\Controllers\Tenant\WorkShop\ColorController;
use App\Http\Controllers\Tenant\WorkShop\ModelController;
use App\Http\Controllers\Tenant\WorkShop\QuoteController;
use App\Http\Controllers\Tenant\WorkShop\ServiceController;
use App\Http\Controllers\Tenant\WorkShop\VehicleController;
use App\Http\Controllers\Tenant\WorkShop\WorkOrderController;
use App\Http\Controllers\Tenant\WorkShop\YearController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "taller"], function () {

    Route::group(["prefix" => "ordenes_trabajo"], function () {

        Route::get('index', [WorkOrderController::class, 'index'])->name('tenant.taller.ordenes_trabajo.index');
        Route::get('create', [WorkOrderController::class, 'create'])->name('tenant.taller.ordenes_trabajo.create');
        Route::get('edit/{id}', [WorkOrderController::class, 'edit'])->name('tenant.taller.ordenes_trabajo.edit');
        Route::get('getWorkOrders', [WorkOrderController::class, 'getWorkOrders'])->name('tenant.taller.ordenes_trabajo.getWorkOrders');
        Route::post('store', [WorkOrderController::class, 'store'])->name('tenant.taller.ordenes_trabajo.store');
        Route::get('getWorkOrder/{id}', [WorkOrderController::class, 'getWorkOrder'])->name('tenant.taller.ordenes_trabajo.getWorkOrder');
        Route::put('update/{id}', [WorkOrderController::class, 'update'])->name('tenant.taller.ordenes_trabajo.update');
        Route::delete('destroy/{id}', [WorkOrderController::class, 'destroy'])->name('tenant.taller.ordenes_trabajo.destroy');
        Route::get('pdf/{id}', [WorkOrderController::class, 'pdfOne'])->name('tenant.taller.ordenes_trabajo.pdfOne');
        Route::post('finish/{id}', [WorkOrderController::class, 'finish'])->name('tenant.taller.ordenes_trabajo.finish');

        Route::get('invoice-create/{id}', [WorkOrderController::class, 'invoiceCreate'])->name('tenant.taller.ordenes_trabajo.invoiceCreate');
        Route::post('invoice-store', [WorkOrderController::class, 'invoiceStore'])->name('tenant.taller.ordenes_trabajo.invoiceStore');
    });

    Route::group(["prefix" => "cotizaciones"], function () {

        Route::get('index', [QuoteController::class, 'index'])->name('tenant.taller.cotizaciones.index');
        Route::get('create', [QuoteController::class, 'create'])->name('tenant.taller.cotizaciones.create');
        Route::get('edit/{id}', [QuoteController::class, 'edit'])->name('tenant.taller.cotizaciones.edit');
        Route::get('getQuotes', [QuoteController::class, 'getQuotes'])->name('tenant.taller.cotizaciones.getQuotes');
        Route::post('store', [QuoteController::class, 'store'])->name('tenant.taller.cotizaciones.store');
        Route::get('getQuote/{id}', [QuoteController::class, 'getQuote'])->name('tenant.taller.cotizaciones.getQuote');
        Route::put('update/{id}', [QuoteController::class, 'update'])->name('tenant.taller.cotizaciones.update');
        Route::delete('destroy/{id}', [QuoteController::class, 'destroy'])->name('tenant.taller.cotizaciones.destroy');
        Route::get('pdf/{id}', [QuoteController::class, 'pdfOne'])->name('tenant.taller.cotizaciones.pdfOne');
        Route::get('convert-order/{id}', [QuoteController::class, 'convertOrderCreate'])->name('tenant.taller.cotizaciones.convertOrderCreate');
    });

    Route::group(["prefix" => "servicios"], function () {

        Route::get('index', [ServiceController::class, 'index'])->name('tenant.taller.servicios.index');
        Route::get('getServices', [ServiceController::class, 'getServices'])->name('tenant.taller.servicios.getServices');
        Route::post('store', [ServiceController::class, 'store'])->name('tenant.taller.servicios.store');
        Route::get('getService/{id}', [ServiceController::class, 'getService'])->name('tenant.taller.servicios.getService');
        Route::put('update/{id}', [ServiceController::class, 'update'])->name('tenant.taller.servicios.update');
        Route::delete('destroy/{id}', [ServiceController::class, 'destroy'])->name('tenant.taller.servicios.destroy');
    });

    Route::group(["prefix" => "vehiculos"], function () {

        Route::get('index', [VehicleController::class, 'index'])->name('tenant.taller.vehiculos.index');
        Route::get('getVehiculos', [VehicleController::class, 'getVehiculos'])->name('tenant.taller.vehiculos.getVehiculos');
        Route::get('create', [VehicleController::class, 'create'])->name('tenant.taller.vehiculos.create');
        Route::post('store', [VehicleController::class, 'store'])->name('tenant.taller.vehiculos.store');
        Route::get('edit/{id}', [VehicleController::class, 'edit'])->name('tenant.taller.vehiculos.edit');
        Route::put('update/{id}', [VehicleController::class, 'update'])->name('tenant.taller.vehiculos.update');
        Route::delete('destroy/{id}', [VehicleController::class, 'destroy'])->name('tenant.taller.vehiculos.destroy');
    });

    Route::group(["prefix" => "colores"], function () {

        Route::get('index', [ColorController::class, 'index'])->name('tenant.taller.colores.index');
        Route::get('getColores', [ColorController::class, 'getColores'])->name('tenant.taller.colores.getColores');
        Route::post('store', [ColorController::class, 'store'])->name('tenant.taller.colores.store');
        Route::get('getColor/{id}', [ColorController::class, 'getColor'])->name('tenant.taller.colores.getColor');
        Route::delete('delete/{id}', [ColorController::class, 'destroy'])->name('tenant.taller.colores.destroy');
        Route::put('update/{id}', [ColorController::class, 'update'])->name('tenant.taller.colores.update');
    });

    Route::group(["prefix" => "marcas"], function () {

        Route::get('index', [BrandController::class, 'index'])->name('tenant.taller.marcas.index');
        Route::get('getMarcas', [BrandController::class, 'getMarcas'])->name('tenant.taller.marcas.getMarcas');
        Route::post('store', [BrandController::class, 'store'])->name('tenant.taller.marcas.store');
        Route::get('getMarca/{id}', [BrandController::class, 'getMarca'])->name('tenant.taller.marcas.getMarca');
        Route::delete('delete/{id}', [BrandController::class, 'destroy'])->name('tenant.taller.marcas.destroy');
        Route::put('update/{id}', [BrandController::class, 'update'])->name('tenant.taller.marcas.update');
    });

    Route::group(["prefix" => "modelos"], function () {

        Route::get('index', [ModelController::class, 'index'])->name('tenant.taller.modelos.index');
        Route::get('getModelos', [ModelController::class, 'getModelos'])->name('tenant.taller.modelos.getModelos');
        Route::post('store', [ModelController::class, 'store'])->name('tenant.taller.modelos.store');
        Route::get('getModelo/{id}', [ModelController::class, 'getModelo'])->name('tenant.taller.modelos.getModelo');
        Route::delete('delete/{id}', [ModelController::class, 'destroy'])->name('tenant.taller.modelos.destroy');
        Route::put('update/{id}', [ModelController::class, 'update'])->name('tenant.taller.modelos.update');
    });

    Route::group(["prefix" => "years"], function () {

        Route::get('index', [YearController::class, 'index'])->name('tenant.taller.years.index');
        Route::get('getYears', [YearController::class, 'getYears'])->name('tenant.taller.years.getYears');
        Route::post('store', [YearController::class, 'store'])->name('tenant.taller.years.store');
        Route::get('getYear/{id}', [YearController::class, 'getYear'])->name('tenant.taller.years.getYear');
        Route::delete('delete/{id}', [YearController::class, 'destroy'])->name('tenant.taller.years.destroy');
        Route::put('update/{id}', [YearController::class, 'update'])->name('tenant.taller.years.update');
    });
});
