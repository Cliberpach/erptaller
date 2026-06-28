<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FormatController;
use App\Http\Controllers\UtilController;
use App\Http\Requests\Sale\SaleStoreRequest;
use App\Http\Services\Tenant\Sale\Sale\SaleManager;
use App\Models\Company;
use App\Models\CompanyInvoice;
use App\Models\Landlord\Color;
use App\Models\Landlord\Customer;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Sale\SaleService;
use App\Models\Tenant\SaleDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\File;
use Throwable;

class SaleController extends Controller
{
    use \App\Http\Concerns\HasSedeActiva;

    protected SaleManager $s_sale;

    public function __construct()
    {
        $this->s_sale   =   new SaleManager();
    }

    public function index()
    {
        $esAdmin = auth()->user()->hasRole('admin');

        return view('sales.sale_document.index', [
            // Fechas por defecto = mes en curso (sobre registration_date).
            'fecha_inicio' => now()->startOfMonth()->toDateString(),
            'fecha_fin'    => now()->toDateString(),
            'esAdmin'      => $esAdmin,
            // Filtro Vendedor: solo admin. Lista = usuarios con rol ventas.
            'vendedores'   => $esAdmin
                ? \App\Models\User::role('ventas')->orderBy('name')->get(['id', 'name'])
                : collect(),
        ]);
    }

    public function getSales(Request $request)
    {
        $customer_id    =   $request->get('customer_id');
        $start_date     =   $request->get('start_date');
        $end_date       =   $request->get('end_date');
        $status         =   $request->get('status');
        $seller_id      =   $request->get('seller_id');

        $esAdmin = auth()->user()->hasRole('admin');

        $sales    =   DB::table('sales_documents as sd')
            // Método de pago: la venta guarda hasta 2 (method_pay_id_1/_2). Se muestran ambos.
            ->leftJoin('payment_methods as pm1', 'pm1.id', '=', 'sd.method_pay_id_1')
            ->leftJoin('payment_methods as pm2', 'pm2.id', '=', 'sd.method_pay_id_2')
            ->select(
                'sd.id',
                'sd.registration_date as fecha_registro',
                'sd.customer_name',
                'sd.plate',
                'sd.user_recorder_name as vendedor',
                'sd.serie',
                'sd.correlative',
                DB::raw("CONCAT(sd.serie, '-', sd.correlative) AS doc"),
                'sd.type_sale_name',
                DB::raw("FORMAT(sd.total, 2) AS total"),
                // CONCAT_WS ignora NULL: 'EFECTIVO' o 'EFECTIVO, YAPE' si hay 2.
                DB::raw("CONCAT_WS(', ', pm1.description, pm2.description) AS metodo_pago"),
                'sd.sunat_status',
                'sd.type_sale_code',
                'sd.ruta_xml',
                'sd.ruta_cdr',
                'sd.payment_status'
            )
            ->where('sd.sunat_status', '!=', 'ANULADO');

        // VISIBILIDAD POR ROL (backend, blindado):
        // - admin: ve TODAS las ventas (de todas las sedes, todos los vendedores).
        // - no-admin: SOLO las que él registró. where fijo a auth()->id(); ignora cualquier
        //   seller_id manipulado en el request.
        if ($esAdmin) {
            // Filtro Vendedor (solo admin): si eligió uno, acota; si no, todas.
            if ($seller_id) {
                $sales->where('sd.user_recorder_id', $seller_id);
            }
        } else {
            $sales->where('sd.user_recorder_id', auth()->id());
        }

        if ($customer_id) {
            $sales->where('sd.customer_id', $customer_id);
        }
        if ($start_date) {
            $sales->whereDate('sd.registration_date', '>=', $start_date);
        }
        if ($end_date) {
            $sales->whereDate('sd.registration_date', '<=', $end_date);
        }
        if ($status) {
            $sales->where('sd.sunat_status', $status);
        }

        return DataTables::of($sales)->make(true);
    }

