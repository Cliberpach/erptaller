<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\Tenant\Inventory\Kardex\KardexExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\Tenant\Kardex;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class KardexController extends Controller
{

    public function index()
    {

        $products   =   DB::select('select
                        p.id,
                        p.name
                        from products as p
                        where p.status = "ACTIVE"');

        return view('inventory.kardex.index', compact('products'));
    }

    public function getKardex(Request $request)
    {
        $kardex =   $this->queryKardex($request);

        return DataTables::of($kardex)->make(true);
    }

    public static function queryKardex(Request $request)
    {
        $warehouse_id   =   $request->get('warehouse_id');
        $product_id     =   $request->get('product_id');
        $start_date     =   $request->get('start_date');
        $end_date       =   $request->get('end_date');

        $kardex =   DB::select(
            'CALL sp_kardex(?, ?, ?,?)',
            [$warehouse_id, $product_id, $start_date, $end_date]
        );

        return $kardex;
    }




    /*
array:14 [ // app\Http\Controllers\Tenant\KardexController.php:11
  "product_id"          => 1
  "brand_id"            => 3
  "category_id"         => 3
  "quantity"            => 2
  "price_sale"          => "2.30"
  "amount"              => 4.6
  "type"                => "SALE"
  "document"            => "B001-1"
  "product_name"        => "PAPA LAYS"
  "brand_name"          => "LAYS"
  "category_name"       => "SNACKS"
  "stock_previous"      => "200.00"
  "stock_later"         => "198.00"
  "sale_document_id"    => 43
]
    */
    public static function store(Request $request)
    {
        try {

            $kardex                     =   new Kardex();
            $kardex->warehouse_id       =   $request->get('warehouse_id');
            $kardex->product_id         =   $request->get('product_id');
            $kardex->brand_id           =   $request->get('brand_id');
            $kardex->category_id        =   $request->get('category_id');
            $kardex->quantity           =   $request->get('quantity');
            $kardex->price_sale         =   $request->get('price_sale');
            $kardex->amount             =   $request->get('amount');
            $kardex->type               =   $request->get('type');
            $kardex->document           =   $request->get('document');
            $kardex->product_name       =   $request->get('product_name');
            $kardex->brand_name         =   $request->get('brand_name');
            $kardex->category_name      =   $request->get('category_name');
            $kardex->stock_previous     =   $request->get('stock_previous');
            $kardex->stock_later        =   $request->get('stock_later');
            $kardex->sale_document_id   =   $request->get('sale_document_id') ?? null;
            $kardex->note_income_id     =   $request->get('note_income_id') ?? null;
            $kardex->note_release_id        =   $request->get('note_release_id') ?? null;
            $kardex->purchase_document_id   =   $request->get('purchase_document_id') ?? null;
            $kardex->customer_id        =   $request->get('customer_id');
            $kardex->customer_name      =   $request->get('customer_name');
            $kardex->user_recorder_id   =   $request->get('user_recorder_id');
            $kardex->user_recorder_name =   $request->get('user_recorder_name');
            $kardex->registration_date  =   now();
            $kardex->save();
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function excel(Request $request)
    {
        $data =   $this->queryKardex($request);

        if ($request->get('product_id')) {
            $request->merge([
                'product_name' => Product::findOrFail($request->get('product_id'))->name
            ]);
        }

        $fecha = Carbon::now()->format('Y-m-d');
        $fecha = $request->date ?? Carbon::now()->format('Y-m-d');

        return Excel::download(
            new KardexExport($data, $request, Company::findOrFail(1)),
            "kardex_producto_{$fecha}.xlsx"
        );
    }

    public function pdf(Request $request)
    {

        $company       =   Company::find(1);
        $data          =   $this->queryKardex($request);

        $pdf = Pdf::loadview('inventory.kardex.pdf.pdf', [
            'company'               =>  $company,
            'data'                  =>  $data,
            'filters'               =>  $request

        ])->setPaper('a4', 'landscape');


        return $pdf->stream('kardex_producto' . Carbon::now()->format('Y_m_d_H_i_s') . '.pdf');
    }
}
