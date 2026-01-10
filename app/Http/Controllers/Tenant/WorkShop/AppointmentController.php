<?php

namespace App\Http\Controllers\Tenant\WorkShop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\WorkShop\Appointment\AppointmentStoreRequest;
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
        return view('workshop.appointments.index');
    }

    public function getEvents(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');

        $appointments = Appointment::where('status', 'ACTIVO')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => (string) $appointment->id,
                    'calendarId' => $appointment->type_calendar ?? 'cal1',
                    'title' => $appointment->name,
                    'body' => $appointment->description,
                    'start' => Carbon::parse($appointment->start_date . ' ' . $appointment->start_time)->toIso8601String(),
                    'end' => Carbon::parse($appointment->end_date . ' ' . $appointment->end_time)->toIso8601String(),
                    'isAllDay' => (bool) $appointment->full_day,
                    'location' => $appointment->location,
                    'state' => $appointment->status,
                    'backgroundColor' => $this->getBackgroundColor($appointment->type_calendar),
                ];
            });

        return response()->json($appointments);
    }

    /*
array:8 [ // app\Http\Services\Tenant\WorkShop\Appointments\AppointmentService.php:23
  "name_event" => "TAREA 1"
  "type_calendar_event" => "cal2"
  "start_date_event" => "2026-01-09"
  "start_time_event" => "17:51"
  "end_date_event" => "2026-01-09"
  "end_time_event" => "18:51"
  "location_event" => "TEST"
  "description_event" => "TEST"
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

    private function getBackgroundColor($type)
    {
        return match ($type) {
            'cal1' => '#03bd9e',
            'cal2' => '#00a9ff',
            default => '#667eea',
        };
    }
}
