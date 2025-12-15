<?php

namespace App\Http\Controllers\Tenant\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\KardexController;
use App\Http\Controllers\UtilController;
use App\Http\Requests\Purchase\PurchaseDocument\PurchaseDocumentStoreRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Tenant\PurchaseDocument;
use App\Models\Tenant\PurchaseDocumentDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class PurchaseDocumentoController extends Controller
{
    public function index(){
        return view('purchases.purchase_document.index');
    }

    public function getPurchaseDocuments(Request $request){
        $purchase_documents  =    DB::table('purchase_documents as dp')
                                    ->select(
                                        'dp.id',
                                        'dp.delivery_date',
                                        'dp.supplier_name',
                                        'dp.supplier_type_document_abbreviation',
                                        'dp.supplier_document_number',
                                        'dp.condition',
                                        'dp.currency',
                                        'dp.document_type',
                                        'dp.serie',
                                        'dp.correlative',
                                        'dp.observation'
                                    )
                                    ->where('dp.estado','!=','ANULADO')
                                    ->get();

        return DataTables::of($purchase_documents)->make(true);
    }

    public function getProducts(Request $request){

        $categoria_id   =   $request->get('categoria_id');
        $marca_id       =   $request->get('marca_id');

        $products = DB::table('products as p')
                    ->leftJoin('warehouse_products as wp', function($join) {
                        $join->on('wp.product_id', '=', 'p.id')
                            ->where('wp.warehouse_id', '=', 1)
                            ->where('wp.stock','>','0'); // Filtrar por almacen_id = 1
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

        if($categoria_id){
            $products  =   $products->where('p.category_id',$categoria_id);
        }

        if($marca_id){
            $products  =   $products->where('p.brand_id',$marca_id);
        }

        $products  =   $products->get();


        return DataTables::of($products)
                ->make(true);
    }

    public function create(){
        $categories                 =   Category::all();
        $brands                     =   Brand::all();
        $colaborador_registrador    =   DB::select('select
                                        *
                                        from
                                        users as u
                                        where u.id = ?',[Auth::user()->id])[0];

        $suppliers                  =   Supplier::where('estado','ACTIVO')->get();

        $igv                        =   DB::select('select c.igv from companies as c')[0]->igv;

        $type_identity_documents    =   DB::select('select *
                                        from types_identity_documents as tid
                                        where
                                        tid.id = "1"
                                        or tid.id = "3" ');

        return view('purchases.purchase_document.create',
        compact('categories','brands','colaborador_registrador','suppliers','igv','type_identity_documents'));
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
]
*/
    public function store(PurchaseDocumentStoreRequest $request){
        DB::beginTransaction();


        $lstPurchaseDocument    =   json_decode($request->get('lstPurchaseDocument'));
        $lstPurchaseDocument    =   $this->validationLstPurchaseDocument($lstPurchaseDocument);

        $montos                 =   PurchaseDocumentoController::calcularMontos($lstPurchaseDocument,$request->get('igv_chk',null),$request->get('igv_value'));

        try {

            $supplier           =   DB::select('select
                                    s.name,
                                    tid.abbreviation as type_document_abbreviation,
                                    s.document_number
                                    from suppliers as s
                                    inner join  types_identity_documents as tid on tid.id = s.type_identity_document_id
                                    where s.id = ?',[$request->get('proveedor')])[0];

            $warehouse          =   DB::select('select *
                                    from warehouses as w
                                    where w.id = 1')[0];

            $purchase_document                                      =   new PurchaseDocument();
            $purchase_document->delivery_date                       =   $request->get('fecha_entrega');
            $purchase_document->supplier_id                         =   $request->get('proveedor');
            $purchase_document->supplier_name                       =   $supplier->name;
            $purchase_document->supplier_type_document_abbreviation =   $supplier->type_document_abbreviation;
            $purchase_document->supplier_document_number            =   $supplier->document_number;
            $purchase_document->condition                           =   'CONTADO';
            $purchase_document->currency                            =   $request->get('moneda');
            $purchase_document->document_type                       =   $request->get('tipo_doc');
            $purchase_document->serie                               =   $request->get('serie');
            $purchase_document->correlative                         =   $request->get('numero');
            $purchase_document->observation                         =   $request->get('observation');
            $purchase_document->user_recorder_id                    =   Auth::user()->id;
            $purchase_document->user_recorder_name                  =   Auth::user()->name;
            $purchase_document->prices_with_igv           =   $request->get('igv_chk')?1:0;
            $purchase_document->igv                       =   $request->get('igv_value');
            $purchase_document->subtotal                  =   $montos->subtotal;
            $purchase_document->amount_igv                =   $montos->monto_igv;
            $purchase_document->total                     =   $montos->total;
            $purchase_document->save();

            foreach ($lstPurchaseDocument as  $item) {

                $detail                         =   new PurchaseDocumentDetail();
                $detail->purchase_document_id   =   $purchase_document->id;
                $detail->product_id             =   $item->product_id;
                $detail->brand_id               =   $item->brand_id;
                $detail->category_id            =   $item->category_id;
                $detail->product_name           =   $item->product_name;
                $detail->brand_name             =   $item->brand_name;
                $detail->category_name          =   $item->category_name;
                $detail->warehouse_id           =   $warehouse->id;
                $detail->warehouse_name         =   $warehouse->descripcion;
                $detail->quantity               =   $item->quantity;
                $detail->purchase_price         =   $item->purchase_price;
                $detail->subtotal               =   $item->quantity * $item->purchase_price;
                $detail->save();

                //======= OBTENIENDO STOCK PREVIO =====
                $stock_previous                     =  UtilController::getStock($item->product_id);

                //====== INSERTANDO STOCK =====
                DB::update('UPDATE warehouse_products
                SET updated_at = ?, stock = stock + ?
                WHERE warehouse_id = 1
                and product_id = ?', [Carbon::now() , $item->quantity , $item->product_id]);

                $stock_later                        =   UtilController::getStock($item->product_id);

                //===== GRABANDO EN KARDEX ========
                $request_kardex     =   new Request();
                $request_kardex->merge([
                                        'warehouse_id'      =>  $warehouse->id,
                                        'product_id'        =>  $item->product_id,
                                        'brand_id'          =>  $item->brand_id,
                                        'category_id'       =>  $item->category_id,
                                        'quantity'          =>  $item->quantity,
                                        'price_sale'        =>  null,
                                        'amount'            =>  null,
                                        'type'              =>  'IN',
                                        'document'          =>  'CO-'.$purchase_document->id,
                                        'product_name'      =>  $item->product_name,
                                        'brand_name'        =>  $item->brand_name,
                                        'category_name'     =>  $item->category_name,
                                        'stock_previous'    =>  $stock_previous,
                                        'stock_later'       =>  $stock_later,
                                        'purchase_document_id'  =>  $purchase_document->id,
                                        'customer_id'           =>  null,
                                        'customer_name'         =>  null,
                                        'user_recorder_id'      =>  Auth::user()->id,
                                        'user_recorder_name'    =>  Auth::user()->name]);

                KardexController::store($request_kardex);
            }

            DB::commit();
            return response()->json(['success'=>true,'message'=>'DOCUMENTO DE COMPRA REGISTRADO']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['succes'=>false,'message'=>$th->getMessage()]);
        }
    }

    public static function calcularMontos($lstPurchaseDocument,$igv_chk,$igv_value){
        $subtotal   =   0;
        $monto_igv  =   0;
        $total      =   0;
        $valor_igv  =   $igv_value;

        if($igv_chk){
            foreach ($lstPurchaseDocument as $item) {
                $total  +=  (float)$item->total;
            }
            $subtotal    =   $total/((100 + (float)$valor_igv)/100);
            $monto_igv   =   $total - $subtotal;
        }else{
            //======= PRECIOS SIN IGV =======
            foreach ($lstPurchaseDocument as $item) {
                $subtotal  +=  (float)$item->total;
            }

            $monto_igv   =   ((float)$valor_igv/100)*$subtotal;
            $total       =   $subtotal + $monto_igv;
        }

        return (object)['subtotal'=>$subtotal,'monto_igv'=>$monto_igv,'total'=>$total];
    }



    public static function validationLstPurchaseDocument($lstPurchaseDocument){

        foreach ($lstPurchaseDocument as $item) {

            //======= VALIDANDO CANTIDAD =======
            if (!is_numeric($item->quantity) || $item->quantity <= 0) {
                throw new Exception("LA CANTIDAD DEL PRODUCTO NO ES VÁLIDA!!!");
            }

            //======== VALIDANDO PRODUCTO =====
            $product_exists                 =   DB::select('select
                                                p.id,
                                                p.name,
                                                br.name as brand_name,
                                                c.name as category_name,
                                                br.id as brand_id,
                                                c.id as category_id
                                                from products as p
                                                inner join brands as br on br.id = p.brand_id
                                                inner join categories as c on c.id = p.category_id
                                                where p.id = ?',[$item->product_id]);


            if(count($product_exists) === 0){
                throw new Exception("EL PRODUCTO ".$item->product_name." NO EXISTE EN LA BD!!!");
            }

            $item->product_name     =   $product_exists[0]->name;
            $item->brand_name       =   $product_exists[0]->brand_name;
            $item->category_name    =   $product_exists[0]->category_name;
            $item->brand_id         =   $product_exists[0]->brand_id;
            $item->category_id      =   $product_exists[0]->category_id;

        }

        return $lstPurchaseDocument;
    }

    public function show($purchase_id){

        try {

            $document   =   DB::select('select * from purchase_documents as dp
                            where dp.id = ?',[$purchase_id]);

            if(count($document) === 0){
                throw new Exception("NO EXISTE EL DOCUMENTO DE COMPRA EN LA BD!!");
            }

            $detail     =   DB::select('select * from purchase_documents_detail as dpd
                            where dpd.purchase_document_id = ?',[$purchase_id]);


            return response()->json(['success'=>true,
            'message'=>'DOCUMENTO COMPRA OBTENIDO',
            'purchase_document'=>$document[0],
            'detail'=>$detail]);


        } catch (\Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }
    }


}
