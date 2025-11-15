<?php

use App\Http\Controllers\Tenant\WorkShop\BrandController;
use App\Http\Controllers\Tenant\WorkShop\ColorController;
use App\Http\Controllers\Tenant\WorkShop\ModelController;
use App\Http\Controllers\Tenant\WorkShop\VehicleController;
use App\Http\Controllers\Tenant\WorkShop\YearController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "taller"], function () {

    Route::group(["prefix" => "vehiculos"], function () {

        Route::get('index', [VehicleController::class, 'index'])->name('tenant.taller.vehiculos.index');
        Route::get('getVehiculos', [VehicleController::class, 'getVehiculos'])->name('tenant.taller.vehiculos.getVehiculos');
        Route::get('create', [VehicleController::class, 'create'])->name('tenant.taller.vehiculos.create');

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
