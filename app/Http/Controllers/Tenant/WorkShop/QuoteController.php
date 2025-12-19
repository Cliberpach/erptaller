<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FormatController;
use App\Http\Controllers\UtilController;
use App\Http\Requests\Tenant\WorkShop\Quote\QuoteStoreRequest;
use App\Http\Services\Tenant\WorkShop\Quotes\QuoteManager;
use App\Models\Company;
use App\Models\CompanyInvoice;
use App\Models\Department;
use App\Models\District;
use App\Models\Landlord\Color;
use App\Models\Province;
use App\Models\Tenant\Warehouse;
use App\Models\Tenant\WorkShop\Quote\Quote;
use App\Models\Tenant\WorkShop\Quote\QuoteProduct;
use App\Models\Tenant\WorkShop\Quote\QuoteService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Throwable;

class QuoteController extends Controller
{
    private QuoteManager $s_quote;

    public function __construct()
    {
        $this->s_quote  =   new QuoteManager();
    }

    public function index()
    {
        return view('workshop.quotes.index');
    }

    public function getQuotes(Request $request)
    {
        $customer_id    =   $request->get('customer_id');
        $start_date     =   $request->get('start_date');
        $end_date       =   $request->get('end_date');
        $status         =   $request->get('status');

        $quotes =   DB::connection('tenant')
            ->table('quotes as q')
            ->leftJoin('work_orders as wo', 'wo.quote_id', 'q.id')
            ->select(
                DB::raw('CONCAT("COT-",q.id) as code'),
                'q.id',
                DB::raw('CONCAT(q.customer_type_document_abbreviation,":",q.customer_document_number,"-",q.customer_name) as customer_name'),
                'q.plate',
                'q.warehouse_name',
                'q.total',
                'q.create_user_name',
                'q.status',
                'q.expiration_date',
                'q.created_at',
                'wo.id as work_order_id',
                DB::raw('CONCAT("OT-",wo.id) as work_order_code')
            )
            ->where('q.status', '<>', 'ANULADO');

        if ($customer_id) {
            $quotes->where('q.customer_id', $customer_id);
        }
        if ($start_date) {
            $quotes->whereDate('q.created_at', '>=', $start_date);
        }
        if ($end_date) {
            $quotes->whereDate('q.created_at', '<=', $end_date);
        }
        if ($status) {
            $quotes->where('q.status', '=', $status);
        }

        return DataTables::of($quotes)
            ->filterColumn('code', function ($query, $keyword) {
                $query->whereRaw("CONCAT('COT-', q.id) LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('work_order_code', function ($query, $keyword) {
                $query->whereRaw("CONCAT('OT-', wo.id) LIKE ?", ["%{$keyword}%"]);
            })
            ->toJson();
    }

    public function getQuote(int $id)
    {
        try {

            $year  =   $this->s_quote->getQuote($id);

            return response()->json(['success' => true, 'message' => 'COTIZACIÓN OBTENIDA', 'data' => $year]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function create(Request $request)
    {
        $igv                        =   round(Company::find(1)->igv, 2);
        $warehouses                 =   Warehouse::where('estado', 'ACTIVO')->get();
        $types_identity_documents   =   UtilController::getIdentityDocuments();
        $departments                =   Department::all();
        $districts                  =   District::all();
        $provinces                  =   Province::all();
        $company_invoice            =   CompanyInvoice::find(1);
        $years                      =   UtilController::getYears();
        $colors                     =   Color::where('status', 'ACTIVE')->get();
        $customer_formatted         =   FormatController::getFormatInitialCustomer(1);

        $categories                 =   UtilController::getCategoriesProducts();
        $brands                     =   UtilController::getBrandsProducts();

        return view('workshop.quotes.create', compact(
            'igv',
            'warehouses',
            'types_identity_documents',
            'departments',
            'districts',
            'provinces',
            'company_invoice',
            'years',
            'colors',
            'customer_formatted',
            'categories',
            'brands'
        ));
    }

    /*
array:17 [ // app\Http\Controllers\Tenant\WorkShop\QuoteController.php:91
  "_token" => "4olnC7YeO8JO17Yg4QWlat1MAQSJzc8VyqntCFyC"
  "_method" => "POST"
  "warehouse_id" => "1"
  "client_id" => "2"
  "vehicle_id" => "6"
  "plate" => "TR3423"
  "expiration_date" => "2025-11-28"
  "product_id" => "1"
  "product_quantity" => "3"
  "product_price" => "14.99"
  "dt-quotes-products_length" => "10"
  "service_id" => "1"
  "service_quantity" => "1"
  "service_price" => "30.00"
  "dt-quotes-services_length" => "10"
  "lst_products" => "[{"id":1,"name":"BUJÍA 20 MM","category_name":"BUJÍAS","brand_name":"ASUS","sale_price":14.99,"quantity":3,"total":44.97}]"
  "lst_services" => "[{"id":1,"name":"LAVADO DE AUTOS","sale_price":30,"quantity":1,"total":30}]"
]
*/
    public function store(QuoteStoreRequest $request)
    {
        DB::beginTransaction();

        try {

            $quote  =   $this->s_quote->store($request->toArray());
            $pdf_url   =   route('tenant.taller.cotizaciones.pdfOne', $quote->id);

            Session::flash('success', 'COTIZACIÓN REGISTRADA CON ÉXITO');
            DB::commit();
            return response()->json(['success' => true, 'message' => 'COTIZACIÓN REGISTRADA CON ÉXITO', 'pdf_url' => $pdf_url]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'file' => $th->getFile(), 'line' => $th->getLine()]);
        }
    }

    public function edit(int $id)
    {
        $igv        =   round(Company::find(1)->igv, 2);
        $warehouses =   Warehouse::where('estado', 'ACTIVO')->get();
        $quote      =   Quote::findOrFail($id);
        $types_identity_documents   =   UtilController::getIdentityDocuments();
        $departments                =   Department::all();
        $districts                  =   District::all();
        $provinces                  =   Province::all();
        $company_invoice            =   CompanyInvoice::find(1);
        $years                      =   UtilController::getYears();
        $colors                     =   Color::where('status', 'ACTIVE')->get();
        $categories                 =   UtilController::getCategoriesProducts();
        $brands                     =   UtilController::getBrandsProducts();

        $customer_formatted = FormatController::getFormatInitialCustomer($quote->customer_id);
        $vehicle_formatted = FormatController::getFormatInitialVehicle($quote->vehicle_id);
        $lst_products =   FormatController::formatLstProducts(QuoteProduct::where('quote_id', $id)->get()->toArray());
        $lst_services =   FormatController::formatLstServices(QuoteService::where('quote_id', $id)->get()->toArray());

        return view(
            'workshop.quotes.edit',
            compact(
                'igv',
                'warehouses',
                'quote',
                'customer_formatted',
                'vehicle_formatted',
                'lst_products',
                'lst_services',
                'types_identity_documents',
                'departments',
                'districts',
                'provinces',
                'company_invoice',
                'years',
                'colors',
                'categories',
                'brands'
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
]
*/
    public function update(Request $request, int $id)
    {
        DB::beginTransaction();
        try {

            $quote  =   $this->s_quote->update($request->toArray(), $id);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'COTIZACIÓN ACTUALIZADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {

            $service  =   $this->s_quote->destroy($id);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'SERVICIO ELIMINADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function pdfOne(int $id)
    {
        try {
            $pdf    =   $this->s_quote->pdfOne($id);

            return $pdf->stream("cotizacion_$id.pdf");
        } catch (Throwable $th) {
            Session::flash('message_error', $th->getMessage());
            return back();
        }
    }

    public function convertOrderCreate(int $id)
    {
        try {

            $view   =   $this->s_quote->convertOrderCreate($id);
            return $view;
        } catch (Throwable $th) {
            Session::flash('message_error', $th->getMessage());
            return back();
        }
    }
}
