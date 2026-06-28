<?php

use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Tenant\Queries\QNotificationController;
use App\Http\Controllers\Tenant\Queries\QVehicleController;
use Illuminate\Support\Facades\Route;

// Permisos P3: Consultas (vehículos + alertas) → can:consultas.ver. AJAX (getList/getAll)
// dentro de los grupos → heredan. (Los créditos viven en web.php, se protegen ahí.)
Route::group(["prefix" => "consultas", 'middleware' => 'can:consultas.ver'], function () {
    Route::group(["prefix" => "vehiculos"], function () {

        Route::get('index', [QVehicleController::class, 'index'])->name('tenant.consultas.vehiculos.index');
        Route::get('getList', [QVehicleController::class, 'getList'])->name('tenant.consultas.vehiculos.getList');
        Route::get('getExcel', [QVehicleController::class, 'getExcel'])->name('tenant.consultas.vehiculos.getExcel');
        Route::get('getPdf', [QVehicleController::class, 'getPdf'])->name('tenant.consultas.vehiculos.getPdf');
    });

    Route::group(["prefix" => "alerts"], function () {
        Route::get('index', [QNotificationController::class, 'index'])->name('tenant.consultas.notificaciones.index');
        Route::get('getAll', [QNotificationController::class, 'getAll'])->name('tenant.consultas.notificaciones.getAll');
    });
});
