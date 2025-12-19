<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FormatController;
use App\Http\Controllers\UtilController;
use App\Http\Services\Tenant\WorkShop\WorkOrders\WorkOrderManager;
use App\Models\Company;
use App\Models\CompanyInvoice;
use App\Models\Department;
use App\Models\District;
use App\Models\Landlord\Color;
use App\Models\Province;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Warehouse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Throwable;

class WorkOrderController extends Controller
{
    private WorkOrderManager $s_order;

    public function __construct()
    {
        $this->s_order  =   new WorkOrderManager();
    }

    public function index()
    {
        return view('workshop.work_orders.index');
    }

    public function getWorkOrders(Request $request)
    {
        $customer_id    =   $request->get('customer_id');
        $start_date     =   $request->get('start_date');
        $end_date       =   $request->get('end_date');
        $status         =   $request->get('status');

        $orders = DB::connection('tenant')
            ->table('work_orders as o')
            ->leftJoin('customer_accounts as ca', 'ca.work_order_id', 'o.id')
            ->select(
                'o.id',
                DB::raw('CONCAT("OT-",o.id) as code'),
                DB::raw('CONCAT(o.customer_type_document_abbreviation,":",o.customer_document_number,"-",o.customer_name) as customer_name'),
                'o.plate',
                'o.warehouse_name',
                'o.total',
                'o.create_user_name',
                'o.status',
                'o.created_at',
                'o.status_invoice',
                'o.quote_id',
                DB::raw('CONCAT("COT-",o.quote_id) as quote_code'),
                'ca.balance',
                DB::raw('(ca.amount - ca.balance) as on_account')
            )
            ->where('o.status', '<>', 'ANULADO');

        if($customer_id){
            $orders->where('o.customer_id',$customer_id);
        }
        if($start_date){
            $orders->whereDate('o.created_at','>=',$start_date);
        }
        if($end_date){
            $orders->whereDate('o.created_at','<=',$end_date);
        }
        if($status){
            $orders->where('o.status','=',$status);
        }

        return DataTables::of($orders)
            ->filterColumn('code', function ($query, $keyword) {
                $query->whereRaw("CONCAT('OT-', o.id) LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('quote_code', function ($query, $keyword) {
                $query->whereRaw("CONCAT('COT-', o.quote_id) LIKE ?", ["%{$keyword}%"]);
            })
            ->toJson();
    }

    public function getWorkOrder(int $id)
    {
        try {

            $year  =   $this->s_order->getWorkOrder($id);

            return response()->json(['success' => true, 'message' => 'ORDEN DE TRABAJO OBTENIDA', 'data' => $year]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function create(Request $request)
    {
        $igv                        =   round(Company::find(1)->igv, 2);
        $warehouses                 =   Warehouse::where('estado', 'ACTIVO')->get();
        $checks_inventory_vehicle   =   UtilController::getInventoryVehicleChecks();
        $technicians                =   UtilController::getTechnicians();
        $customer_formatted         =   FormatController::getFormatInitialCustomer(1);
        $validate_stock             =   Configuration::findOrFail(2)->property;
        $types_identity_documents   =   UtilController::getIdentityDocuments();
        $departments                =   Department::all();
        $districts                  =   District::all();
        $provinces                  =   Province::all();
        $company_invoice            =   CompanyInvoice::find(1);
        $years                      =   UtilController::getYears();
        $colors                     =   Color::where('status', 'ACTIVE')->get();
        $categories                 =   UtilController::getCategoriesProducts();
        $brands                     =   UtilController::getBrandsProducts();
        $configuration              =   Configuration::findOrFail(2);

        return view('workshop.work_orders.create', compact(
            'igv',
            'warehouses',
            'checks_inventory_vehicle',
            'technicians',
            'customer_formatted',
            'validate_stock',
            'types_identity_documents',
            'departments',
            'provinces',
            'districts',
            'company_invoice',
            'years',
            'colors',
            'categories',
            'brands',
            'configuration'
        ));
    }

    /*
array:18 [ // app\Http\Controllers\Tenant\WorkShop\WorkOrderController.php:102
  "_token" => "EAIxRCarInINDg0PeQVtzkSpimjPtaszEuWLeARl"
  "_method" => "POST"
  "warehouse_id" => "1"
  "client_id" => "2"
  "vehicle_id" => "10"
  "inventory_items" => array:6 [
    0 => "2"
    1 => "6"
    2 => "14"
    3 => "19"
    4 => "22"
    5 => "23"
  ]
  "fuel_level" => "-1"
  "technicians" => "1"
  "product_id" => "1"
  "product_quantity" => "2"
  "product_price" => "14.99"
  "dt-quotes-products_length" => "10"
  "service_id" => "1"
  "service_quantity" => "1"
  "service_price" => "30.00"
  "dt-quotes-services_length" => "10"
  "lst_products" => "[{"id":1,"name":"BUJÍA 20 MM","category_name":"BUJÍAS","brand_name":"ASUS","sale_price":14.99,"quantity":2,"total":29.98}]"
  "lst_services" => "[{"id":1,"name":"LAVADO DE AUTOS","sale_price":30,"quantity":1,"total":30}]"
  "vehicle_images" => array:2 [
    0 =>Illuminate\Http\UploadedFile {#2238}
    4 =>Illuminate\Http\UploadedFile {#2243}
  ]
]
*/
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $order  =   $this->s_order->store($request->toArray());
            $pdf_url   =   route('tenant.taller.ordenes_trabajo.pdfOne', $order->id);

            Session::flash('success', 'ORDEN DE TRABAJO REGISTRADA CON ÉXITO');
            DB::commit();
            return response()->json(['success' => true, 'message' => 'ORDEN DE TRABAJO REGISTRADA CON ÉXITO', 'pdf_url' => $pdf_url]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'file' => $th->getFile(), 'line' => $th->getLine()]);
        }
    }

    public function edit(int $id)
    {
        $igv                        =   round(Company::find(1)->igv, 2);
        $warehouses                 =   Warehouse::where('estado', 'ACTIVO')->get();
        $checks_inventory_vehicle   =   UtilController::getInventoryVehicleChecks();
        $technicians                =   UtilController::getTechnicians();

        $order                      =   $this->s_order->getWorkOrder($id);
        $work_order                 =   $order['order'];

        $customer_formatted         =   FormatController::getFormatInitialCustomer($work_order->customer_id);
        $vehicle_formatted          =   FormatController::getFormatInitialVehicle($work_order->vehicle_id);

        $lst_products               =   FormatController::formatLstProducts($order['products']->toArray());
        $lst_services               =   FormatController::formatLstServices($order['services']->toArray());
        $lst_inventory              =   FormatController::formatLstInventory($order['inventory']->toArray());
        $lst_technicians            =   FormatController::formatLstTechnicians($order['technicians']->toArray());
        $lst_images                 =   FormatController::formatLstImages($order['images']->toArray());

        $types_identity_documents   =   UtilController::getIdentityDocuments();
        $departments                =   Department::all();
        $districts                  =   District::all();
        $provinces                  =   Province::all();
        $company_invoice            =   CompanyInvoice::find(1);
        $years                      =   UtilController::getYears();
        $colors                     =   Color::where('status', 'ACTIVE')->get();
        $categories                 =   UtilController::getCategoriesProducts();
        $brands                     =   UtilController::getBrandsProducts();
        $configuration              =   Configuration::findOrFail(2);

        return view(
            'workshop.work_orders.edit',
            compact(
                'igv',
                'warehouses',
                'work_order',
                'customer_formatted',
                'vehicle_formatted',
                'lst_products',
                'lst_services',
                'checks_inventory_vehicle',
                'technicians',
                'lst_inventory',
                'lst_technicians',
                'lst_images',

                'types_identity_documents',
                'departments',
                'provinces',
                'districts',
                'company_invoice',
                'years',
                'colors',
                'categories',
                'brands',
                'configuration'
            )
        );
    }

    /*
array:17 [ // app\Http\Controllers\Tenant\WorkShop\QuoteController.php:145
  "_token" => "dmJ2sDFFwSLunK4KEKQdm6Wkn6XbriZ10upcdNKx"
  "_method" => "PUT"
  "warehouse_id" => "1"
  "client_id" => "2"
  "vehicle_id" => "6"
  "plate" => "TR3423"
  "expiration_date" => "2025-11-27"
  "product_id" => null
  "product_quantity" => null
  "product_price" => null
  "dt-quotes-products_length" => "10"
  "service_id" => null
  "service_quantity" => null
  "service_price" => null
  "dt-quotes-services_length" => "10"
  "lst_products" => "[{"id":1,"name":"BUJÍA 20 MM","category_name":"BUJÍAS","brand_name":"ASUS","sale_price":"14.990000","quantity":"3.000000","total":"44.970000"}]"
  "lst_services" => "[{"id":1,"name":"LAVADO DE AUTOS","sale_price":"30.000000","quantity":"1.000000","total":"30.000000"}]"
  "vehicle_images" => array:2 [
    0 =>Illuminate\Http\UploadedFile {#2238}
    4 =>Illuminate\Http\UploadedFile {#2243}
]
*/
    public function update(Request $request, int $id)
    {
        DB::beginTransaction();
        try {

            $work_order  =   $this->s_order->update($request->toArray(), $id);

            Session::flash('message_success', 'ORDEND DE TRABAJO ACTUALIZADA CON ÉXITO');
            DB::commit();

            return response()->json(['success' => true, 'message' => 'ORDEN DE TRABAJO ACTUALIZADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {

            $work_order  =   $this->s_order->destroy($id);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'ORDEN DE TRABAJO ELIMINADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function finish(int $id)
    {
        DB::beginTransaction();
        try {

            $work_order  =   $this->s_order->finish($id);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'ORDEN DE TRABAJO FINALIZADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function pdfOne(int $id)
    {
        try {
            $pdf    =   $this->s_order->pdfOne($id);

            return $pdf->stream("orden_trabajo_$id.pdf");
        } catch (Throwable $th) {
            Session::flash('message_error', $th->getMessage());
            return back();
        }
    }

    public function invoiceCreate(int $id)
    {
        try {
            $view   =   $this->s_order->invoiceCreate($id);
            return $view;
        } catch (Throwable $th) {
            return back();
        }
    }

    /*
array:16 [ // app\Http\Controllers\Tenant\WorkShop\WorkOrderController.php:327
  "_token" => "YqcvEqq5grlzzcaGuNm4u9CTRIp7Y9ntQzwa1yUI"
  "_method" => "POST"
  "warehouse_id" => "1"
  "client_id" => "3"
  "vehicle_id" => "7"
  "product_id" => null
  "product_stock" => null
  "product_quantity" => null
  "product_price" => null
  "dt-orders-products_length" => "10"
  "service_id" => null
  "service_quantity" => null
  "service_price" => null
  "dt-orders-services_length" => "10"
  "lst_products" => "[{"warehouse_id":1,"id":1,"name":"BUJÍA 20 MM","category_name":"BUJÍAS","brand_name":"ASUS","sale_price":"14.990000","quantity":"2.000000","total":"29.980000"},{"warehouse_id":1,"id":13,"name":"ARRANCADOR Z","category_name":"LLAVES","brand_name":"NASCAR","sale_price":"1.000000","quantity":"1.000000","total":"1.000000"}]"
  "lst_services" => "[{"id":3,"name":"RENOVACIÓN DE MOTOR","sale_price":"2000.000000","quantity":"2.000000","total":"4000.000000"}]"
]
*/
    public function invoiceStore(Request  $request)
    {
        DB::beginTransaction();
        try {
            $sale       =   $this->s_order->invoiceStore($request->toArray());
            $pdf_url    =   route('tenant.ventas.comprobante_venta.pdf_voucher', ['id' => $sale->id, 'size' => 1]);
            $message    =   'OT-' . $request->get('work_order_id') . ' ,FACTURADA CON ÉXITO: ' . $sale->serie . '-' . $sale->correlative;

            Session::flash('message_success', $message);

            DB::commit();
            return response()->json([
                'success'   =>  true,
                'message'   =>  $message,
                'pdf_url'   =>  $pdf_url
            ]);
        } catch (Throwable $th) {
            DB::rollBack();

            Session::flash('message_error',$th->getMessage());
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ]);
        }
    }
}
