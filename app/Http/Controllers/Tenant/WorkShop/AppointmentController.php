<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FormatController;
use App\Http\Controllers\UtilController;
use App\Http\Requests\Tenant\WorkShop\Appointment\AppointmentStoreRequest;
use App\Http\Requests\Tenant\WorkShop\Appointment\AppointmentUpdateRequest;
use App\Http\Services\Tenant\WorkShop\Appointments\AppointmentManager;
use App\Models\Tenant\WorkShop\Appointment\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AppointmentController extends Controller
{
    private AppointmentManager $s_manager;

    public function __construct()
    {
        $this->s_manager    =   new AppointmentManager();
    }

    public function index()
    {
        $customer_formatted         =   FormatController::getFormatInitialCustomer(1);

        return view('workshop.appointments.index', compact('customer_formatted'));
    }

    public function getEvents(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');

        $appointments = Appointment::where('status', 'ACTIVO')
            ->get()
            ->map(function ($item) {

                $vehicle    =   FormatController::getFormatInitialVehicle($item->vehicle_id);
                $customer   =   FormatController::getFormatInitialCustomer($item->customer_id);

                $item->vehicle  =   $vehicle;
                $item->customer =   $customer;
                $item->start_time = Carbon::parse($item->start_time)->format('H:i');
                $item->end_time   = Carbon::parse($item->end_time)->format('H:i');

                return [
                    'id' => (string) $item->id,
                    'calendarId' => $item->type_calendar ?? 'cal1',
                    'title' => $item->name,
                    'body' => $item->description,
                    'start' => Carbon::parse($item->start_date . ' ' . $item->start_time)->toIso8601String(),
                    'end' => Carbon::parse($item->end_date . ' ' . $item->end_time)->toIso8601String(),
                    'isAllDay' => (bool) $item->full_day,
                    'location' => $item->location,
                    'state' => $item->status,
                    'backgroundColor'   =>  $this->getBackgroundColor($item->type_calendar),

                    // ===== CLIENTE =====
                    'raw' => [
                        'item'  =>  $item,
                    ]
                ];
            });

        return response()->json($appointments);
    }

    /*
array:10 [ // app\Http\Controllers\Tenant\WorkShop\AppointmentController.php:81
  "name" => "COMER CHIFA"
  "customer_id" => "2"
  "vehicle_id" => "1"
  "type_calendar" => "TRABAJO"
  "start_date" => "2026-01-10"
  "start_time" => "12:00"
  "end_date" => "2026-01-10"
  "end_time" => "12:30"
  "location" => "TALLER"
  "description" => "TEST"
]
*/
    public function store(AppointmentStoreRequest $request)
    {
        DB::beginTransaction();
        try {

            $item  =   $this->s_manager->store($request->toArray());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CITA REGISTRADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    /*
array:12 [ // app\Http\Controllers\Tenant\WorkShop\AppointmentController.php:119
  "name" => "COMER CHIFA"
  "customer_id" => "2"
  "vehicle_id" => "2"
  "type_calendar" => "TRABAJO"
  "start_date" => "2026-01-10"
  "start_time" => "11:00:00"
  "end_date" => "2026-01-10"
  "end_time" => "11:30:00"
  "location" => "TALLER"
  "description" => "TEST edit"
  "_method" => "PUT"
  "id" => "7"
]
*/
    public function update(AppointmentUpdateRequest $request, int $id)
    {
        DB::beginTransaction();
        try {

            $item  =   $this->s_manager->update($request->toArray(), $id);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CITA ACTUALIZADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }



    private function getBackgroundColor($type)
    {
        return match ($type) {
            'cal1' => '#03bd9e',
            'cal2' => '#00a9ff',
            default => '#667eea',
        };
    }
}
