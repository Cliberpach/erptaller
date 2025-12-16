<?php

use App\Http\Controllers\LandLord\ApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\BookController;
use App\Http\Controllers\Tenant\BrandController;
use App\Http\Controllers\Tenant\Cash\PettyCashController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\FieldController;
use App\Http\Controllers\Tenant\Consultas\ConsultasCreditosController;
use App\Http\Controllers\Tenant\Consultas\QueryReservationController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\InventoryController;
use App\Http\Controllers\Tenant\KardexController;
use App\Http\Controllers\Tenant\Maintenance\BankAccountController;
use App\Http\Controllers\Tenant\ModuleController;
use App\Http\Controllers\Tenant\NoteIncomeController;
use App\Http\Controllers\Tenant\NoteReleaseController;
use App\Http\Controllers\Tenant\PettyCashBookController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\Purchase\PurchaseDocumentoController;
use App\Http\Controllers\Tenant\PurchaseController;
use App\Http\Controllers\Tenant\Reports\ReportContableController;
use App\Http\Controllers\Tenant\Reports\ReportFieldController;
use App\Http\Controllers\Tenant\Reports\ReportSaleController;
use App\Http\Controllers\Tenant\Reports\ReservationDocumentController;
use App\Http\Controllers\Tenant\SupplierController;
use App\Http\Controllers\Tenant\ValuedKardexController;
use App\Http\Controllers\Tenant\WorkShop\ModelController;
use App\Http\Controllers\Tenant\WorkShop\ServiceController;
use App\Http\Controllers\Tenant\WorkShop\VehicleController;
use App\Http\Controllers\Tenant\WorkShop\YearController;
use App\Http\Controllers\UtilController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Route::middleware(['auth:sanctum', config('jetstream.auth_session'),'verified',])->group(function () {
//     Route::get('/dashboard', function () {
//         return view('dashboard');
//     })->name('dashboard');
// });

Route::get('user/tenant', [UserController::class, 'index'])->name('tenant.users.index');
Route::post('user/create', [UserController::class, 'store'])->name('tenant.users.create');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', [ModuleController::class, 'home'])->name('tenant.home');



    // Route::group(["prefix" => "cajas"], function () {
    //     Route::get('caja', [PettyCashController::class, 'pettyCash'])->name('tenant.cajas.caja');
    //     Route::get('apertura-cierre', [PettyCashController::class, 'initialFinalBalancing'])->name('tenant.cajas.apertura_cierre');
    //     Route::get('egreso', [PettyCashController::class, 'exitMoney'])->name('tenant.cajas.egreso');
    // });

    Route::group(["prefix" => "reservas"], function () {
        Route::get('reserva', [BookController::class, 'book'])->middleware('verificar.caja')->name('tenant.reservas.reserva');
        Route::get('/reserva/{id}/recibo', [BookController::class, 'showPDF'])->middleware('verificar.caja')->name('tenant.reservas.recibo');
        Route::get('/reservas/pdf', [BookController::class, 'generatePDF'])->name('tenant.reservas.pdf');
        Route::get('/available-fields', [BookController::class, 'getAvailableFields'])->name('tenat.reservas.camposdisponibles');
    });

    Route::group(["prefix" => "consultas"], function () {

        Route::get('index', [ConsultasCreditosController::class, 'index'])->name('tenant.consultas.creditos');
        Route::get('creditos/data', [ConsultasCreditosController::class, 'data'])->name('tenant.consultas.creditos.data');
        Route::get('creditos/pdf', [ConsultasCreditosController::class, 'generateCreditPDF'])->name('tenant.consultas.creditos.pdf');
        Route::get('/creditos/excel', [ConsultasCreditosController::class, 'exportExcel'])->name('tenant.consultas.creditos.excel');
        Route::post('/creditos/generar-documento', [ConsultasCreditosController::class, 'generarDocumento'])->name('tenant.consultas.creditos.generar_documento');

        Route::group(["prefix" => "reservas"], function () {
            Route::get('index', [QueryReservationController::class, 'index'])->name('tenant.consultas.reservas');
            Route::get('data', [QueryReservationController::class, 'data'])->name('tenant.consultas.reservas.data');
            // Route::get('creditos/pdf', [ConsultasCreditosController::class, 'generateCreditPDF'])->name('tenant.consultas.creditos.pdf');
            // Route::get('/creditos/excel', [ConsultasCreditosController::class, 'exportExcel'])->name('tenant.consultas.creditos.excel');
            Route::post('/creditos/generar-documento', [QueryReservationController::class, 'generarDocumento'])->name('tenant.consultas.reservas.generar-documento');
        });
    });

    Route::group(["prefix" => "inventarios", 'middleware' => 'validar.plan:inventario'], function () {

        Route::get('productos/categoria', [CategoryController::class, 'index'])->name('tenant.inventarios.productos.categoria');
        Route::get('productos/categoria/get-all', [CategoryController::class, 'getAll'])->name('tenant.inventarios.productos.categoria.get-all');
        Route::post('productos/registrar-categoria', [CategoryController::class, 'store'])->name('tenant.inventarios.productos.categoria.store');
        Route::put('productos/actualizar-categoria/{id}', [CategoryController::class, 'update'])->name('tenant.inventarios.productos.categoria.update');
        Route::delete('productos/eliminar-categoria/{id}', [CategoryController::class, 'destroy'])->name('tenant.inventarios.productos.categoria.destroy');
        Route::get('/get-format-excel', [CategoryController::class, 'getFormatExcel'])->name('tenant.inventarios.productos.categoria.get-format-excel');
        Route::post('/import-categories-excel', [CategoryController::class, 'importCategoriesExcel'])->name('tenant.inventarios.productos.categoria.import-categories-excel');

        Route::get('productos/marca', [BrandController::class, 'index'])->name('tenant.inventarios.productos.marca');
        Route::get('productos/marca/get-all', [BrandController::class, 'getAll'])->name('tenant.inventarios.productos.marca.get-all');
        Route::post('productos/registrar-marca', [BrandController::class, 'store'])->name('tenant.inventarios.productos.marca.store');
        Route::put('productos/actualizar-marca/{id}', [BrandController::class, 'update'])->name('tenant.inventarios.productos.marca.update');
        Route::delete('productos/eliminar-marca/{id}', [BrandController::class, 'destroy'])->name('tenant.inventarios.productos.marca.destroy');
        Route::get('/marca/get-format-excel', [BrandController::class, 'getFormatExcel'])->name('tenant.inventarios.productos.marca.get-format-excel');
        Route::post('/marca/import-marcas-excel', [BrandController::class, 'importExcel'])->name('tenant.inventarios.productos.marca.import-excel');

        Route::get('productos/producto', [ProductController::class, 'index'])->name('tenant.inventarios.productos.producto');
        Route::get('productos/producto/get-all', [ProductController::class, 'getAll'])->name('tenant.inventarios.productos.producto.get-all');
        Route::post('productos/registrar-producto', [ProductController::class, 'store'])->name('tenant.inventarios.productos.store');
        Route::put('productos/actualizar-producto/{id}', [ProductController::class, 'update'])->name('tenant.inventarios.productos.update');
        Route::delete('productos/eliminar-producto/{id}', [ProductController::class, 'destroy'])->name('tenant.inventarios.productos.destroy');
        Route::get('/producto/get-format-excel', [ProductController::class, 'getFormatExcel'])->name('tenant.inventarios.productos.producto.get-format-excel');
        Route::post('/producto/import-producto-excel', [ProductController::class, 'importExcel'])->name('tenant.inventarios.productos.producto.import-excel');
        Route::post('/producto/export-producto-excel', [ProductController::class, 'exportExcel'])->name('tenant.inventarios.productos.producto.export-excel');


        Route::get('servicio', [InventoryController::class, 'service'])->name('tenant.inventarios.servicio');
        Route::get('movimiento', [InventoryController::class, 'movement'])->name('tenant.inventarios.movimiento');
        Route::get('devolucion-proveedor', [InventoryController::class, 'supplierReturn'])->name('tenant.inventarios.devolucion_proveedor');

        //============ KARDEX ============
        Route::get('kardex', [KardexController::class, 'index'])->name('tenant.inventory.kardex.index');
        Route::get('getKardex', [KardexController::class, 'getKardex'])->name('tenant.inventory.kardex.getKardex');
        Route::get('kardex/excel', [KardexController::class, 'excel'])->name('tenant.inventory.kardex.excel');
        Route::get('kardex/pdf', [KardexController::class, 'pdf'])->name('tenant.inventory.kardex.pdf');

        Route::get('inventario', [InventoryController::class, 'index'])->name('tenant.inventarios.inventario');
        Route::get('inventario/getInventory', [InventoryController::class, 'getInventory'])->name('tenant.inventarios.inventario.getInventory');
        Route::get('inventario/excel', [InventoryController::class, 'excel'])->name('tenant.inventarios.inventario.excel');
        Route::get('inventario/pdf', [InventoryController::class, 'pdf'])->name('tenant.inventarios.inventario.pdf');

        Route::get('kardex-valor/index', [ValuedKardexController::class, 'index'])->name('tenant.inventarios.kardex_valorizado');
        Route::get('kardex-valor/getValuedKardex', [ValuedKardexController::class, 'getValuedKardex'])->name('tenant.inventarios.kardex_valorizado.getValuedKardex');
        Route::get('kardex-valor/pdf', [ValuedKardexController::class, 'pdf'])->name('tenant.inventarios.kardex_valorizado.pdf');

        //============ NOTA INGRESO ========
        Route::get('nota_ingreso', [NoteIncomeController::class, 'index'])->name('tenant.inventarios.nota_ingreso');
        Route::get('getNoteIncome', [NoteIncomeController::class, 'getNoteIncome'])->name('tenant.inventarios.nota_ingreso.getNoteIncome');
        Route::get('nota_ingreso/create', [NoteIncomeController::class, 'create'])->name('tenant.inventarios.nota_ingreso.create');
        Route::post('nota_ingreso/store', [NoteIncomeController::class, 'store'])->name('tenant.inventarios.nota_ingreso.store');
        Route::get('getProducts', [NoteIncomeController::class, 'getProducts'])->name('tenant.inventarios.nota_ingreso.getProducts');
        Route::get('nota_ingreso/show/{id}', [NoteIncomeController::class, 'show'])->name('tenant.inventarios.nota_ingreso.show');


        //============ NOTA SALIDA ========
        Route::get('nota_salida', [NoteReleaseController::class, 'index'])->name('tenant.inventarios.nota_salida');
        Route::get('nota_salida/create', [NoteReleaseController::class, 'create'])->name('tenant.inventarios.nota_salida.create');
        Route::get('nota_salida/getProducts', [NoteReleaseController::class, 'getProducts'])->name('tenant.inventarios.nota_salida.getProducts');
        Route::get('nota_salida/validateStock/{product_id}/{quantity}', [NoteReleaseController::class, 'validateStock'])->name('tenant.inventarios.nota_salida.validateStock');
        Route::post('nota_salida/store', [NoteReleaseController::class, 'store'])->name('tenant.inventarios.nota_salida.store');
        Route::get('getNotesRelease', [NoteReleaseController::class, 'getNotesRelease'])->name('tenant.inventarios.nota_salida.getNotesRelease');
        Route::get('nota_salida/show/{id}', [NoteReleaseController::class, 'show'])->name('tenant.inventarios.nota_salida.show');
    });

    Route::group(["prefix" => "campos"], function () {
        Route::post('tipo-campo', [FieldController::class, 'fieldType'])->name('tenant.campos.tipo_campo');
        Route::get('tipo-campos', [FieldController::class, 'indexFieldType'])->name('tenant.campos.index_tipo_campos');
        Route::put('tipo-campos/{id}', [FieldController::class, 'editFieldType'])->name('tenant.campos.edit_tipo_campos');
        Route::delete('tipo-campos/{id}', [FieldController::class, 'deleteFieldType'])->name('tenant.campos.delete_tipo_campos');
        Route::get('campo', [FieldController::class, 'field'])->name('tenant.campos.campo');
        Route::get('campo/registrar', [FieldController::class, 'create'])->name('tenant.campos.create');
        Route::post('campo/guardar', [FieldController::class, 'store'])->name('tenant.campos.store');
        Route::get('campo/{id}/editar', [FieldController::class, 'edit'])->name('tenant.campos.edit');
        Route::put('campo/{id}/actualizar', [FieldController::class, 'update'])->name('tenant.campos.update');
        Route::delete('campo/{id}/anular', [FieldController::class, 'destroy'])->name('tenant.campos.delete');
    });

    Route::group(["prefix" => "compras", 'middleware' => 'validar.plan:compras'], function () {

        //======= PROVEEDORES =========
        Route::get('proveedor', [SupplierController::class, 'index'])->name('tenant.compras.proveedor');
        Route::get('proveedor/create', [SupplierController::class, 'create'])->name('tenant.compras.proveedor.create');
        Route::delete('proveedor/destroy/{id}', [SupplierController::class, 'destroy'])->name('tenant.compras.proveedor.destroy');
        Route::get('proveedor/getSuppliers', [SupplierController::class, 'getSuppliers'])->name('tenant.compras.proveedor.getSuppliers');
        Route::get('proveedor/consultarDocumento', [SupplierController::class, 'consultarDocumento'])->name('tenant.compras.proveedor.consultarDocumento');
        Route::post('proveedor/store', [SupplierController::class, 'store'])->name('tenant.compras.proveedor.store');
        Route::get('proveedor/edit/{id}', [SupplierController::class, 'edit'])->name('tenant.compras.proveedor.edit');
        Route::put('/update/{id}', [SupplierController::class, 'update'])->name('tenant.compras.proveedor.update');
        Route::get('proveedor/getLstSuppliers', [SupplierController::class, 'getLstSuppliers'])->name('tenant.compras.proveedor.getLstSuppliers');

        //========== DOCUMENTO DE COMPRA ========
        Route::get('purchase_document/index', [PurchaseDocumentoController::class, 'index'])->name('tenant.compras.documento_compra.index');
        Route::get('purchase_document/getPurchaseDocuments', [PurchaseDocumentoController::class, 'getPurchaseDocuments'])->name('tenant.compras.documento_compra.getPurchaseDocuments');
        Route::get('purchase_document/create', [PurchaseDocumentoController::class, 'create'])->name('tenant.compras.documento_compra.create');
        Route::get('purchase_document/getProducts', [PurchaseDocumentoController::class, 'getProducts'])->name('tenant.compras.documento_compra.getProducts');
        Route::post('purchase_document/store', [PurchaseDocumentoController::class, 'store'])->name('tenant.compras.documento_compra.store');
        Route::get('purchase_document/show/{id}', [PurchaseDocumentoController::class, 'show'])->name('tenant.compras.documento_compra.show');

        Route::get('orden-compra', [PurchaseController::class, 'orderPurchse'])->name('tenant.compras.orden_compra');
        Route::get('documento-compra', [PurchaseController::class, 'purchaseDocument'])->name('tenant.compras.documento_compra');
        Route::get('gasto-diverso', [PurchaseController::class, 'miscellaneousExpenses'])->name('tenant.compras.gasto_diverso');
    });

    Route::group(["prefix" => "reportes"], function () {

        //======= REPORTE VENTAS =========
        Route::get('reporte-venta', [ReportSaleController::class, 'index'])->name('tenant.reportes.reporte_venta');
        Route::get('reporte-venta/getReporteVenta', [ReportSaleController::class, 'getReporteVenta'])->name('tenant.reportes.reporte_venta.getReporteVenta');
        Route::get('reporte-venta/excel', [ReportSaleController::class, 'excel'])->name('tenant.reportes.reporte_venta.excel');
        Route::get('reporte-venta/pdf', [ReportSaleController::class, 'pdf'])->name('tenant.reportes.reporte_venta.pdf');

        //======== REPORTE DE CAMPOS =======
        Route::get('reporte-campo', [ReportFieldController::class, 'index'])->name('tenant.reportes.reporte_campo');
        Route::get('reporte-campo/getReporteCampos', [ReportFieldController::class, 'getReporteCampos'])->name('tenant.reportes.reporte_campo.getReporteCampos');
        Route::get('reporte-campo/excel', [ReportFieldController::class, 'excel'])->name('tenant.reportes.reporte_campo.excel');
        Route::get('reporte-campo/pdf', [ReportFieldController::class, 'pdf'])->name('tenant.reportes.reporte_campo.pdf');
        Route::get('reporte-campo/generarDocumento/{id}', [ReportFieldController::class, 'generateDocumentCreate'])->name('tenant.reportes.reporte_campo.generarDocumento');
        Route::post('reporte-campo/generarDocumento/store', [ReportFieldController::class, 'generateDocumentStore'])->name('tenant.reportes.reporte_campo.generateDocumentStore');
        Route::get('reporte-campo/pdf_voucher/{id}', [ReportFieldController::class, 'pdf_voucher'])->name('tenant.reportes.reporte_campo.pdf_voucher');

        //======== REPORTE CONTABLE =======
        Route::get('reporte-contable', [ReportContableController::class, 'index'])->name('tenant.reportes.reporte_contable');
        Route::get('reporte-contable/getReporteContable', [ReportContableController::class, 'getReporteContable'])->name('tenant.reportes.reporte_contable.getReporteContable');
        Route::get('reporte-contable/excel', [ReportContableController::class, 'excel'])->name('tenant.reportes.reporte_contable.excel');
        Route::get('reporte-contable/pdf', [ReportContableController::class, 'pdf'])->name('tenant.reportes.reporte_contable.pdf');

        //========== REPORTE COMPROBANTE RESERVAS ========
        Route::get('comprobantes-reservas', [ReservationDocumentController::class, 'index'])->name('tenant.reportes.comprobantes_reservas');
        Route::get('comprobantes-reservas/getReservationDocuments', [ReservationDocumentController::class, 'getReservationDocuments'])->name('tenant.reportes.comprobantes_reservas.getReservationDocuments');
        Route::post('comprobantes-reservas/send_sunat', [ReservationDocumentController::class, 'send_sunat'])->name('tenant.reportes.comprobantes_reservas.send_sunat');
        Route::get('comprobantes-reservas/pdf_voucher/{id}', [ReservationDocumentController::class, 'pdf_voucher'])->name('tenant.reportes.comprobantes_reservas.pdf_voucher');
        Route::get('downloadXml/{id}', [ReservationDocumentController::class, 'downloadXml'])->name('tenant.reportes.comprobantes_reservas.downloadXml');
        Route::get('downloadCdr/{id}', [ReservationDocumentController::class, 'downloadCdr'])->name('tenant.reportes.comprobantes_reservas.downloadCdr');
    });


    require __DIR__ . '/tenant/taller/web.php';
    require __DIR__ . '/tenant/mantenimiento/web.php';
    require __DIR__ . '/tenant/cash/web.php';
    require __DIR__ . '/tenant/sales/web.php';
    require __DIR__ . '/tenant/accounts/web.php';


    Route::get("landlord/ruc/{ruc}", [ApiController::class, 'apiRuc']);
    Route::get("landlord/dni/{dni}", [ApiController::class, 'apiDni']);

    Route::get("/logout", [ModuleController::class, 'logout'])->name('module.logout');

    Route::post('/closeCashBook', [PettyCashBookController::class, 'closeCashBook']);
});

