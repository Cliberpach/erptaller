<?php

use App\Http\Controllers\LandLord\ApiController;
use App\Http\Controllers\Notifications\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\BookController;
use App\Http\Controllers\Tenant\Cash\PettyCashController;
use App\Http\Controllers\Tenant\FieldController;
use App\Http\Controllers\Tenant\Consultas\ConsultasCreditosController;
use App\Http\Controllers\Tenant\Consultas\QueryReservationController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\Maintenance\BankAccountController;
use App\Http\Controllers\Tenant\ModuleController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\Purchase\PurchaseDocumentoController;
use App\Http\Controllers\Tenant\PurchaseController;
use App\Http\Controllers\Tenant\Reports\ReportContableController;
use App\Http\Controllers\Tenant\Reports\ReportFieldController;
use App\Http\Controllers\Tenant\Reports\ReportSaleController;
use App\Http\Controllers\Tenant\Reports\ReservationDocumentController;
use App\Http\Controllers\Tenant\SupplierController;
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

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'sede.activa'])->group(function () {
    Route::get('/dashboard', [ModuleController::class, 'home'])->name('tenant.home');

    // Multi-sede: cambiar la sede activa (Etapa 2)
    Route::get('sede/cambiar/{id}', [\App\Http\Controllers\Tenant\Maintenance\SedeController::class, 'cambiar'])->name('tenant.sede.cambiar');



    // Route::group(["prefix" => "cajas"], function () {
    //     Route::get('caja', [PettyCashController::class, 'pettyCash'])->name('tenant.cajas.caja');
    //     Route::get('apertura-cierre', [PettyCashController::class, 'initialFinalBalancing'])->name('tenant.cajas.apertura_cierre');
    //     Route::get('egreso', [PettyCashController::class, 'exitMoney'])->name('tenant.cajas.egreso');
    // });

    // P3: módulo Reservas/Campos a eliminar -> can:reservas.ver (admin-only de facto, nadie más lo tiene).
    Route::group(["prefix" => "reservas", 'middleware' => 'can:reservas.ver'], function () {
        Route::get('reserva', [BookController::class, 'book'])->middleware('verificar.caja')->name('tenant.reservas.reserva');
        Route::get('/reserva/{id}/recibo', [BookController::class, 'showPDF'])->middleware('verificar.caja')->name('tenant.reservas.recibo');
        Route::get('/reservas/pdf', [BookController::class, 'generatePDF'])->name('tenant.reservas.pdf');
        Route::get('/available-fields', [BookController::class, 'getAvailableFields'])->name('tenat.reservas.camposdisponibles');
    });

    // P3: consultas (créditos + reservas-query) -> can:consultas.ver (los 3 roles lo tienen).
    Route::group(["prefix" => "consultas", 'middleware' => 'can:consultas.ver'], function () {

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



    // P3: Campos (parte de Reservas, a eliminar) -> can:reservas.ver (admin-only de facto).
    Route::group(["prefix" => "campos", 'middleware' => 'can:reservas.ver'], function () {
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

    Route::group(["prefix" => "compras", 'middleware' => ['validar.plan:compras', 'can:compras.ver']], function () {

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
        Route::get('reporte-venta', [ReportSaleController::class, 'index'])->name('tenant.reportes.reporte_venta')->middleware('can:reportes.ventas');
        Route::get('reporte-venta/getReporteVenta', [ReportSaleController::class, 'getReporteVenta'])->name('tenant.reportes.reporte_venta.getReporteVenta')->middleware('can:reportes.ventas');
        Route::get('reporte-venta/excel', [ReportSaleController::class, 'excel'])->name('tenant.reportes.reporte_venta.excel')->middleware('can:reportes.ventas');
        Route::get('reporte-venta/pdf', [ReportSaleController::class, 'pdf'])->name('tenant.reportes.reporte_venta.pdf')->middleware('can:reportes.ventas');

        //======== REPORTE DE CAMPOS =======
        Route::get('reporte-campo', [ReportFieldController::class, 'index'])->name('tenant.reportes.reporte_campo')->middleware('can:reservas.ver');
        Route::get('reporte-campo/getReporteCampos', [ReportFieldController::class, 'getReporteCampos'])->name('tenant.reportes.reporte_campo.getReporteCampos')->middleware('can:reservas.ver');
        Route::get('reporte-campo/excel', [ReportFieldController::class, 'excel'])->name('tenant.reportes.reporte_campo.excel')->middleware('can:reservas.ver');
        Route::get('reporte-campo/pdf', [ReportFieldController::class, 'pdf'])->name('tenant.reportes.reporte_campo.pdf')->middleware('can:reservas.ver');
        Route::get('reporte-campo/generarDocumento/{id}', [ReportFieldController::class, 'generateDocumentCreate'])->name('tenant.reportes.reporte_campo.generarDocumento')->middleware('can:reservas.ver');
        Route::post('reporte-campo/generarDocumento/store', [ReportFieldController::class, 'generateDocumentStore'])->name('tenant.reportes.reporte_campo.generateDocumentStore')->middleware('can:reservas.ver');
        Route::get('reporte-campo/pdf_voucher/{id}', [ReportFieldController::class, 'pdf_voucher'])->name('tenant.reportes.reporte_campo.pdf_voucher')->middleware('can:reservas.ver');

        //======== REPORTE CONTABLE =======
        Route::get('reporte-contable', [ReportContableController::class, 'index'])->name('tenant.reportes.reporte_contable')->middleware('can:reportes.contable');
        Route::get('reporte-contable/getReporteContable', [ReportContableController::class, 'getReporteContable'])->name('tenant.reportes.reporte_contable.getReporteContable')->middleware('can:reportes.contable');
        Route::get('reporte-contable/excel', [ReportContableController::class, 'excel'])->name('tenant.reportes.reporte_contable.excel')->middleware('can:reportes.contable');
        Route::get('reporte-contable/pdf', [ReportContableController::class, 'pdf'])->name('tenant.reportes.reporte_contable.pdf')->middleware('can:reportes.contable');

        //========== REPORTE COMPROBANTE RESERVAS ========
        Route::get('comprobantes-reservas', [ReservationDocumentController::class, 'index'])->name('tenant.reportes.comprobantes_reservas')->middleware('can:reservas.ver');
        Route::get('comprobantes-reservas/getReservationDocuments', [ReservationDocumentController::class, 'getReservationDocuments'])->name('tenant.reportes.comprobantes_reservas.getReservationDocuments')->middleware('can:reservas.ver');
        Route::post('comprobantes-reservas/send_sunat', [ReservationDocumentController::class, 'send_sunat'])->name('tenant.reportes.comprobantes_reservas.send_sunat')->middleware('can:reservas.ver');
        Route::get('comprobantes-reservas/pdf_voucher/{id}', [ReservationDocumentController::class, 'pdf_voucher'])->name('tenant.reportes.comprobantes_reservas.pdf_voucher')->middleware('can:reservas.ver');
        Route::get('downloadXml/{id}', [ReservationDocumentController::class, 'downloadXml'])->name('tenant.reportes.comprobantes_reservas.downloadXml')->middleware('can:reservas.ver');
        Route::get('downloadCdr/{id}', [ReservationDocumentController::class, 'downloadCdr'])->name('tenant.reportes.comprobantes_reservas.downloadCdr')->middleware('can:reservas.ver');
    });


    Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('notifications.index');
    Route::get('/notifications/count', [NotificationController::class, 'getNotificationsCount'])->name('notifications.count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::post('/notifications/notified', [NotificationController::class, 'notified'])->name('notifications.notified');
    Route::put('/notifications/finish/{id}', [NotificationController::class, 'finish'])->name('notifications.finish');

    require __DIR__ . '/tenant/taller/web.php';
    require __DIR__ . '/tenant/mantenimiento/web.php';
    require __DIR__ . '/tenant/cash/web.php';
    require __DIR__ . '/tenant/sales/web.php';
    require __DIR__ . '/tenant/accounts/web.php';
    require __DIR__ . '/tenant/queries/web.php';
    require __DIR__ . '/tenant/inventory/web.php';
    require __DIR__ . '/tenant/dashboard/web.php';


    Route::get("landlord/ruc/{ruc}", [ApiController::class, 'apiRuc']);
    Route::get("landlord/dni/{dni}", [ApiController::class, 'apiDni']);

    Route::get("/logout", [ModuleController::class, 'logout'])->name('module.logout');
});

// P3: utils/* = lookups AJAX (product/customer/vehicle/etc.). Solo-auth (sin permiso),
// pero ya NO quedan fuera de autenticación: requieren sesión válida.
Route::group(["prefix" => "utils", 'middleware' => ['auth:sanctum', config('jetstream.auth_session'), 'verified']], function () {
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
    Route::get('getListBankAccounts', [BankAccountController::class, 'getListBankAccounts'])->name('tenant.utils.getListBankAccounts');
    Route::get('is-active-invoice/{id}', [UtilController::class, 'isActiveInvoiceType'])->name('tenant.utils.isActiveInvoiceType');
    Route::get('search-supplier', [SupplierController::class, 'searchSupplier'])->name('tenant.utils.searchSupplier');
});
