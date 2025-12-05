<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LandLord\ApiController;
use App\Http\Requests\Customer\CustomerStoreRequest;
use App\Http\Requests\CustomerRequest;
use App\Models\Landlord\Customer;
use App\Models\Landlord\TypeIdentityDocument;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Models\Tenant;
use Throwable;

class CustomerController extends Controller
{
    //
    public function index()
    {
        $customersList = DB::connection('landlord')->select('select * from customers');
        $customer = Customer::all();
        return view('customer.index', compact('customersList', 'customer'));
    }
    public function create()
    {
        return view('customer.create-customer-modal');
    }

    /*
    array:10 [ // app\Http\Controllers\Tenant\CustomerController.php:28
        "_token"                    => "xcicwEW2N8GRpMSruclohEPesCudo6RslC5Hd7pz"
        "type_identity_document"    => "1"
        "nro_document"              => "75608753"
        "name"                      => "luis daniel alva luján"
        "address"                   => "AV PERU 123"
        "department"                => "02"
        "province"                  => "0203"
        "district"                  => "020301"
        "phone"                     => "965345124"
        "email"                     => "ld@gmail.com"
    ]
    */

    /*
        TIPOS DE DOCUMENTO IDENTIDAD SEGÚN SUNAT:
        01 - DNI
        04 - CARNET EXTRANJERÍA
        06 - RUC
        07 - PASAPORTE
        11 - PARTIDA NACIMIENTO
        00 - OTROS
    */
    public function store(CustomerStoreRequest $request)
    {
        DB::beginTransaction();

        try {

            $customer                               =   new Customer();
            $customer->document_number              =   mb_strtoupper($request->get('nro_document'), 'UTF-8');
            $customer->name                         =   mb_strtoupper($request->get('name'), 'UTF-8');
            $customer->phone                        =   $request->get('phone');

            //======== GRABANDO EL TIPO DE DOCUMENTO DE IDENTIDAD ========
            $type_identity_document                 =   TypeIdentityDocument::findOrFail($request->get('type_identity_document'));

            $customer->type_identity_document_id    =   $request->get('type_identity_document');
            $customer->type_document_name           =   $type_identity_document->name;
            $customer->type_document_abbreviation   =   $type_identity_document->abbreviation;
            $customer->type_document_code           =   $type_identity_document->code;

            $customer->address                      =   mb_strtoupper($request->get('address'), 'UTF-8');
            $customer->email                        =   mb_strtoupper($request->get('email'), 'UTF-8');

            $customer->department_id                =   $request->get('department');
            $customer->province_id                  =   $request->get('province');
            $customer->district_id                  =   $request->get('district');

            $department         =   DB::select(
                'select
                                    d.name,
                                    d.zone
                                    from departments as d
                                    where d.id = ?',
                [$request->get('department')]
            )[0];

            $customer->department_name  =   $department->name;

            $customer->province_name    =   DB::select(
                'select
                                            p.name
                                            from provinces as p
                                            where p.id = ?',
                [$request->get('province')]
            )[0]->name;

            $customer->district_name    =   DB::select(
                'select
                                            d.name
                                            from districts as d
                                            where d.id = ?',
                [$request->get('district')]
            )[0]->name;

            $customer->zone             =   $department->zone;
            $customer->ubigeo           =   $request->get('district');
            $customer->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CLIENTE REGISTRADO!!!', 'customer' => $customer]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customer.update-customer-modal', compact('customer'));
    }

    public function update(CustomerRequest $request, $id)
    {
        $request->validated();

        $customer = Customer::findOrFail($id);
        $customer->document_number = $request->document_number;
        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->save();

        return redirect()->route('customers.index')->with('datos', 'Cliente actualizado');
    }

    public function destroy($id)
    {
        // Encuentra el cliente
        $customer = Customer::findOrFail($id);

        // Elimina todas las reservas asociadas a este cliente
        $customer->bookings()->each(function ($booking) {

            $booking->bookingDetails()->delete();

            $booking->delete();
        });

        //eliminar el cliente
        $customer->delete();

        return back()->with('datos', 'Cliente eliminado');
    }

