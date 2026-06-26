<?php

use App\Http\Controllers\Tenant\BrandController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\InventoryController;
use App\Http\Controllers\Tenant\KardexController;
use App\Http\Controllers\Tenant\NoteIncomeController;
use App\Http\Controllers\Tenant\NoteReleaseController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\ValuedKardexController;
use App\Http\Controllers\Tenant\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "inventarios", 'middleware' => 'validar.plan:inventario'], function () {

    Route::group(["prefix" => "almacenes"], function () {
        Route::get('index', [WarehouseController::class, 'index'])->name('tenant.inventarios.almacenes.index');
        Route::get('getWarehouses', [WarehouseController::class, 'getWarehouses'])->name('tenant.inventarios.almacenes.getWarehouses');
        Route::post('store', [WarehouseController::class, 'store'])->name('tenant.inventarios.almacenes.store');
        Route::get('edit/{id}', [WarehouseController::class, 'edit'])->name('tenant.inventarios.almacenes.edit');
        Route::put('update/{id}', [WarehouseController::class, 'update'])->name('tenant.inventarios.almacenes.update');
        Route::put('toggle-status/{id}', [WarehouseController::class, 'toggleStatus'])->name('tenant.inventarios.almacenes.toggleStatus');
    });

    Route::group(["prefix" => "categorias"], function () {
        Route::get('index', [CategoryController::class, 'index'])->name('tenant.inventarios.productos.categoria');
        Route::get('get-all', [CategoryController::class, 'getAll'])->name('tenant.inventarios.productos.categoria.get-all');
        Route::post('store', [CategoryController::class, 'store'])->name('tenant.inventarios.productos.categoria.store');
        Route::put('update/{id}', [CategoryController::class, 'update'])->name('tenant.inventarios.productos.categoria.update');
        Route::delete('destroy/{id}', [CategoryController::class, 'destroy'])->name('tenant.inventarios.productos.categoria.destroy');
        Route::get('get-format-excel', [CategoryController::class, 'getFormatExcel'])->name('tenant.inventarios.productos.categoria.get-format-excel');
        Route::post('import-categories-excel', [CategoryController::class, 'importCategoriesExcel'])->name('tenant.inventarios.productos.categoria.import-categories-excel');
    });

    Route::group(["prefix" => "marcas"], function () {
        Route::get('index', [BrandController::class, 'index'])->name('tenant.inventarios.productos.marca');
        Route::get('get-all', [BrandController::class, 'getAll'])->name('tenant.inventarios.productos.marca.get-all');
        Route::post('store', [BrandController::class, 'store'])->name('tenant.inventarios.productos.marca.store');
        Route::put('update/{id}', [BrandController::class, 'update'])->name('tenant.inventarios.productos.marca.update');
        Route::delete('destroy/{id}', [BrandController::class, 'destroy'])->name('tenant.inventarios.productos.marca.destroy');
        Route::get('get-format-excel', [BrandController::class, 'getFormatExcel'])->name('tenant.inventarios.productos.marca.get-format-excel');
        Route::post('import-marcas-excel', [BrandController::class, 'importExcel'])->name('tenant.inventarios.productos.marca.import-excel');
    });

    Route::group(["prefix" => "productos"], function () {
        Route::get('index', [ProductController::class, 'index'])->name('tenant.inventarios.productos.producto');
        Route::get('get-all', [ProductController::class, 'getAll'])->name('tenant.inventarios.productos.producto.get-all');
        Route::post('store', [ProductController::class, 'store'])->name('tenant.inventarios.productos.store');
        Route::put('update/{id}', [ProductController::class, 'update'])->name('tenant.inventarios.productos.update');
        Route::delete('destroy/{id}', [ProductController::class, 'destroy'])->name('tenant.inventarios.productos.destroy');
        Route::get('get-format-excel', [ProductController::class, 'getFormatExcel'])->name('tenant.inventarios.productos.producto.get-format-excel');
        Route::post('import-producto-excel', [ProductController::class, 'importExcel'])->name('tenant.inventarios.productos.producto.import-excel');
        Route::post('export-producto-excel', [ProductController::class, 'exportExcel'])->name('tenant.inventarios.productos.producto.export-excel');
    });

    Route::group(["prefix" => "inventario"], function () {
        Route::get('servicio', [InventoryController::class, 'service'])->name('tenant.inventarios.servicio');
        Route::get('movimiento', [InventoryController::class, 'movement'])->name('tenant.inventarios.movimiento');
        Route::get('devolucion-proveedor', [InventoryController::class, 'supplierReturn'])->name('tenant.inventarios.devolucion_proveedor');
        Route::get('inventario', [InventoryController::class, 'index'])->name('tenant.inventarios.inventario');
        Route::get('inventario/getInventory', [InventoryController::class, 'getInventory'])->name('tenant.inventarios.inventario.getInventory');
        Route::get('inventario/excel', [InventoryController::class, 'excel'])->name('tenant.inventarios.inventario.excel');
        Route::get('inventario/pdf', [InventoryController::class, 'pdf'])->name('tenant.inventarios.inventario.pdf');
    });

    Route::group(["prefix" => "kardex"], function () {
        //============ KARDEX ============
        Route::get('kardex', [KardexController::class, 'index'])->name('tenant.inventory.kardex.index');
        Route::get('getKardex', [KardexController::class, 'getKardex'])->name('tenant.inventory.kardex.getKardex');
        Route::get('kardex/excel', [KardexController::class, 'excel'])->name('tenant.inventory.kardex.excel');
        Route::get('kardex/pdf', [KardexController::class, 'pdf'])->name('tenant.inventory.kardex.pdf');
    });


    Route::group(["prefix" => "kardex_valor"], function () {
        Route::get('kardex-valor/index', [ValuedKardexController::class, 'index'])->name('tenant.inventarios.kardex_valorizado');
        Route::get('kardex-valor/getValuedKardex', [ValuedKardexController::class, 'getValuedKardex'])->name('tenant.inventarios.kardex_valorizado.getValuedKardex');
        Route::get('kardex-valor/pdf', [ValuedKardexController::class, 'pdf'])->name('tenant.inventarios.kardex_valorizado.pdf');
    });


    Route::group(["prefix" => "nota_ingreso"], function () {
        //============ NOTA INGRESO ========
        Route::get('index', [NoteIncomeController::class, 'index'])->name('tenant.inventarios.nota_ingreso');
        Route::get('getNoteIncome', [NoteIncomeController::class, 'getNoteIncome'])->name('tenant.inventarios.nota_ingreso.getNoteIncome');
        Route::get('create', [NoteIncomeController::class, 'create'])->name('tenant.inventarios.nota_ingreso.create');
        Route::post('store', [NoteIncomeController::class, 'store'])->name('tenant.inventarios.nota_ingreso.store');
        Route::get('getProducts', [NoteIncomeController::class, 'getProducts'])->name('tenant.inventarios.nota_ingreso.getProducts');
        Route::get('show/{id}', [NoteIncomeController::class, 'show'])->name('tenant.inventarios.nota_ingreso.show');
    });
    Route::group(["prefix" => "nota_salida"], function () {
        //============ NOTA SALIDA ========
        Route::get('index', [NoteReleaseController::class, 'index'])->name('tenant.inventarios.nota_salida');
        Route::get('create', [NoteReleaseController::class, 'create'])->name('tenant.inventarios.nota_salida.create');
        Route::get('getProducts', [NoteReleaseController::class, 'getProducts'])->name('tenant.inventarios.nota_salida.getProducts');
        Route::get('validateStock/{product_id}/{quantity}', [NoteReleaseController::class, 'validateStock'])->name('tenant.inventarios.nota_salida.validateStock');
        Route::post('store', [NoteReleaseController::class, 'store'])->name('tenant.inventarios.nota_salida.store');
        Route::get('getNotesRelease', [NoteReleaseController::class, 'getNotesRelease'])->name('tenant.inventarios.nota_salida.getNotesRelease');
        Route::get('show/{id}', [NoteReleaseController::class, 'show'])->name('tenant.inventarios.nota_salida.show');
    });
});
