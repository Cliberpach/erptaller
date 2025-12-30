<?php

namespace App\Http\Controllers\Tenant\Queries;

use App\Exports\Tenant\Queries\QVehicles\QVehiclesExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Landlord\Customer;
use App\Models\Tenant\Accounts\CustomerAccount;
use App\Models\Tenant\Sale;
use App\Models\Tenant\WorkShop\Vehicle;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class QVehicleController extends Controller
{
    public function index()
    {
        return view('consultas.vehicles.index');
    }

    public function getList(Request $request)
    {
        $items  =   $this->queryQVehicles($request);
        return DataTables::of(
            DB::query()->fromSub($items, 't')
        )
            ->filterColumn('document_number', function ($query, $keyword) {
                $query->where('t.document_number', 'like', "%{$keyword}%");
            })
            ->orderColumn('document_number', function ($query, $order) {
                $query->orderBy('t.document_number', $order);
            })
            ->toJson();
    }

    public function queryQVehicles(Request $request)
    {
        $vehicle_id     =   $request->get('vehicle_id');
        $customer_id    =   $request->get('customer_id');
        $start_date     =   $request->get('start_date');
        $end_date       =   $request->get('end_date');

        $q1  =   CustomerAccount::from('customer_accounts as ca')
            ->leftJoin('work_orders as wo', 'wo.id', 'ca.work_order_id')
            ->leftJoin('sales_documents as sd', 'sd.id', 'ca.sale_id')
            ->select(
                DB::raw('IF(wo.id IS NULL,sd.id,wo.id) as document_id'),
                DB::raw('DATE_FORMAT(ca.created_at, "%Y-%m-%d %H:%i:%s") as date'),
                'ca.document_number',
                DB::raw('IF(wo.id IS NULL,sd.customer_name,wo.customer_name) as customer_name'),
                DB::raw('IF(wo.id IS NULL,sd.plate,wo.plate) as plate'),
                DB::raw('"CREDITO" as payment_condition'),
                'ca.amount',
                'ca.paid',
                'ca.balance'
            );

        $q2 = Sale::from('sales_documents as sd')
            ->where('sd.payment_condition_name', "CONTADO") //======== CONTADO ========
            ->select(
                'sd.id as document_id',
                DB::raw('DATE_FORMAT(sd.created_at, "%Y-%m-%d %H:%i:%s") as date'),
                DB::raw('CONCAT(sd.serie,"-",sd.correlative) as document_number'),
                'sd.customer_name as customer_name',
                'sd.plate as plate',
                'sd.payment_condition_name as payment_condition',
                'sd.total as amount',
                DB::raw('IF(sd.payment_status = "PAGADO", sd.total,0) as paid'),
                DB::raw('IF(sd.payment_status = "PAGADO", 0,sd.total) as balance')
            );

        if ($start_date) {
            $q1->whereDate('ca.created_at', '>=', $start_date);
            $q2->whereDate('sd.created_at', '>=', $start_date);
        }
        if ($end_date) {
            $q1->whereDate('ca.created_at', '<=', $end_date);
            $q2->whereDate('sd.created_at', '<=', $end_date);
        }

        if ($customer_id) {

            $q1->when($customer_id, function ($query) use ($customer_id) {
                $query->where(function ($q) use ($customer_id) {
                    $q->whereNotNull('wo.id')
                        ->where('wo.customer_id', $customer_id)
                        ->orWhere(function ($q2) use ($customer_id) {
                            $q2->whereNotNull('sd.id')
                                ->where('sd.customer_id', $customer_id);
                        });
                });
            });

            $q2->where('sd.customer_id', $customer_id);
        }

        if ($vehicle_id) {

            $q1->when($vehicle_id, function ($query) use ($vehicle_id) {
                $query->where(function ($q) use ($vehicle_id) {
                    $q->whereNotNull('wo.id')
                        ->where('wo.vehicle_id', $vehicle_id)
                        ->orWhere(function ($q2) use ($vehicle_id) {
                            $q2->whereNotNull('sd.id')
                                ->where('sd.vehicle_id', $vehicle_id);
                        });
                });
            });

            $q2->where('sd.vehicle_id', $vehicle_id);
        }

        $items = $q1->unionAll($q2);

        return $items;
    }

    public function getExcel(Request $request)
    {

        $company    =   Company::find(1);
        $data       =   $this->queryQVehicles($request)->get();

        if ($request->get('customer_id')) {
            $customer   =   Customer::findOrFail($request->get('customer_id'));
            $request->merge(['customer_name' => $customer->type_document_abbreviation . ":" . $customer->document_number . "-" . $customer->name]);
        }
        if ($request->get('vehicle_id')) {
            $vehicle   =   Vehicle::findOrFail($request->get('vehicle_id'));
            $request->merge(['plate' => $vehicle->plate]);
        }

        return Excel::download(new QVehiclesExport($data, $request, $company), 'qvehicles_' . Carbon::now()->format('Y-m-d') . '.xlsx');
    }

    public function getPdf(Request $request)
    {

        $company    =   Company::find(1);
        $data       =   $this->queryQVehicles($request)->get();

        if ($request->get('customer_id')) {
            $customer   =   Customer::findOrFail($request->get('customer_id'));
            $request->merge(['customer_name' => $customer->type_document_abbreviation . ":" . $customer->document_number . "-" . $customer->name]);
        }
        if ($request->get('vehicle_id')) {
            $vehicle   =   Vehicle::findOrFail($request->get('vehicle_id'));
            $request->merge(['plate' => $vehicle->plate]);
        }

        $pdf = Pdf::loadview('consultas.vehicles.pdf.pdf', [
            'company'   => $company,
            'data'      => $data,
            'filters'   => $request,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('qvehicles' . Carbon::now()->format('Y_m_d_H_i_s') . '.pdf');
    }
}