    public function create()
    {

        $urlImagen = asset('assets/img/products/img_default.png');

        $categories =   DB::select('select * from categories');
        $brands     =   DB::select('select * from brands');
        $customers  =   Customer::where('status', 'ACTIVO')->get();
        $company    =   Company::find(1);

        $types_identity_documents   =   UtilController::getIdentityDocuments();

        $departments    =   DB::select('select * from departments');
        $districts      =   DB::select('select * from districts');
        $provinces      =   DB::select('select * from provinces');

        $company_invoice    =   CompanyInvoice::find(1);
        $invoice_types      =   UtilController::getInvoiceTypes();
        $payment_methods    =   PaymentMethod::where('estado', 'ACTIVO')->get();
        $customer_formatted =   FormatController::getFormatInitialCustomer(1);
        $payment_conditions =   UtilController::getPaymentConditions();

        $years                      =   UtilController::getYears();
        $colors                     =   Color::where('status', 'ACTIVE')->get();

        return view(
            'sales.sale_document.create',
            compact(
                'customers',
                'categories',
                'brands',
                'urlImagen',
                'company',
                'types_identity_documents',
                'departments',
                'districts',
                'provinces',
                'payment_methods',
                'company_invoice',
                'invoice_types',
                'customer_formatted',
                'payment_conditions',
                'years',
                'colors'
            )
        );
    }

    public function getProductos(Request $request)
    {

        $category_id   =   $request->get('category_id');
        $brand_id      =   $request->get('brand_id');

        // Stock del almacén principal de la sede activa (multi-sede 3b). Solo LECTURA: el picker
        // de la venta muestra el stock de ESTE almacén; el descuento real se hace en Capa 2.
        $warehouseId = $this->almacenPrincipalSedeActivaId();

        $products = DB::table('products as p')
            ->leftJoin('warehouse_products as wp', function ($join) use ($warehouseId) {
                $join->on('wp.product_id', '=', 'p.id')
                    ->where('wp.warehouse_id', '=', $warehouseId);
            })
            ->join('brands as b', 'b.id', '=', 'p.brand_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->select(
                'p.id',
                'p.brand_id',
                'p.category_id',
                'p.name',
                'p.stock_min',
                'p.code_factory',
                'p.code_bar',
                'c.name as category_name',
                'b.name as brand_name',
                'wp.stock',
                'p.sale_price',
                'p.purchase_price'
            )->where('wp.stock', '>', '0');


        if ($category_id) {
            $products  =   $products->where('p.category_id', $category_id);
        }

        if ($brand_id) {
            $products  =   $products->where('p.brand_id', $brand_id);
        }

        $products  =   $products->get();


        return DataTables::of($products)
            ->make(true);
    }

    public function validateStock(Request $request)
    {
        try {

            // Multi-sede 3b: valida contra el stock del almacén principal de la sede activa
            // (no deja vender más de lo que hay EN ESA SEDE).
            $product    =   DB::select(
                'select
                            wp.*
                            from warehouse_products as wp
                            where
                            wp.product_id = ?
                            and wp.warehouse_id = ?',
                [$request->get('product_id'), $this->almacenPrincipalSedeActivaId()]
            );

            if (count($product) === 0) {
                throw new Exception("EL PRODUCTO NO EXISTE EN LA BD!!");
            }

            if ($product[0]->stock < $request->get('cant')) {

                $message    =   "EL STOCK (" . $product[0]->stock . "), ES MENOR A LA CANTIDAD (" . $request->get('cant') . ")";

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'stock' => $product[0]->stock
                ]);
            }

