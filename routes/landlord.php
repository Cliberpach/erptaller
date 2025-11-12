<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandLord\ApiController;
use App\Http\Controllers\LandLord\CompanyController;
use App\Http\Controllers\LandLord\ModuleController;
use App\Http\Controllers\LandLord\PlanController;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::middleware(['auth:web', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return redirect()->route('landlord.mantenimientos.empresa');
    });
    //Route::get('/dashboard', [ModuleController::class, 'home'])->name('landlord.home');

    Route::group(["prefix" => "mantenimiento"], function () {
        
        //======== EMPRESA =======
        Route::get('empresa', [CompanyController::class, 'index'])->name('landlord.mantenimientos.empresa');
        Route::get('empresa/edit/{id}', [CompanyController::class, 'edit'])->name('landlord.mantenimientos.empresas.edit');
        Route::get('empresa/getCompanies', [CompanyController::class, 'getCompanies'])->name('landlord.mantenimientos.getCompanies');
        Route::get('empresa/registrar', [CompanyController::class, 'create'])->name('landlord.mantenimientos.empresas.create');
        Route::post('empresa', [CompanyController::class, 'store'])->name('landlord.mantenimientos.empresas.store');
        Route::post('empresa/resetearClave', [CompanyController::class, 'resetearClave'])->name('landlord.mantenimientos.empresas.resetearClave');
        Route::put('empresa/update/{id}', [CompanyController::class, 'update'])->name('landlord.mantenimientos.empresas.update');
        Route::delete('empresa/deleteTenant/{id}', [CompanyController::class, 'deleteTenant'])->name('landlord.mantenimientos.empresas.deleteTenant');

        Route::get('plan', [PlanController::class, 'index'])->name('landlord.mantenimientos.plan');
        Route::post('plan', [PlanController::class, 'store'])->name('landlord.mantenimientos.planes.store');
        Route::get('plan/edit/{id}', [PlanController::class, 'edit'])->name('landlord.mantenimientos.planes.edit');
        Route::put('plan/update/{id}', [PlanController::class, 'update'])->name('landlord.mantenimientos.planes.update');
        Route::get('plan/delete/{id}', [PlanController::class, 'delete'])->name('landlord.mantenimientos.planes.delete');
        Route::delete('plan/destroy/{id}', [PlanController::class, 'destroy'])->name('landlord.mantenimientos.planes.destroy');
    });
});

Route::get("landlord/ruc/{ruc}", [ApiController::class, 'apiRuc']);
Route::get("landlord/dni/{dni}", [ApiController::class, 'apiDni']);

