<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilController;
use App\Http\Requests\Supplier\SupplierStoreRequest;
use App\Http\Requests\Supplier\SupplierUpdatedRequest;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index(){
        return view('purchases.supplier.index');
    }

    public function getSuppliers(Request $request){

        $suppliers  =    DB::table('suppliers as s')
                        ->join('types_identity_documents as tid','tid.id','s.type_identity_document_id')
                        ->select(
                            's.id', 
                            's.name',
                            's.address',
                            's.phone',
                            's.email',
                            's.document_number',
                            'tid.name as type_identity_document_name'
                        )
                        ->where('s.estado','!=','ANULADO')
                        ->get();

        return DataTables::of($suppliers)->make(true);    
    }

    public function create(){
        
        $type_identity_documents    =   DB::select('select * 
                                        from types_identity_documents as tid
                                        where 
                                        tid.id = "1"
                                        or tid.id = "3" ');

        return view('purchases.supplier.create',compact('type_identity_documents'));

    }

    
    public function consultarDocumento(Request $request){

        try {
            //========= VALIDANDO QUE EL TIPO DOCUMENTO Y N° DOCUMENTO NO SEAN NULL =======
            $tipo_documento =   $request->get('tipo_documento',null);
            $nro_documento  =   $request->get('nro_documento',null);

            if(!$tipo_documento){
                throw new Exception("EL TIPO DE DOCUMENTO ES OBLIGATORIO");
            }

            if(!$nro_documento){
                throw new Exception("EL N° DOC ES OBLIGATORIO");
            }

            if (!is_numeric($nro_documento)) {
                throw new Exception("EL N° DOCUMENTO DEBE SER NUMÉRICO");
            }

            //========= VERIFICANDO QUE EXISTA EL TIPO DOC EN LA BD ========
            $exists_tipo_doc    =   DB::select('select 
                                    tid.id,tid.name
                                    from types_identity_documents as tid
                                    where tid.id = ?',[$tipo_documento]);

            if(count($exists_tipo_doc) === 0){
                throw new Exception("EL TIPO DE DOC NO EXISTE EN LA BD");
            }

            if($tipo_documento != 1 && $tipo_documento != 3){
                throw new Exception("SOLO SE PUEDEN CONSULTAR DNI Y RUC");
            }

            if ( $tipo_documento == 1 && strlen($nro_documento) != 8) {
                throw new Exception("EL TIPO DE DOCUMENTO DNI DEBE TENER 8 DÍGITOS");
            }

            if ( $tipo_documento == 3 && strlen($nro_documento) != 11) {
                throw new Exception("EL TIPO DE DOCUMENTO RUC DEBE TENER 11 DÍGITOS");
            }


            //======= COMPROBAR QUE NO EXISTA EL DOCUMENTO EN LA TABLA supplierES =======
            $existe_nro_documento   =   DB::select('select 
                                        s.id,s.name
                                        from suppliers as s
                                        where 
                                        s.type_identity_document_id = ?
                                        and s.document_number = ? 
                                        and s.estado = "ACTIVO"',
                                        [$tipo_documento,$nro_documento]);

            if(count($existe_nro_documento) > 0){
                throw new Exception($exists_tipo_doc[0]->name.':'.$nro_documento.'.YA EXISTE EN LA BD');
            }
            
            if($tipo_documento == 1){

                $res_consulta_api   =   UtilController::apiDni($nro_documento);
                $res                =   $res_consulta_api->getData();

                //======= EN CASO LA CONSULTA FUE EXITOSA =====
                if($res->success){
                    return response()->json(['success'=>true,'data'=>$res->data,'message'=>'OPERACIÓN COMPLETADA']);
                }else{
                    throw new Exception($res->message);
                }
            }

            if($tipo_documento == 3){
                $res_consulta_api   =   UtilController::apiRuc($nro_documento);
                $res                =   $res_consulta_api->getData();

                //======= EN CASO LA CONSULTA FUE EXITOSA =====
                if($res->success){
                    return response()->json(['success'=>true,'data'=>$res->data,'message'=>'OPERACIÓN COMPLETADA']);
                }else{
                    throw new Exception($res->message);
                }
            }


        } catch (\Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }
    }


    /*
    array:7 [ // app\Http\Controllers\Tenant\SupplierController.php:138
        "_token"            => "z1cryQ02Q7fuWUHPIfGiwE2Eo5KqQneoE0d409iU"
        "tipo_documento"    => "1"
        "nro_documento"     => "41242141"
        "nombre"            => "LUIS DANIEL ALVA LUJAN"
        "direccion"         => "adasdsad"
        "telefono"          => "974585471"
        "correo"            => "a@gmail.com"
    ]
    */
    public function store(SupplierStoreRequest $request){
        DB::beginTransaction();
        try {

            $type_identity_document                 =   DB::select('select 
                                                        tid.name,tid.abbreviation,tid.code
                                                        from types_identity_documents as tid
                                                        where tid.id = ?',[$request->get('tipo_documento')])[0];

            $supplier                               =   new Supplier();

            $supplier->type_identity_document_id    =   $request->get('tipo_documento');
            $supplier->type_document_name           =   $type_identity_document->name;
            $supplier->type_document_abbreviation   =   $type_identity_document->abbreviation;
            $supplier->type_document_code           =   $type_identity_document->code;

            $supplier->document_number              =   $request->get('nro_documento');
            $supplier->name                         =   $request->get('nombre');
            $supplier->address                      =   $request->get('direccion');
            $supplier->phone                        =   $request->get('telefono');
            $supplier->email                        =   $request->get('correo');
            $supplier->save();

            DB::commit();

            return response()->json(['success' => true,'message'=>'PROVEEDOR REGISTRADO CON ÉXITO']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }
    }

    public function edit($id){

        $type_identity_documents    =   DB::select('select * 
                                        from types_identity_documents as tid
                                        where 
                                        tid.id = "1"
                                        or tid.id = "3" ');
        $supplier           =   Supplier::find($id);

        if(!$supplier){
            dd('EL PROVEEDOR NO EXISTE EN LA BD');
        }
        if($supplier->estado == "ANULADO"){
            dd('PROVEEDOR ANULADO');
        }

        return view('purchases.supplier.edit',
        compact('supplier','type_identity_documents'));
    }


/*
    array:11 [ // app\Http\Controllers\Compras\ProveedorController.php:122
        "_token"            => "vIEl6FeSyG6BGHQs3ipq4uWmeqymdP7JB4y5DFwc"
        "tipo_documento"    => "1"
        "nro_documento"     => "75608753"
        "nombre"            => "LUIS DANIEL ALVA LUJAN"
        "banco"             => "2"
        "nro_cuenta"        => "41241251251"
        "cci"               => "412412414342"
        "cuenta_detraccion" => "151251255414"
        "direccion"         => "av magnolias 321"
        "telefono"          => "974585471"
        "correo"            => "EVA@GMAIL.COM"
    ]
  */ 
  public function update($id,SupplierUpdatedRequest $request){
      
    DB::beginTransaction();
    try {

        $type_identity_document             =   DB::select('select 
                                                tid.name,tid.abbreviation,tid.code
                                                from types_identity_documents as tid
                                                where tid.id = ?',[$request->get('tipo_documento')])[0];

        $supplier                               =   Supplier::find($id);
        $supplier->type_identity_document_id    =   $request->get('tipo_documento');
        $supplier->type_document_name           =   $type_identity_document->name;
        $supplier->type_document_abbreviation   =   $type_identity_document->abbreviation;
        $supplier->type_document_code           =   $type_identity_document->code;

        $supplier->document_number              =   $request->get('nro_documento');
        $supplier->name                         =   $request->get('nombre');
        $supplier->address                      =   $request->get('direccion');
        $supplier->phone                        =   $request->get('telefono');
        $supplier->email                        =   $request->get('correo');
        $supplier->update();

        DB::commit();

        return response()->json(['success' => true,'message'=>'PROVEEDOR ACTUALIZADO CON ÉXITO']);
    } catch (\Throwable $th) {
        DB::rollBack();
        return response()->json(['success'=>false,'message'=>$th->getMessage()]);
    }
}

    public function destroy($id){
        DB::beginTransaction();
        try {
            $proveedor  =   Supplier::find($id);
            $proveedor->estado  =   'ANULADO';
            $proveedor->update();

            DB::commit();
            return response()->json(['success'=>true,'PROVEEDOR ELIMINADO']);
        } catch (\Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }
    }

    public function getLstSuppliers(){
        try {
            $suppliers  =   Supplier::where('estado','ACTIVO')->get();

            return response()->json(['success'=>true,'lstSuppliers'=>$suppliers,'message'=>'PROVEEDORES OBTENIDOS']);
        } catch (\Throwable $th) {
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }
    }

    
}
