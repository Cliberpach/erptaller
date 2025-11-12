<?php

namespace App\Http\Controllers\Tenant\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\PaymentMethod\PaymentMethodStoreRequest;
use App\Http\Requests\Tenant\PaymentMethod\PaymentMethodUpdateRequest;
use App\Models\Tenant\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PaymentMethodController extends Controller
{
    public function index(){
        return view('sales.payment_method.index');
    }

    public function getPaymentMethods(Request $request){

        $payment_methods    =   DB::table('payment_methods as p')
                                ->select(
                                    'p.*'
                                )
                                ->where('p.estado','!=','ANULADO')
                                ->get();


        return DataTables::of($payment_methods)->make(true);

    }

    public function store(PaymentMethodStoreRequest $request){
        DB::beginTransaction();
        try {

            $payment_method                 =   new PaymentMethod();
            $payment_method->description    =   $request->get('descripcion');
            $payment_method->save();

            DB::commit();
            return response()->json(['success'=>true,'message'=>'MÉTODO DE PAGO REGISTRADO']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }
    }

    public function update(PaymentMethodUpdateRequest $request,$id){
        DB::beginTransaction();
        try {

            $payment_method                 =   PaymentMethod::find($id);
            $payment_method->description    =   $request->get('descripcion_edit');
            $payment_method->update();

            DB::commit();
            return response()->json(['success'=>true,'message'=>'MÉTODO DE PAGO ACTUALIZADO']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>$th->getMessage()]);
        }
    }
}