Route::group(["prefix" => "utils"], function () {
    Route::get('cash-available-search', [PettyCashController::class, 'searchCashAvailable'])->name('tenant.utils.searchCashAvailable');
    Route::get('service-search', [ServiceController::class, 'searchService'])->name('tenant.utils.searchService');
    Route::get('product-search', [ProductController::class, 'searchProduct'])->name('tenant.utils.searchProduct');
    Route::get('product-search/stock', [ProductController::class, 'searchProductStock'])->name('tenant.utils.searchProductStock');
    Route::get('model-search', [ModelController::class, 'searchModel'])->name('tenant.utils.searchModel');
    Route::get('customer-search', [CustomerController::class, 'searchCustomer'])->name('tenant.utils.searchCustomer');
    Route::get('vehicle-search', [VehicleController::class, 'searchVehicle'])->name('tenant.utils.searchVehicle');
    Route::get('get-years/{model}', [YearController::class, 'getYearsModel'])->name('tenant.utils.getYearsModel');
    Route::get('serch-plate/{placa}', [VehicleController::class, 'searchPlate'])->name('tenant.utils.searchPlate');
    Route::get('validated-product/stock', [ProductController::class, 'validatedProductStock'])->name('tenant.utils.validatedProductStock');
    Route::get('getListBankAccounts', [BankAccountController::class,'getListBankAccounts'])->name('tenant.utils.getListBankAccounts');
    Route::get('is-active-invoice/{id}', [UtilController::class, 'isActiveInvoiceType'])->name('tenant.utils.isActiveInvoiceType');
});