    //========== CONSULTAR DOCUMENTO ==========
    public function consult_document(Request $request)
    {
        try {
            //========= VALIDANDO QUE EL TIPO DOCUMENTO Y N° DOCUMENTO NO SEAN NULL =======
            $type_identity_document =   $request->get('type_identity_document', null);
            $nro_document           =   $request->get('nro_document', null);

            if (!$type_identity_document) {
                throw new Exception("EL TIPO DE DOCUMENTO ES OBLIGATORIO");
            }

            if (!$nro_document) {
                throw new Exception("EL N° DOC ES OBLIGATORIO");
            }

            if (!is_numeric($nro_document)) {
                throw new Exception("EL N° DOCUMENTO DEBE SER NUMÉRICO");
            }

            //========= VERIFICANDO QUE EXISTA EL TIPO DOC EN LA BD ========
            $exists_tipo_doc    =   TypeIdentityDocument::findOrFail($type_identity_document);

            if (!$exists_tipo_doc) {
                throw new Exception("EL TIPO DE DOC NO EXISTE EN LA BD");
            }

            if ($type_identity_document != 1 && $type_identity_document != 3) {
                throw new Exception("SOLO SE PUEDEN CONSULTAR DNI Y RUC");
            }

            if ($type_identity_document == 1 && strlen($nro_document) != 8) {
                throw new Exception("EL TIPO DE DOCUMENTO DNI DEBE TENER 8 DÍGITOS");
            }

            if ($type_identity_document == 3 && strlen($nro_document) != 11) {
                throw new Exception("EL TIPO DE DOCUMENTO RUC DEBE TENER 11 DÍGITOS");
            }


            //======= COMPROBAR QUE NO EXISTA EL DOCUMENTO EN LA TABLA CLIENTES =======
            $exists_nro_document   =   DB::connection('landlord')->select(
                'select
                                        c.id,
                                        c.name
                                        from customers as c
                                        where
                                        c.type_identity_document_id = ?
                                        and c.document_number = ?
                                        and c.status = "ACTIVO"',
                [$type_identity_document, $nro_document]
            );

            if (count($exists_nro_document) > 0) {
                throw new Exception($exists_nro_document[0]->name . ':' . $nro_document . '. YA EXISTE EN LA BD');
            }

            if ($type_identity_document == 1) {

                $api_controller     =   new ApiController();
                $res_consult_api    =   $api_controller->apiDni($nro_document);
                $res_consult_api    =   json_decode($res_consult_api);

                //======= EN CASO LA CONSULTA FUE EXITOSA =====
                if ($res_consult_api->success) {
                    return response()->json(['success' => true, 'data' => $res_consult_api->data, 'message' => 'OPERACIÓN COMPLETADA']);
                } else {
                    throw new Exception($res_consult_api->message);
                }
            }

            if ($type_identity_document == 3) {
                
                $api_controller     =   new ApiController();
                $res_consult_api    =   $api_controller->apiRuc($nro_document);
                $res_consult_api    =   json_decode($res_consult_api);

                //======= EN CASO LA CONSULTA FUE EXITOSA =====
                if ($res_consult_api->success) {
                    return response()->json(['success' => true, 'data' => $res_consult_api->data, 'message' => 'OPERACIÓN COMPLETADA']);
                } else {
                    throw new Exception($res_consult_api->message);
                }
            }
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }


    public function getListCustomers()
    {
        try {
            $listCustomers  =   Customer::where('status', 'ACTIVO')->get();

            return response()->json(['success' => true, 'listCustomers' => $listCustomers]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }


    /**
     * Buscar clientes (para TomSelect server-side)
     */
    public function searchCustomer(Request $request)
    {
        try {

            $query = trim($request->get('q', ''));
            $vehicle_id = $request->get('vehicle_id', null);

            $customers = DB::table('erptaller.customers as c');

            if ($query) {
                $customers->whereRaw("CONCAT(type_document_abbreviation, ':', document_number, ' - ', name) LIKE ?", ["%{$query}%"])
                    ->orWhereRaw("CONCAT(document_number, ' - ', name) LIKE ?", ["%{$query}%"])
                    ->orWhere('name', 'LIKE', "%{$query}%");
            }

            if ($vehicle_id) {
                $currentTenant = Tenant::current()->database;
                $customers->join(DB::raw("{$currentTenant}.vehicles as v"), 'v.customer_id', '=', 'c.id')
                    ->where('v.id', $vehicle_id);
            }

            $results = $customers->limit(20)->get([
                'c.id',
                'c.type_document_abbreviation',
                'c.document_number',
                'c.name',
                'c.email'
            ]);

            $data = $results->map(fn($c) => [
                'id' => $c->id,
                'full_name' => "{$c->type_document_abbreviation}:{$c->document_number} - {$c->name}",
                'email' => $c->email,
            ]);

            return response()->json(['success' => true, 'message' => 'CLIENTES OBTENIDOS', 'data' => $data]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