            return response()->json(['success' => true, 'message' => "CANTIDAD VÁLIDA"]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'stock' => 0]);
        }
    }


    /*
array:10 [ // app\Http\Controllers\Tenant\SaleController.php:202
  "_token" => "HWA6Jt5lC1pNZKwmvKo1LiAP8xFGC9p2kbAME8Im"
  "type_sale" => "67"
  "payment_condition_id" => "3"
  "registration_date" => "2025-12-29"
  "expiration_date" => "2026-01-28"
  "customer_id" => "1"
  "vehicle_id" => "1"
  "plate" => "M5I098"
  "lstSale" => "[{"id":2,"brand_id":2,"category_id":2,"name":"ARRANCADOR","stock_min":0,"code_factory":null,"code_bar":null,"category_name":"RESPUESTO","brand_name":"NACIONAL","stock":"99.00","sale_price":"1.00","purchase_price":"1.00","cant":1},{"id":1,"brand_id":2,"category_id":2,"name":"BUJIA","stock_min":0,"code_factory":null,"code_bar":null,"category_name":"RESPUESTO","brand_name":"NACIONAL","stock":"99.00","sale_price":"1.00","purchase_price":"1.00","cant":1}]"
  "lstPays" => "[{"method_pay":1,"amount":2}]"
  "user_recorder_id" => "1"
  "igv_percentage" => "18.0000"
]
*/
    public function store(SaleStoreRequest $request)
    {

        DB::beginTransaction();
        try {
            $data   =   $request->toArray();

            $sale   =   $this->s_sale->store($data);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "VENTA REGISTRADA",
                'data' => (object)['sale_id' => $sale->id]
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    public function pdf_voucher($sale_id, $size = 0)
    {
        try {

            $company                =   Company::find(1);
            $sale_document          =   Sale::findOrFail($sale_id);
            $sale_products          =   SaleDetail::where('sale_document_id',$sale_id)->get();
            $sale_services          =   SaleService::where('sale_document_id',$sale_id)->get();

            $sale_document_detail   =   DB::select('SELECT *
                                        FROM sales_documents_details AS sdd
                                        WHERE sdd.sale_document_id = ?', [$sale_id]);

            $route_view             =   'sales.sale_document.pdf.pdf';
            $pdf_size               =   null;

            $data_qr                =   (object)[
                'ruc_emisor'        =>  $company->ruc,
                'tipo_comprobante'  =>  $sale_document->type_sale_code,
                'serie'             =>  $sale_document->serie,
                'correlativo'       =>  $sale_document->correlative,
                'mto_total_igv'     =>  number_format($sale_document->igv_amount, 2, '.', ''),
                'total'             =>  number_format($sale_document->total, 2, '.', ''),
                'fecha_emision'     =>  \Carbon\Carbon::parse($sale_document->created_at)->format('Y-m-d'),
                'tipo_documento_adquiriente'    =>  $sale_document->customer_document_code,
                'nro_documento_adquieriente'    =>  $sale_document->customer_document_number
            ];

            $res_qr         =   QRController::generateQr(json_encode($data_qr));
            $res_qr         =   $res_qr->getData();

            if ($res_qr->success) {
                $sale_document->ruta_qr =   $res_qr->data->ruta_qr;
                $sale_document->update();
            }

            $customer       =   Customer::find($sale_document->customer_id);

            if ((int)$size === 0) {
                $pdf_size   =   [0, 0, 226.772, 651.95];
            } else {
                $pdf_size   =   'A4';
                $route_view =   'sales.sale_document.pdf.pdf-a4';
            }

            $pdf = PDF::loadview($route_view, [
                'company'               =>  $company,
                'sale_document'         =>  $sale_document,
                'customer'              =>  $customer,
                'sale_document_detail'  =>  $sale_document_detail,
                'sale_products'         =>  $sale_products,
                'sale_services'         =>  $sale_services
            ]);

            $pdf->setPaper($pdf_size);

            return $pdf->stream($sale_document->serie . '-' . $sale_document->correlative . '.pdf');
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine()]);
        }
    }

    public function saleNote()
    {
        //
    }

    public function electronicReceipt()
    {
        //
    }

    public function quotation()
    {
        //
    }

    public function customer()
    {
        //
    }

    public function downloadXml($sale_document_id)
    {

        $sale_document  =   Sale::find($sale_document_id);

        $ruta_xml       =   $sale_document->ruta_xml;

        $filePath       = public_path("{$ruta_xml}");

        if (File::exists($filePath)) {
            return response()->download($filePath);
        } else {
            abort(404, 'Archivo no encontrado');
        }
    }

    public function downloadCdr($sale_document_id)
    {

        $sale_document  =   Sale::find($sale_document_id);

        $ruta_cdr       =   $sale_document->ruta_cdr;

        $filePath       = public_path("{$ruta_cdr}");


        if (File::exists($filePath)) {
            return response()->download($filePath);
        } else {
            abort(404, 'Archivo no encontrado');
        }
    }

    /*
sale_document_id:1
*/
    public function send_sunat(Request $request)
    {

        try {

            $sale_document_id   =   $request->get('sale_document_id');

            if (!$sale_document_id) {
                throw new Exception("NO SE ENCONTRÓ EL ID DEL COMPROBANTE DE PAGO");
            }

            $sale_document = Sale::find($sale_document_id);

            if (!$sale_document) {
                throw new Exception("COMPROBANTE DE RESERVA NO ENCONTRADO EN LA BD");
            }
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }

        $request->merge([
            'type' => "SALE_DOCUMENT"
        ]);
        $res    =   InvoiceController::send_sunat($request);
        return $res;
    }
}
