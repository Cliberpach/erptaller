<?php

use App\Http\Controllers\Tenant\BookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\CompanyController;
use App\Http\Controllers\Tenant\Maintenance\BankAccountController;
use App\Http\Controllers\Tenant\Maintenance\CollaboratorController;
use App\Http\Controllers\Tenant\Maintenance\ConfigurationController;
use App\Http\Controllers\Tenant\Maintenance\PositionController;
use App\Http\Controllers\Tenant\Maintenance\SedeController;
use App\Http\Controllers\Tenant\Maintenance\UserController;
use App\Http\Controllers\Tenant\PlanController;
use App\Http\Controllers\Tenant\RoleController;

Route::group(["prefix" => "mantenimiento"], function () {

    Route::group(["prefix" => "sedes"], function () {
        Route::get('index', [SedeController::class, 'index'])->name('tenant.mantenimientos.sedes.index');
        Route::get('getSedes', [SedeController::class, 'getSedes'])->name('tenant.mantenimientos.sedes.getSedes');
        Route::post('store', [SedeController::class, 'store'])->name('tenant.mantenimientos.sedes.store');
        Route::put('update/{id}', [SedeController::class, 'update'])->name('tenant.mantenimientos.sedes.update');
        Route::put('toggle-status/{id}', [SedeController::class, 'toggleStatus'])->name('tenant.mantenimientos.sedes.toggleStatus');
    });

    Route::group(["prefix" => "cuentas"], function () {
        Route::get('index', [BankAccountController::class, 'index'])->name('tenant.mantenimientos.cuentas.index');
        Route::get('getCuentas', [BankAccountController::class, 'getBankAccounts'])->name('tenant.mantenimiento.cuentas.getBankAccounts');
        Route::post('store', [BankAccountController::class, 'store'])->name('tenant.mantenimiento.cuentas.store');
        Route::put('update/{id}', [BankAccountController::class, 'update'])->name('tenant.mantenimiento.cuentas.update');
        Route::delete('/destroy/{id}', [BankAccountController::class, 'destroy'])->name('tenant.mantenimiento.cuentas.destroy');
    });

    Route::group(["prefix" => "empresa"], function () {
        Route::get('empresa', [CompanyController::class, 'index'])->name('tenant.mantenimientos.empresa');
        Route::get('empresa/registrar', [CompanyController::class, 'create'])->name('tenant.mantenimientos.empresas.create');
        Route::get('empresa/editar/{id}', [CompanyController::class, 'edit'])->name('tenant.mantenimientos.empresa.edit');
        Route::put('empresa/update/{id}', [CompanyController::class, 'update'])->name('tenant.mantenimientos.empresa.update');
        Route::post('empresa', [CompanyController::class, 'store'])->name('tenant.mantenimientos.empresas.store');
        Route::put('updateInvoice/{id}', [CompanyController::class, 'updateInvoice'])->name('tenant.mantenimientos.empresas.updateInvoice');
        Route::post('storeNumeration', [CompanyController::class, 'storeNumeration'])->name('tenant.mantenimientos.empresas.storeNumeration');
        Route::get('getListNumeration', [CompanyController::class, 'getListNumeration'])->name('tenant.mantenimientos.empresas.getListNumeration');
    });

    Route::group(["prefix" => "cargos"], function () {
        Route::get('index', [PositionController::class, 'index'])->name('tenant.mantenimientos.cargos.index');
        Route::get('getPositions', [PositionController::class, 'getPositions'])->name('tenant.mantenimientos.cargos.getPositions');
        Route::post('store', [PositionController::class, 'store'])->name('tenant.mantenimientos.cargos.store');
        Route::put('update/{id}', [PositionController::class, 'update'])->name('tenant.mantenimientos.cargos.update');
        Route::delete('destroy/{id}', [PositionController::class, 'destroy'])->name('tenant.mantenimientos.cargos.destroy');
    });

    Route::group(["prefix" => "colaborador"], function () {
        Route::get('index', [CollaboratorController::class, 'index'])->name('tenant.mantenimientos.colaboradores.index');
        Route::get('getColaboradores', [CollaboratorController::class, 'getCollaborators'])->name('tenant.mantenimientos.colaboradores.getColaboradores');
        Route::get('edit/{id}', [CollaboratorController::class, 'edit'])->name('tenant.mantenimientos.colaboradores.edit');
        Route::put('update/{id}', [CollaboratorController::class, 'update'])->name('tenant.mantenimientos.colaboradores.update');
        Route::delete('destroy/{id}', [CollaboratorController::class, 'destroy'])->name('tenant.mantenimientos.colaboradores.destroy');
        Route::get('create', [CollaboratorController::class, 'create'])->name('tenant.mantenimientos.colaboradores.create');
        Route::post('store', [CollaboratorController::class, 'store'])->name('tenant.mantenimientos.colaboradores.store');
        Route::get('search-document', [CollaboratorController::class, 'searchDocument'])->name('tenant.mantenimientos.colaboradores.searchDocument');
    });

    Route::group(["prefix" => "plan"], function () {
        Route::get('plan', [PlanController::class, 'index'])->name('tenant.mantenimientos.plan');
    });

    Route::group(["prefix" => "usuario"], function () {
        Route::get('index', [UserController::class, 'index'])->name('tenant.mantenimientos.usuario.index');
        Route::get('create', [UserController::class, 'create'])->name('tenant.mantenimientos.usuario.create');
        Route::get('getAll', [UserController::class, 'getAll'])->name('tenant.mantenimientos.usuario.getAll');
        Route::get('edit/{id}', [UserController::class, 'edit'])->name('tenant.mantenimientos.usuario.edit');
        Route::delete('destroy/{id}', [UserController::class, 'destroy'])->name('tenant.mantenimientos.usuario.destroy');
        Route::post('store', [UserController::class, 'store'])->name('tenant.mantenimientos.usuario.store');
        Route::put('update/{id}', [UserController::class, 'update'])->name('tenant.mantenimientos.usuario.update');
    });

    Route::group(["prefix" => "configuracion"], function () {
        //========== CONFIGURACION =========
        Route::get('configuracion', [ConfigurationController::class, 'index'])->name('tenant.mantenimientos.configuracion');
        Route::post('configuracion/store', [ConfigurationController::class, 'store'])->name('tenant.mantenimientos.configuracion.store');
    });

    Route::group(["prefix" => "rol"], function () {
        Route::get('rol', [RoleController::class, 'index'])->name('tenant.mantenimientos.rol');
    });

    Route::get('horario-de-atencion', [BookController::class, 'schedule'])->name('tenant.mantenimientos.horario');
    Route::post('horario-de-atencion/guardar', [BookController::class, 'saveSchedule'])->name('tenant.mantenimientos.horario.store');
});
