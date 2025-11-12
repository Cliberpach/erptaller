<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Booking\BookStoreRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\ReservationResource;
use App\Http\Services\Tenant\Bookings\Booking\BookingManager;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Company;
use App\Models\Landlord\Customer;
use App\Models\Field;
use App\Models\Schedule;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Credit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Throwable;

class BookController extends Controller
{
    private BookingManager $s_booking;

    public function __construct(){
        $this->s_booking    =   new BookingManager();
    }

    public function showPDF($id)
    {
        // Obtener los datos de la reserva, incluyendo detalles
        $reservation            = Booking::findOrFail($id);
        $reservation_detail     = BookingDetail::where('booking_id', $reservation->id)->get();
        $company                = Company::first();


        // $pdf = PDF::loadView('booking.recibo', compact('reservation', 'reservation_detail', 'company'))
        //            ->setPaper([0, 0, 226.77, 841.89], 'portrait');

        $pdf    =   PDF::loadview('booking.pdf.recibo', [
            'company'               =>  $company,
            'reservation'           =>  $reservation,
            'reservation_detail'    =>  $reservation_detail
        ])->setPaper([0, 0, 226.772, 651.95]);


        return $pdf->stream('R-' . str_pad($reservation->id, 8, '0', STR_PAD_LEFT) . '_' . $reservation->customer->document_number . '_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }


    public function book(Request $request)
{
    Carbon::setLocale('es');

    $today = $request->date != null ? $request->date : now()->format('Y-m-d');
    $currentTime = now()->format('H:i');

    $schedules = Schedule::where('active', true)->orderBy('description', 'asc')->get();

    $bookings = Booking::from('bookings as b')
        ->join('schedules as s', 's.id', '=', 'b.schedule_id')
        ->select('b.*', 's.start_time', 's.end_time', 'b.nro_hours', 'b.is_credit')
        ->selectRaw('DATE_ADD(s.start_time, INTERVAL (b.nro_hours * 60) MINUTE) as new_end_time')
        ->where('b.date', $today)
        ->get();

    $bookingDetail  = BookingDetail::all();
    $fields         = Field::where('isDeleted', false)->get();
    $hourNight      = Configuration::find(1)->property;
    $isNight        = $currentTime >= $hourNight;

    // Calcular precios correctos por reserva según hora
    foreach ($bookings as $booking) {
        $field = $fields->firstWhere('id', $booking->field_id);

        if (!$field) {
            continue; // saltamos bookings cuyo campo ya fue eliminado
        }

        $start = Carbon::createFromFormat('H:i:s', $booking->start_time);
        $turnoNoche = Carbon::createFromFormat('H:i', $hourNight);

        $booking->price_per_hour = $start->gte($turnoNoche)
            ? (float) $field->night_price
            : (float) $field->day_price;
    }


    foreach ($fields as $field) {
        $field->day_price = number_format($field->day_price, 2, '.', '');
        $field->night_price = number_format($field->night_price, 2, '.', '');
    }

    $company = Company::first();

    return view(
        'booking.index',
        compact(
            'today',
            'currentTime',
            'bookings',
            'fields',
            'schedules',
            'bookingDetail',
            'isNight',
            'hourNight',
            'company'
        )
    );
}

    public function generatePDF(Request $request)
    {
        Carbon::setLocale('es');
        $today = $request->query('date', now()->format('Y-m-d'));

        $schedules = Schedule::where('active', true)->orderBy('start_time', 'asc')->get();

        $bookings = Booking::from('bookings as b')
            ->join('schedules as s', 's.id', '=', 'b.schedule_id')
            ->select('b.*', 's.start_time', 's.end_time', 'b.nro_hours', 'b.is_credit')
            ->selectRaw('DATE_ADD(s.start_time, INTERVAL (b.nro_hours * 60) MINUTE) as new_end_time')
            ->where('b.date', $today)
            ->get();

        $bookingDetail = BookingDetail::all();

        $fields = Field::where('isDeleted', false)->get();

        $hourNight = Configuration::find(1)->property;

        $company = Company::first();

        // Calcular precios correctos por reserva según hora
        foreach ($bookings as $booking) {
            $field = $fields->firstWhere('id', $booking->field_id);

            if (!$field) {
                continue;
            }

            $start = Carbon::createFromFormat('H:i:s', $booking->start_time);
            $turnoNoche = Carbon::createFromFormat('H:i', $hourNight);

            $booking->price_per_hour = $start->gte($turnoNoche)
                ? (float) $field->night_price
                : (float) $field->day_price;
        }


        $pdf = PDF::loadView('booking.pdf.reservas', compact(
            'today',
            'bookings',
            'fields',
            'schedules',
            'bookingDetail',
            'hourNight',
            'company'
        ));

        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $font = $dompdf->getFontMetrics()->get_font("helvetica", "bold");
        $dompdf->get_canvas()->page_text(530, 800, "{PAGE_NUM} / {PAGE_COUNT}", $font, 10, array(0, 0, 0));

        return $pdf->stream('reservas_' . $today . '.pdf');
    }



/*
array:13 [ // app\Http\Controllers\Tenant\BookController.php:188
  "document_number" => "99999999"
  "name" => "GISELL CRUZ ULLOA"
  "phone" => "912312111"
  "field_id" => "1"
  "schedule_id" => "25"
  "date" => "2025-07-10"
  "payment_type" => "EFECTIVO"
  "payment" => "10.00"
  "nro_hours" => "1"
  "modality" => "7v7"
  "ruc_number" => "12345678901"
  "razon_social" => "null"
  "credit" => "0"
  "juntar_con_ids" => "2"
]
*/
    public function store(BookStoreRequest $request)
    {
        DB::beginTransaction();
        try {

            $res    =   $this->s_booking->store($request->toArray());
            dd($res);
            //DB::commit();

            return response()->json($res, 200);
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('Error al crear la reserva: ' . $th->getMessage());
            return response()->json([
                'message' => 'Error al crear la reserva',
                'error' => $th->getMessage(),
                'line' => $th->getLine()
            ], 500);
        }
    }

/*
public function update(Request $request, $id)
{
    $request->validate([
        'field_id' => 'exists:fields,id',
        'schedule_id' => 'exists:schedules,id',
        'document_number' => 'string|size:8',
        'name' => 'string|max:255',
        'phone' => 'string|max:20',
        'payment' => 'numeric|min:0',
        'payment_type' => 'required|string',
        'voucher' => 'nullable|file|mimes:jpg,jpeg,png,pdf|required_unless:payment_type,EFECTIVO',
        'date' => 'date',
    ]);

    try {
        // Encuentra la reserva por id
        $booking = Booking::findOrFail($id);

        // Actualizar campos de la reserva
        $booking->field_id = $request->input('field_id', $booking->field_id);
        $booking->schedule_id = $request->input('schedule_id', $booking->schedule_id);
        $booking->date = $request->input('date', $booking->date);

        // Verificar si hay datos de cliente para actualizar o crear
        if ($request->has('document_number') && $request->has('name') && $request->has('phone')) {
            $customer = Customer::firstOrCreate(
                ['document_number' => $request->input('document_number')],
                ['name' => $request->input('name'), 'phone' => $request->input('phone')]
            );
            $booking->customer_id = $customer->id;
        }

        // Actualizar estado de pago y reserva
        $booking->payment_status = 'TOTAL';
        $booking->status = 'ALQUILADO';

        DB::beginTransaction(); // Inicia la transacción

        $booking->save(); // Guarda los cambios en la reserva

        // Buscar el detalle de la reserva
        $bookingDetail = BookingDetail::where('booking_id', $booking->id)->first();
        if ($bookingDetail) {
            $bookingDetail->payment = $request->input('payment', $bookingDetail->payment);
            $bookingDetail->payment_type = $request->input('payment_type', $bookingDetail->payment_type);

            // Solo guardar el voucher si el tipo de pago no es EFECTIVO
            if ($request->input('payment_type') !== 'EFECTIVO' && $request->hasFile('voucher')) {
                $voucherFile = $request->file('voucher');
                $voucherFileName = time() . '_' . $voucherFile->getClientOriginalName();
                $voucherPath = $voucherFile->storeAs('vouchers', $voucherFileName, 'public');
                $bookingDetail->voucher = $voucherPath;
            }

            $bookingDetail->save(); // Guarda el detalle de la reserva
        }

        DB::commit(); // Confirmar la transacción

        return new ReservationResource($booking); // Responder con los datos actualizados
    } catch (\Exception $e) {
        DB::rollBack(); // Revertir en caso de error
        return response()->json(['message' => 'Error al actualizar la reserva', 'error' => $e->getMessage()], 500);
    }
}
*/


    public function show($id)
    {
        $reservation = Booking::with(['field', 'schedule', 'customer'])
            ->where('id', $id)
            ->first();

        $details = BookingDetail::where('booking_id', $id)->first();
        $payment = BookingDetail::where('booking_id', $id)->sum('payment');

        if (!$reservation) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        return response()->json([
            'field'             => $reservation->field,
            'schedule'          => $reservation->schedule,
            'date'              => $reservation->date,
            'modality'          => $reservation->modality,
            'document_number'   => $reservation->customer->document_number,
            'name'              => $reservation->customer->name,
            'phone'             => $reservation->customer->phone,
            'total'             => $reservation->total,
            'payment_type'      => $reservation->payment_type,
            'payment'           => $payment,
            'voucher'           => $reservation->voucher,
            'is_credit'         => $reservation->is_credit
        ]);
    }


    public function schedule()
    {
        $horas_1 = [
            "00:00 - 00:30 am",
            "00:30 - 01:00 am",
            "01:00 - 01:30 am",
            "01:30 - 02:00 am",
            "02:00 - 02:30 am",
            "02:30 - 03:00 am",
            "03:00 - 03:30 am",
            "03:30 - 04:00 am",
            "04:00 - 04:30 am",
            "04:30 - 05:00 am",
            "05:00 - 05:30 am",
            "05:30 - 06:00 am",
            "06:00 - 06:30 am",
            "06:30 - 07:00 am",
            "07:00 - 07:30 am",
            "07:30 - 08:00 am"
        ];

        $horas_2 = [
            "08:00 - 08:30 am",
            "08:30 - 09:00 am",
            "09:00 - 09:30 am",
            "09:30 - 10:00 am",
            "10:00 - 10:30 am",
            "10:30 - 11:00 am",
            "11:00 - 11:30 am",
            "11:30 - 12:00 pm",
            "12:00 - 12:30 pm",
            "12:30 - 13:00 pm",
            "13:00 - 13:30 pm",
            "13:30 - 14:00 pm",
            "14:00 - 14:30 pm",
            "14:30 - 15:00 pm",
            "15:00 - 15:30 pm",
            "15:30 - 16:00 pm"
        ];

        $horas_3 = [
            "16:00 - 16:30 pm",
            "16:30 - 17:00 pm",
            "17:00 - 17:30 pm",
            "17:30 - 18:00 pm",
            "18:00 - 18:30 pm",
            "18:30 - 19:00 pm",
            "19:00 - 19:30 pm",
            "19:30 - 20:00 pm",
            "20:00 - 20:30 pm",
            "20:30 - 21:00 pm",
            "21:00 - 21:30 pm",
            "21:30 - 22:00 pm",
            "22:00 - 22:30 pm",
            "22:30 - 23:00 pm",
            "23:00 - 23:30 pm",
            "23:30 - 00:00 am"
        ];


        //==== Filtrar solo los horarios activos ====
        $schedules = Schedule::where('active', true)->get();

        return view('booking.schedule', compact('horas_1', 'horas_2', 'horas_3', 'schedules'));
    }

    public function saveSchedule(Request $request)
    {
        // Obtener los horarios enviados en la solicitud
        $newSchedules = $request->schedules;

        // Si no se enviaron horarios o es nulo, desactivar todos
        if (!is_array($newSchedules) || count($newSchedules) === 0) {
            Schedule::where('active', true)->update(['active' => false]);
            return back()->with('success', 'Todos los horarios han sido desactivados.');
        }

        // 1. Marcar como inactivos los horarios que ya no están en la lista de nuevos horarios
        Schedule::whereNotIn('description', $newSchedules)->update(['active' => false]);

        // 2. Crear o actualizar los horarios
        foreach ($newSchedules as $schedule) {

            [$startTime, $endTime]  = explode('-', $schedule);
            $startTime              = trim(str_replace([' am', ' pm'], '', $startTime));
            $endTime                = trim(str_replace([' am', ' pm'], '', $endTime));

            $startTimeFormatted = Carbon::createFromFormat('H:i', $startTime)->format('H:i:s');
            $endTimeFormatted   = Carbon::createFromFormat('H:i', $endTime)->format('H:i:s');

            // Actualiza si existe, de lo contrario, crea uno nuevo
            Schedule::updateOrCreate(
                ['description' => $schedule],
                [
                    'description'   => $schedule,
                    'start_time'    => $startTimeFormatted,
                    'end_time'      => $endTimeFormatted,
                    'active'        => true
                ]
            );
        }

        return back()->with('success', 'Horarios actualizados con éxito');
    }



    public function searchCustomer($document_number)
    {
        $customer = Customer::where('document_number', $document_number)->first();
        if ($customer) {
            return new CustomerResource($customer);
        } else {
            return response()->json(['message' => 'Customer not found'], 404);
        }
    }

    public function searchCustomerByRuc($ruc_number)
    {
        $customer = Customer::where('ruc_number', $ruc_number)->first();

        if ($customer) {
            return response()->json([
                'ruc_number' => $customer->ruc_number,
                'razon_social' => $customer->razon_social,
            ]);
        } else {
            return response()->json(['message' => 'Customer not found'], 404);
        }
    }


    /*
array:5 [ // app\Http\Controllers\Tenant\BookController.php:432
  "_token"              => "RGTXSPxWSxrQ6kAarC8FaNU7bkiKozZXrQSjQC9E"
  "_method"             => "PUT"
  "reservation_id"      => "9"
  "payment_finish-2-1"  => null
  "chkBall" => null
]
*/
    public function attachments(Request $request)
    {
        DB::beginTransaction();
        try {
            $chkBall    = $request->has('chkBall') ? 1 : 0;
            $chkVest    = $request->has('chkVest') ? 1 : 0;
            $chkDni     = $request->has('chkDni') ? 1 : 0;

            $reservation = Booking::find($request->get('reservation_id'));

            if (!$reservation) {
                throw new Exception("NO SE ENCONTRÓ LA RESERVA EN LA BD!!!");
            }

            // Actualizar los valores en Booking
            $reservation->ball  = $chkBall;
            $reservation->vest  = $chkVest;
            $reservation->dni   = $chkDni;
            $reservation->update();

            // Si la reserva es de crédito, actualizar también en la tabla credits
            if ($reservation->is_credit) {
                $credit = Credit::where('booking_id', $reservation->id)->first();

                if ($credit) {
                    $credit->ball = $chkBall;
                    $credit->vest = $chkVest;
                    $credit->dni  = $chkDni;
                    $credit->update();
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'REGISTRO ACTUALIZADO']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }


    public function customer_record($document_number)
    {
        DB::beginTransaction();
        try {

            if (!$document_number) {
                throw new Exception("EL N° DE DNI ESTÁ VACÍO!!!");
            }
            if (strlen($document_number) !== 8) {
                throw new Exception("DNI DEBE CONTAR CON 8 DÍGITOS!!!");
            }
            if (!is_numeric($document_number)) {
                throw new Exception("EL DNI DEBE SER NUMÉRICO!!!");
            }

            $customer   =   DB::connection('landlord')->select(
                'SELECT * FROM customers WHERE document_number = ?',
                [$document_number]
            );

            $record_customer    =   [];
            if (count($customer) === 0) {
                return response()->json(['success' => true, 'record_customer' => $record_customer]);
            }

            $record_customer    =   DB::table('bookings as b')
                ->join('schedules as s', 's.id', 'b.schedule_id')
                ->join('fields as f', 'f.id', 'b.field_id')
                ->select(
                    'b.id as booking_id',
                    'b.date as date_booking',
                    's.description as schedule',
                    'f.field',
                    'b.status'
                )
                ->where('customer_id', $customer[0]->id)
                ->orderBy('b.created_at', 'desc')
                ->take(5)
                ->get();

            return response()->json(['success' => true, 'record_customer' => $record_customer]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
    public function getAvailableFields(Request $request)
    {
        $date = $request->get('date');
        $scheduleId = $request->get('schedule_id');
        $nro_hours = $request->get('nro_hours', 1);
        $excludeFieldId = $request->get('exclude_field_id');

        $schedule = Schedule::findOrFail($scheduleId);

        $start_time = $schedule->start_time;
        $end_time = date("H:i:s", strtotime("+{$nro_hours} hours", strtotime($start_time)));

        $fields = Field::where('isDeleted', false)->get();

        $availableFields = $fields->filter(function ($field) use ($date, $start_time, $end_time, $excludeFieldId) {
            if ($field->id == $excludeFieldId) return false;

            $conflicts = DB::select('
            SELECT b.id
            FROM bookings AS b
            INNER JOIN schedules AS s ON s.id = b.schedule_id
            WHERE b.date = ?
            AND b.field_id = ?
            AND (
                s.start_time < ? AND DATE_ADD(s.start_time, INTERVAL (b.nro_hours * 60) MINUTE) > ?
            )
        ', [$date, $field->id, $end_time, $start_time]);

            return count($conflicts) === 0;
        })->values();

        return response()->json($availableFields);
    }
}
