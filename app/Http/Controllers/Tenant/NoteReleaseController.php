<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Tenant\NoteRelease;
use App\Models\Tenant\NoteReleaseDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class NoteReleaseController extends Controller
{
    public function index()
    {
        return view('inventory.note_release.index');
    }

    public function getNotesRelease(Request $request){

        $notes_release    =   DB::table('notes_release as nr')
                            ->select(
                              'nr.id',
                              'nr.created_at',
                              'nr.user_recorder_name',
                              'nr.observation',
                            )
                            ->where('nr.estado','!=','ANULADO')
                            ->orderByDesc('nr.created_at');

        if($request->get('product_id')){
            $notes_release =   $notes_release->where('k.product_id',$request->get('product_id'));
        }

        if($request->get('date_start')){
            $notes_release = $notes_release->whereRaw('DATE(k.created_at) >= ?', [$request->get('date_start')]);
        }
    
        if($request->get('date_end')){
            $notes_release = $notes_release->whereRaw('DATE(k.created_at) <= ?', [$request->get('date_end')]);
        }

        $notes_release =   $notes_release->get();

        return DataTables::of($notes_release)->make(true);
    
    }


    public function create(){
        $categories                 =   Category::all();
        $brands                     =   Brand::all();
        $colaborador_registrador    =   DB::select('select 
                                        *
                                        from 
                                        users as u
                                        where u.id = ?',[Auth::user()->id])[0];

        return view('inventory.note_release.create',compact('categories','brands','colaborador_registrador'));
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

    public function validateStock($product_id,$quantity){

        try {
            $warehouse_product  =   DB::select('select 
                                    wp.* 
                                    from warehouse_products as wp
                                    where wp.warehouse_id = ?
                                    and wp.product_id = ?',[1,$product_id]);

            if(count($warehouse_product) === 0){
                throw new Exception("EL PRODUCTO NO EXISTE EN LA BD!!!");
            }

            if($quantity > $warehouse_product[0]->stock){
                throw new Exception("LA CANTIDAD A RETIRAR (".$quantity.") , ES MAYOR AL STOCK (".
                $warehouse_product[0]->stock.")!!!");
            }

            return response()->json(['success'=>true,'message'=>"CANTIDAD VÁLIDA",'current_stock'=>$warehouse_product[0]->stock]);

        } catch (\Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage(),'current_stock'=>$warehouse_product[0]->stock]);
        }
       
    }


    /*
    array:4 [ // app\Http\Controllers\Tenant\NoteReleaseController.php:103
        "lstNoteRelease"        => "[{"product_id":4,"product_name":"AGUA SAN MATEO CON GAS","brand_name":"SAN MATEO","category_name":"AGUA","quantity":"2"},{"product_id":3,"product_name":"PAPA LAYS","brand_name":"LAYS","category_name":"SNACKS","quantity":"14"}]"
        "user_recorder_id"      => "1"
        "user_recorder_name"    => "SUPERADMIN"
        "observation"           => "nota salida"
    ]
    */
    public function store(Request $request){
        DB::beginTransaction();
        try {

            $lstNoteRelease  =   json_decode($request->get('lstNoteRelease'));   
            $lstNoteRelease  =   $this->validationLstNoteRelease($lstNoteRelease);

            $warehouse                          =   DB::select('select * 
                                                    from warehouses as w
                                                    where w.id = 1')[0];


            $note_release                        =   new NoteRelease();   
            $note_release->user_recorder_id      =   $request->get('user_recorder_id');
            $note_release->user_recorder_name    =   $request->get('user_recorder_name');
            $note_release->observation           =   $request->get('observation');
            $note_release->save();

            foreach ($lstNoteRelease as  $item) {

                $note_detail                    =   new NoteReleaseDetail();
                $note_detail->note_release_id   =   $note_release->id; 
                $note_detail->product_id        =   $item->product_id;
                $note_detail->brand_id          =   $item->brand_id;
                $note_detail->category_id       =   $item->category_id;
                $note_detail->product_name      =   $item->product_name;
                $note_detail->brand_name        =   $item->brand_name;
                $note_detail->category_name     =   $item->category_name;
                $note_detail->warehouse_id      =   $warehouse->id;
                $note_detail->warehouse_name    =   $warehouse->descripcion;
                $note_detail->quantity          =   $item->quantity;
                $note_detail->save();

                //======= OBTENIENDO STOCK PREVIO =====
                $stock_previous                     =   UtilController::getStock($item->product_id);

                //====== RESTANDO STOCK =====
                DB::update('UPDATE warehouse_products 
                SET updated_at = ?, stock = stock - ? 
                WHERE warehouse_id = 1 
                and product_id = ?', [Carbon::now() , $item->quantity , $item->product_id]);

                $stock_later                        =   UtilController::getStock($item->product_id);

                //===== GRABANDO EN KARDEX ========
                $request_kardex     =   new Request();
                $request_kardex->merge([  'product_id'      =>  $item->product_id,
                                        'brand_id'          =>  $item->brand_id,
                                        'category_id'       =>  $item->category_id,
                                        'quantity'          =>  $item->quantity,
                                        'price_sale'        =>  null,
                                        'amount'            =>  null,
                                        'type'              =>  'SALIDA',
                                        'document'          =>  'NS-'.$note_release->id,
                                        'product_name'      =>  $item->product_name,
                                        'brand_name'        =>  $item->brand_name,
                                        'category_name'     =>  $item->category_name,
                                        'stock_previous'    =>  $stock_previous,
                                        'stock_later'       =>  $stock_later,
                                        'note_release_id'   =>  $note_release->id,
                                        'customer_id'           =>  null,
                                        'customer_name'         =>  null,
                                        'user_recorder_id'      =>  $note_release->user_recorder_id,
                                        'user_recorder_name'    =>  $note_release->user_recorder_name]);

                KardexController::store($request_kardex);
            }

            DB::commit();

            Session::flash('note_release_success','NOTA DE SALIDA REGISTRADA!!');
            return response()->json(['success'=>true,'message'=>'NOTA DE SALIDA REGISTRADA!!!']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>$th->getMessage(),'line'=>$th->getLine()]);
        }
    }

    public static function validationLstNoteRelease($lstNoteRelease){

        foreach ($lstNoteRelease as $item) {

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

            //========== VALIDANDO STOCK =======
            $warehouse_product      =   DB::select('select 
                                        wp.stock
                                        from warehouse_products as wp
                                        where wp.warehouse_id = ?
                                        and wp.product_id = ?',[1,$item->product_id]);

            if(count($warehouse_product) === 0){
                throw new Exception("PRODUCTO ".$item->product_name."NO EXISTE EN EL ALMACÉN");
            }

            if($item->quantity > $warehouse_product[0]->stock){
                throw new Exception("PRODUCTO: ".$item->product_name.", CANTIDAD(".$item->quantity.") ES MAYOR AL STOCK(".$warehouse_product[0]->stock.")");
            }

            $item->product_name     =   $product_exists[0]->name;
            $item->brand_name       =   $product_exists[0]->brand_name;
            $item->category_name    =   $product_exists[0]->category_name;
            $item->brand_id         =   $product_exists[0]->brand_id;
            $item->category_id      =   $product_exists[0]->category_id;

        }

        return $lstNoteRelease;
    }


    public function show($id){
        
        try {
            $note_release   =   DB::select('select nr.* 
                                from notes_release as nr
                                where nr.estado = "ACTIVO"
                                and nr.id = ?',[$id]);

            if(count($note_release) === 0){
                throw new Exception("NO EXISTE LA NOTA DE SALIDA EN LA BD!!!");
            }
            
            $note_release_detail =   DB::select('select nrd.*
                                    from notes_release_detail as nrd
                                    where nrd.note_release_id = ?',[$id]);

            return response()->json(['success'=>true,
            'message'=>'OPERACIÓN COMPLETADA',
            'note_release'=>$note_release[0],'note_release_detail'=>$note_release_detail]);
            
        } catch (\Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }
        
    }

}
