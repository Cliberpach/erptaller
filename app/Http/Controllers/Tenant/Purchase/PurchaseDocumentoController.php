<?php

namespace App\Http\Controllers\Tenant\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilController;
use App\Http\Requests\Purchase\PurchaseDocument\PurchaseDocumentStoreRequest;
use App\Http\Services\Tenant\Purchases\Purchase\PurchaseManager;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Throwable;

class PurchaseDocumentoController extends Controller
{
    private PurchaseManager $s_purchase;

    public function __construct()
    {
        $this->s_purchase   =   new PurchaseManager();
    }

    public function index()
    {
        return view('purchases.purchase_document.index', [
            // Fechas por defecto = mes en curso, filtrando por delivery_date (fecha del
            // documento de compra, no created_at/tipeo).
            'fecha_inicio' => now()->startOfMonth()->toDateString(),
            'fecha_fin'    => now()->toDateString(),
        ]);
    }

    public function getPurchaseDocuments(Request $request)
    {
        $items  =   $this->queryAll($request);

        return DataTables::of($items)->make(true);
    }

    public function queryAll(Request $request)
    {
        $supplier_id    =   $request->get('supplier');
        $status         =   $request->get('status');
        $start_date     =   $request->get('start_date');
        $end_date       =   $request->get('end_date');
        $filter_id      =   $request->get('id');

        $items  =    DB::table('purchase_documents as pd')
            ->select(
                'pd.id',
                'pd.delivery_date',
                'pd.supplier_name',
                'pd.supplier_type_document_abbreviation',
                'pd.supplier_document_number',
                'pd.currency',
                'pd.document_type',
                'pd.serie',
                'pd.correlative',
                'pd.observation',
                'pd.payment_status',
                'pd.payment_condition_name',
            )
            ->where('pd.status', '!=', 'ANULADO');

        if ($supplier_id) {
            $items->where('pd.supplier_id', $supplier_id);
        }
        if ($status) {
            $items->where('pd.payment_status', $status);
        }
        if ($start_date) {
            $items->whereDate('pd.delivery_date', '>=', $start_date);
        }
        if ($end_date) {
            $items->whereDate('pd.delivery_date', '<=', $end_date);
        }
        if ($filter_id) {
            $items->where('pd.id', $filter_id);
        }

        return $items;
    }

    public function getProducts(Request $request)
    {

        $categoria_id   =   $request->get('categoria_id');
        $marca_id       =   $request->get('marca_id');

        $products = DB::table('products as p')
            ->leftJoin('warehouse_products as wp', function ($join) {
                $join->on('wp.product_id', '=', 'p.id')
                    ->where('wp.warehouse_id', '=', 1)
                    ->where('wp.stock', '>', '0'); // Filtrar por almacen_id = 1
            })
            ->join('brands as b', 'b.id', '=', 'p.brand_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->select(
                'p.id',
                'p.brand_id',
                'p.category_id',
                'p.name',
                'p.sale_price',
                DB::raw('IFNULL(wp.stock, 0) as stock'),
                'p.stock_min',
                'b.name as brand_name',
                'c.name as category_name',
                'p.purchase_price'
            );

        if ($categoria_id) {
            $products  =   $products->where('p.category_id', $categoria_id);
        }

        if ($marca_id) {
            $products  =   $products->where('p.brand_id', $marca_id);
        }

        $products  =   $products->get();


        return DataTables::of($products)
            ->make(true);
    }

    public function create()
    {
        $categories                 =   Category::all();
        $brands                     =   Brand::all();

        $colaborador_registrador    =   DB::select('select
                                        *
                                        from
                                        users as u
                                        where u.id = ?', [Auth::user()->id])[0];

        $suppliers                  =   Supplier::where('estado', 'ACTIVO')->get();

        $igv                        =   DB::select('select c.igv from companies as c')[0]->igv;

        $type_identity_documents    =   UtilController::getIdentityDocuments();
        $payment_conditions         =   UtilController::getPaymentConditions();


        return view(
            'purchases.purchase_document.create',
            compact(
                'categories',
                'brands',
                'colaborador_registrador',
                'suppliers',
                'igv',
                'type_identity_documents',
                'payment_conditions'
            )
        );
    }


    /*
array:18 [ // app\Http\Controllers\Tenant\Purchase\PurchaseDocumentoController.php:121
  "_token"              => "6w9LoYZuswkRqecEN18TfE1aqNudu1s40cFAxJoh"
  "fecha_registro"      => "2024-11-29"
  "fecha_entrega"       => "2024-11-29"
  "usuario"             => "SUPERADMIN"
  "proveedor"           => "4"
  "tipo_doc"            => "BOLETA"
  "igv_chk"             => "18"  //====== SI O NO =====
  "igv_value"           => "18"  // %IGV
  "serie"               => "B001"
  "numero"              => "541"
  "observation"         => "documento compra test"
  "moneda"              => "PEN"
  "producto"            => null
  "precio"              => null
  "cantidad"            => null
  "tbl_purchase_document_detail_length" => "10"
  "lstPurchaseDocument" => "[{"product_id":5,"product_name":"GRANOLA UNIÓN","category_name":"SNACKS","brand_name":"LAYS","producto_unidad_medida":"NIU","quantity":"23","purchase_price":"9.00","almacen_id":null,"total":207},{"product_id":3,"product_name":"PAPA LAYS","category_name":"SNACKS","brand_name":"LAYS","producto_unidad_medida":"NIU","quantity":"12","purchase_price":"1.20","almacen_id":null,"total":14.399999999999999}]"
  "user_recorder_id"    => "1"
  "user_recorder_name"  => "SUPERADMIN"
  "payment_condition_id" => "2"
  "expiration_date" => "2026-01-24"
]
*/
    public function store(PurchaseDocumentStoreRequest $request)
    {
        DB::beginTransaction();

        try {

            $this->s_purchase->store($request->toArray());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'DOCUMENTO DE COMPRA REGISTRADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'succes' => false,
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);
        }
    }

    public function show($purchase_id)
    {

        try {

            $document   =   DB::select('select * from purchase_documents as dp
                            where dp.id = ?', [$purchase_id]);

            if (count($document) === 0) {
                throw new Exception("NO EXISTE EL DOCUMENTO DE COMPRA EN LA BD!!");
            }

            $detail     =   DB::select('select * from purchase_documents_detail as dpd
                            where dpd.purchase_document_id = ?', [$purchase_id]);


            return response()->json([
                'success' => true,
                'message' => 'DOCUMENTO COMPRA OBTENIDO',
                'purchase_document' => $document[0],
                'detail' => $detail
            ]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
