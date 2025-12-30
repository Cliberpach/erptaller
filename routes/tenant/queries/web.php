<?php

use App\Http\Controllers\Tenant\Queries\QVehicleController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "consultas"], function () {
    Route::group(["prefix" => "vehiculos"], function () {

        Route::get('index', [QVehicleController::class, 'index'])->name('tenant.consultas.vehiculos.index');
        Route::get('getList', [QVehicleController::class, 'getList'])->name('tenant.consultas.vehiculos.getList');
        Route::get('getExcel', [QVehicleController::class, 'getExcel'])->name('tenant.consultas.vehiculos.getExcel');
        Route::get('getPdf', [QVehicleController::class, 'getPdf'])->name('tenant.consultas.vehiculos.getPdf');
    });
});
