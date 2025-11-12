<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Services\Tenant\Inventory\NoteIncome\NoteIncomeManager;
use App\Http\Services\Tenant\Inventory\NoteIncome\NoteIncomeService;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Tenant\NoteIncome;
use App\Models\Tenant\NoteIncomeDetail;
use App\Models\Tenant\WarehouseProduct;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class NoteIncomeController extends Controller
{
    protected NoteIncomeManager $s_note_income;

    public function __construct(){
        $this->s_note_income    =   new NoteIncomeManager();
    }

    public function index(){
        return view('inventory.note_income.index');
    }

    public function getNoteIncome(Request $request){

        $notes_income    =   DB::table('notes_income as ni')
                            ->select(
                              'ni.id',
                              'ni.created_at',
                              'ni.user_recorder_name',
                              'ni.observation',
                            )
                            ->where('ni.estado','!=','ANULADO')
                            ->orderByDesc('ni.created_at');

        if($request->get('product_id')){
            $notes_income =   $notes_income->where('k.product_id',$request->get('product_id'));
        }

        if($request->get('date_start')){
            $notes_income = $notes_income->whereRaw('DATE(k.created_at) >= ?', [$request->get('date_start')]);
        }

        if($request->get('date_end')){
            $notes_income = $notes_income->whereRaw('DATE(k.created_at) <= ?', [$request->get('date_end')]);
        }

        return DataTables::of($notes_income)->make(true);

    }

    public function getProducts(Request $request){

        $categoria_id   =   $request->get('categoria_id');
        $marca_id       =   $request->get('marca_id');

        $products = DB::table('products as p')
                    ->leftJoin('warehouse_products as wp', function($join) {
                        $join->on('wp.product_id', '=', 'p.id')
                            ->where('wp.warehouse_id', '=', 1); // Filtrar por almacen_id = 1
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
                    );

        if($categoria_id){
            $products  =   $products->where('p.category_id',$categoria_id);
        }

        if($marca_id){
            $products  =   $products->where('p.brand_id',$marca_id);
        }

        $products  =   $products->get();


        return DataTables::of($products)->make(true);
    }

    public function create(){

        $categories                 =   Category::all();
        $brands                     =   Brand::all();
        $colaborador_registrador    =   DB::select('select
                                        *
                                        from
                                        users as u
                                        where u.id = ?',[Auth::user()->id])[0];

        return view('inventory.note_income.create',compact('categories','brands','colaborador_registrador'));
    }

/*
array:4 [ // app\Http\Controllers\Tenant\NoteIncomeController.php:11
  "user_recorder_id"    => 1
  "user_recorder_name"  => "SUPERADMIN"
  "observation"         => "GENERADO AL GRABAR PRODUCTO"
    "lstNoteDetail"     => "[{"product_id":7,
                            "product_name":"GASEOSA CONCORDIA",
                            "brand_name":"COCA COLA",
                            "category_name":"GASEOSAS",
                            "quantity":"121",
                            "warehouse_id":1}]
]
*/
    public function storeToStock(Request $request){

        try {

            $note_income                        =   new NoteIncome();
            $note_income->user_recorder_id      =   $request->get('user_recorder_id');
            $note_income->user_recorder_name    =   $request->get('user_recorder_name');
            $note_income->observation           =   $request->get('observation');
            $note_income->save();

            $lstNoteDetail  =   json_decode($request->get('lstNoteDetail'));
            foreach ($lstNoteDetail as $product) {
                $note_income_detail                 =   new NoteIncomeDetail();
                $note_income_detail->note_income_id =   $note_income->id;
                $note_income_detail->product_id     =   $product->product_id;
                $note_income_detail->product_name   =   $product->product_name;
                $note_income_detail->category_id    =   $product->category_id;
                $note_income_detail->category_name  =   $product->category_name;
                $note_income_detail->brand_id       =   $product->brand_id;
                $note_income_detail->brand_name     =   $product->brand_name;
                $note_income_detail->quantity       =   $product->quantity;
                $note_income_detail->warehouse_id   =   $product->warehouse_id;
                $note_income_detail->warehouse_name =   $product->warehouse_name;
                $note_income_detail->save();

                //====== INSERTANDO STOCK =====
                $warehouse_product                  =   new WarehouseProduct();
                $warehouse_product->warehouse_id    =   1;
                $warehouse_product->product_id      =   $product->product_id;
                $warehouse_product->stock           =   $product->quantity;
                $warehouse_product->save();

                //===== GRABANDO KARDEX ======
                $request_kardex     =   new Request();
                $request_kardex->merge([  'product_id'      =>  $product->product_id,
                                        'brand_id'          =>  $product->brand_id,
                                        'category_id'       =>  $product->category_id,
                                        'quantity'          =>  $product->quantity,
                                        'price_sale'        =>  null,
                                        'amount'            =>  null,
                                        'type'              =>  'INGRESO',
                                        'document'          =>  'NI-'.$note_income->id,
                                        'product_name'      =>  $product->product_name,
                                        'brand_name'        =>  $product->brand_name,
                                        'category_name'     =>  $product->category_name,
                                        'stock_previous'    =>  0,
                                        'stock_later'       =>  $product->quantity,
                                        'note_income_id'    =>  $note_income->id,
                                        'customer_id'           =>  null,
                                        'customer_name'         =>  null,
                                        'user_recorder_id'      =>  Auth::user()->id,
                                        'user_recorder_name'    =>  Auth::user()->name]);

                KardexController::store($request_kardex);
            }


            return (object)['success'=>true,'note_income_id'=>$note_income->id];

        } catch (Throwable $th) {

            return (object)['success'=>false,'message'=>$th->getMessage()];
        }
    }


    /*
    array:2 [ // app\Http\Controllers\Tenant\NoteIncomeController.php:186
        "lstNoteIncome"     => "[
                                {"product_id":9,"product_name":"AGUA SAN MATEO CON GAS","brand_name":"SAN MATEO","category_name":"AGUA","quantity":"12"},
                                {"product_id":8,"product_name":"PAPA LAYS","brand_name":"LAYS","category_name":"SNACKS","quantity":"23"}
                                ]"
        "user_recorder_id"  => "1"
        "observation"       => "y dale U"
    ]
    */
    public function store(Request $request){
        DB::beginTransaction();
        try {

            $data   =   $request->toArray();
            $this->s_note_income->store($data);

            DB::commit();

            Session::flash('note_income_success','NOTA DE INGRESO REGISTRADA!!');
            return response()->json(['success'=>true,'message'=>'NOTA DE INGRESO REGISTRADA!!!']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>$th->getMessage(),'line'=>$th->getLine(),'file'=>$th->getFile()]);
        }
    }

    public static function getStock($product_id){

        //======= VERIFICANDO SI EXISTE PRODUCTO EN EL ALMACÉN =======
        $warehouse_product          =   DB::select('select
                                        wp.stock
                                        from warehouse_products as wp
                                        where wp.warehouse_id = 1
                                        and wp.product_id = ?',[$product_id]);

        return $warehouse_product[0]->stock;
    }

    public static function validationLstNote($lstNoteIncome){

        foreach ($lstNoteIncome as $item) {

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

        return $lstNoteIncome;
    }

    public function show($id){

        try {
            $note_income    =   DB::select('select ni.*
                                from notes_income as ni
                                where ni.estado = "ACTIVO"
                                and ni.id = ?',[$id]);

            if(count($note_income) === 0){
                throw new Exception("NO EXISTA LA NOTA DE INGRESO EN LA BD!!!");
            }

            $note_income_detail =   DB::select('select nid.*
                                    from notes_income_detail as nid
                                    where nid.note_income_id = ?',[$id]);

            return response()->json(['success'=>true,
            'message'=>'OPERACIÓN COMPLETADA',
            'note_income'=>$note_income[0],'note_income_detail'=>$note_income_detail]);

        } catch (\Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }
    }


}
