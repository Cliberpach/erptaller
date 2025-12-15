<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\Tenant\KardexExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
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
        $kardex = DB::table('kardex as k')
            ->select(
                'k.id',
                'k.created_at',
                'k.product_id',
                'k.product_name',
                'k.category_id',
                'k.category_name',
                'k.brand_id',
                'k.brand_name',
                'k.type',
                'k.warehouse_id',
                'k.sale_document_id',
                'k.note_income_id',
                'k.document',
                'k.quantity',
                'k.user_recorder_id',
                'k.user_recorder_name'
            )
            ->where('k.status', '!=', 'INACTIVE')
            ->orderBy('k.product_id')
            ->orderBy('k.warehouse_id')
            ->orderBy('k.created_at');


        if ($request->get('product_id')) {
            $kardex = $kardex->where('k.product_id', $request->get('product_id'));
        }

        if ($request->get('date_start')) {
            $kardex = $kardex->whereDate('k.created_at', '>=', $request->get('date_start'));
        }

        if ($request->get('date_end')) {
            $kardex = $kardex->whereDate('k.created_at', '<=', $request->get('date_end'));
        }

        $kardex = $kardex->get();

        return self::calculateStockMovement($kardex);
    }

    protected static function calculateStockMovement($kardexCollection)
    {
        $stock_by_product = [];

        return $kardexCollection->map(function ($item) use (&$stock_by_product) {
            $key = $item->product_id . '-' . $item->warehouse_id;

            $previous_stock = $stock_by_product[$key] ?? 0;

            $entrada = $item->type === 'IN'  ? (float) $item->quantity : 0;
            $salida  = $item->type === 'OUT' ? (float) $item->quantity : 0;

            $saldo = $previous_stock + $entrada - $salida;

            // Añadir los campos al objeto
            $item->stock_previous = number_format($previous_stock, 2, '.', '');
            $item->entrada = number_format($entrada, 2, '.', '');
            $item->salida  = number_format($salida, 2, '.', '');
            $item->stock_later = number_format($saldo, 2, '.', '');

            // Actualizamos el acumulado
            $stock_by_product[$key] = $saldo;

            return $item;
        });
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
        $kardex =   $this->queryKardex($request);

        $kardex->transform(function ($item) {
            unset($item->id);
            unset($item->product_id);
            unset($item->category_id);
            unset($item->brand_id);
            unset($item->sale_document_id);
            unset($item->note_income_id);
            unset($item->user_recorder_id);
            return $item;
        });

        return Excel::download(new KardexExport($kardex, $request), 'kardex.xlsx');
    }

    public function pdf(Request $request)
    {

        $company                =   Company::find(1);

        $report_kardex          =   $this->queryKardex($request);

        $report_kardex->transform(function ($item) {
            unset($item->id);
            unset($item->product_id);
            unset($item->category_id);
            unset($item->brand_id);
            unset($item->sale_document_id);
            unset($item->note_income_id);
            unset($item->user_recorder_id);
            return $item;
        });


        $pdf = Pdf::loadview('inventory.kardex.pdf.pdf', [
            'company'               =>  $company,
            'report_kardex'         =>  $report_kardex,
            'filters'               =>  $request

        ])->setPaper('a4', 'landscape');


        return $pdf->stream('reporte_kardex_' . Carbon::now()->format('Y_m_d_H_i_s') . '.pdf');
    }
}
