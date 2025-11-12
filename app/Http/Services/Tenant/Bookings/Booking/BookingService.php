<?php

namespace App\Http\Services\Tenant\Bookings\Booking;

use App\Http\Controllers\Tenant\QRController;
use App\Http\Resources\ReservationResource;
use App\Http\Services\Tenant\PettyCash\PettyCashMovements\PettyCashMovementsManager;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Company;
use App\Models\Field;
use App\Models\Landlord\Customer;
use App\Models\PettyCashBook;
use App\Models\Schedule;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Credit;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingService
{
    private PettyCashMovementsManager $s_petty_cash_movement;

    public function __construct()
    {
        $this->s_petty_cash_movement    =   new PettyCashMovementsManager();
    }

    public function store(array $data): array
    {
        //======= REGISTRAR CLIENTE ========
        $_customer  =   $this->storeCustomer($data);

        //======= ACTUALIZAR MONTO CIERRE CAJA =======
        $cajaAbierta            =   $this->setClosingAmountCash($data);
        $data['cajaAbierta']    =   $cajaAbierta;
        //======= VERIFICAR SI LA RESERVA YA EXISTE =========
        $existingBooking    =   Booking::where('customer_id', $_customer->id)
            ->where('field_id', $data['field_id'])
            ->where('schedule_id', $data['schedule_id'])
            ->where('date', $data['date'])
            ->first();

        if ($existingBooking) {
            return $this->setPayBooking($data, $_customer->id, $existingBooking);
        }

        return $this->saveBooking($data, $_customer);
    }

    public function storeCustomer(array $data): Customer
    {
        $document_number    =   $data['document_number'];
        $_customer          =   null;
        if ($document_number === '99999999') {
            $_customer                              =   new Customer();
            $_customer->name                        =   $data['name'];
            $_customer->phone                       =   $data['phone'];
            $_customer->document_number             =   $document_number;
            $_customer->type_identity_document_id   =   '1';
            $_customer->type_document_name          =   'DOCUMENTO NACIONAL DE IDENTIDAD';
            $_customer->type_document_abbreviation  =   'DNI';
            $_customer->type_document_code          =   '01';
            $_customer->ruc_number                  =   $data['ruc_number'];
            $_customer->razon_social                =   $data['razon_social'];
            $_customer->save();
        } else {
            $_customer = Customer::firstOrCreate(
                ['document_number' => $data['document_number']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'type_identity_document_id' => '1',
                    'type_document_name' => 'DOCUMENTO NACIONAL DE IDENTIDAD',
                    'type_document_abbreviation' => 'DNI',
                    'type_document_code' => '01',
                    'ruc_number' => $data['ruc_number'] ?? null,
                    'razon_social' => $data['razon_social'] ?? null
                ]
            );
        }

        return $_customer;
    }

    public function setClosingAmountCash(array $data): PettyCashBook
    {
        $cajaAbierta = PettyCashBook::where('status', 'open')->first();
        if ($cajaAbierta->closing_amount === null) {
            $cajaAbierta->closing_amount = $cajaAbierta->initial_amount;
        }
        return $cajaAbierta;
    }

    public function setPayBooking(array $data, int $customer_id, $existingBooking): array
    {
        if ($existingBooking->payment_status == 'TOTAL') {
            throw new Exception('La reserva ya ha sido pagada completamente.', 400);
        }

        $remainingAmount = round($existingBooking->total - $existingBooking->bookingDetails()->sum('payment'), 2);

        if ($data['payment'] == 0) {
            throw new Exception('No puede realizar un pago de 0.00 en Finalizar Reserva!!!');
        }

        if ($data['payment'] > $remainingAmount) {
            throw new Exception('El monto excede el pago pendiente. Debes pagar como máximo: ' . $remainingAmount);
        }

        $paymentStatus                      = $data['payment'] == $remainingAmount ? 'TOTAL' : 'PARCIAL';
        $existingBooking->payment_status    = $paymentStatus;
        $existingBooking->status            = $paymentStatus == 'TOTAL' ? 'ALQUILADO' : 'RESERVADO';
        $existingBooking->save();

        if ($paymentStatus === 'TOTAL') {
            $reservasJuntadas   =   Booking::where('customer_id', $customer_id)
                ->where('schedule_id', $data['schedule_id'])
                ->where('date', $data['date'])
                ->where('total', 0)
                ->where('status', 'ADICIONAL')
                ->get();

            foreach ($reservasJuntadas as $reserva) {
                $reserva->payment_status = 'TOTAL';
                $reserva->save();
            }
        }

        $bookingDetail = new BookingDetail([
            'booking_id'    => $existingBooking->id,
            'payment'       => $data['payment'],
            'payment_type'  => $data['payment_type'],
        ]);

        if ($data['payment_type'] != 'EFECTIVO' && isset($data['voucher'])) {
            $bookingDetail->voucher = $data['voucher']->store('vouchers', 'public');
        }

        $bookingDetail->save();

        //======== INCREMENTAR MONTO CIERRE Y VENTA DÍA EN LA CAJA ==========
        $this->s_petty_cash_movement->increaseClosingAmount($data['cajaAbierta']->id, $bookingDetail->payment);

        return [
            'message' => 'Pago registrado exitosamente',
            'data' => new ReservationResource($existingBooking),
        ];
    }

    public function saveBooking(array $data, $_customer): array
    {
        //======== OBTENER CAMPO Y CONFIGURACIÓN ========
        $field          =   Field::findOrFail($data['field_id']);
        $schedule       =   Schedule::findOrFail($data['schedule_id']);
        $configuration  =   Configuration::find(1);
        $isCredit       =   $data['credit'] == '1';
        $nro_hours      =   $data['nro_hours'];

        $booking_start_time     =   $schedule->start_time;
        $booking_end_time       =   date("H:i:s", strtotime("+{$nro_hours} hours", strtotime($booking_start_time)));
        $configurationTimeNight =   Carbon::createFromFormat('H:i', $configuration->property);

        //====== FORMATEAR A TIMESTAMPS EL START TIME =======
        $booking_start_time_format  =   Carbon::createFromFormat('H:i:s', $booking_start_time);

        //====== DEFINIENDO PRECIO DEL CAMPO ==========
        $price_field    =   $booking_start_time_format >= $configurationTimeNight ? $field->night_price : $field->day_price;
        $total          =   $price_field * $nro_hours;

        //========= VALIDAR QUE NO EXISTAN CONFLICTOS CON OTROS HORARIOS EN EL MISMO CAMPO Y FECHA =========
        $reservasPrincipales    =   DB::select('SELECT
                                    s.start_time,
                                    SEC_TO_TIME( TIME_TO_SEC(s.start_time) + (b.nro_hours * 3600) ) AS new_end_time
                                    FROM bookings AS b
                                    INNER JOIN schedules AS s ON s.id = b.schedule_id
                                    WHERE b.date = ? AND b.field_id = ?', [$data['date'], $field->id]);

        foreach ($reservasPrincipales as $booking) {

            $existing_start = strtotime($booking->start_time);
            $existing_end   = strtotime($booking->new_end_time);
            $new_start      = strtotime($booking_start_time);
            $new_end        = strtotime($booking_end_time);

            if ($new_start < $existing_end && $new_end > $existing_start) {
                throw new Exception('El horario seleccionado entra en conflicto con una reserva ya existente.', 400);
            }
        }

        //======= GUARDAR BLOQUES DE HORARIOS CON LA RESERVA ========
        $startTime = Carbon::parse($schedule->start_time);
        $endTime = $startTime->copy()->addMinutes($nro_hours * 60);

        if ($endTime->format('H:i:s') > $startTime->format('H:i:s')) {
            // Caso normal: dentro del mismo día
            $bloques = Schedule::where('start_time', '>=', $startTime->format('H:i:s'))
                ->where('start_time', '<', $endTime->format('H:i:s'))
                ->orderBy('start_time')
                ->get();
        } else {
            // Cruza medianoche: hacer dos bloques
            $bloques = Schedule::where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '>=', $startTime->format('H:i:s'))
                    ->orWhere('start_time', '<', $endTime->format('H:i:s'));
            })
                ->orderBy('start_time')
                ->get();
        }
        dd($bloques);

        //======= PROCESAR OPCIÓN UNIR CAMPOS =========
        $juntarConIds   =   collect(explode(',', $data['juntar_con_ids'] ?? null))
            ->filter(fn($id) => is_numeric($id))
            ->map(fn($id) => (int) $id);

        foreach ($juntarConIds as $extraFieldId) {
            $conflicts  =   DB::select('SELECT
                                s.start_time,
                                SEC_TO_TIME( TIME_TO_SEC(s.start_time) + (b.nro_hours * 3600) ) AS new_end_time
                                FROM bookings AS b
                                INNER JOIN schedules AS s ON s.id = b.schedule_id
                                WHERE b.date = ? AND b.field_id = ?', [$data['date'], $extraFieldId]);

            foreach ($conflicts as $booking) {

                $existing_start = strtotime($booking->start_time);
                $existing_end   = strtotime($booking->new_end_time);
                $new_start      = strtotime($booking_start_time);
                $new_end        = strtotime($booking_end_time);

                if ($new_start < $existing_end && $new_end > $existing_start) {
                    throw new Exception('El campo adicional ' . $extraFieldId . ' tiene conflicto de horario.', 400);
                }
            }

            //=========== INCREMENTAR PRECIO DE LA RESERVA CON LOS CAMPOS ADICIONALES ==========
            $extraField     =   Field::find($extraFieldId);
            $priceExtra     =   $booking_start_time_format >= $configurationTimeNight ? $extraField->night_price : $extraField->day_price;
            $total          +=  $priceExtra * $nro_hours;
        }


        if ($data['payment'] > $total) {
            throw new Exception('El monto excede el total. Debes pagar como máximo: ' . $total);
        }

        //======= VALIDAR MODALIDADES ==========
        $modalidad = $data['modality'];
        if ($modalidad === '9v9' && $juntarConIds->count() !== 1) {
            throw new Exception('Para la modalidad 9vs9 debes seleccionar exactamente 1 campo adicional.');
        }

        if ($modalidad === '11vs11' && $juntarConIds->count() !== 2) {
            throw new Exception('Para la modalidad 11vs11 debes seleccionar exactamente 2 campos adicionales.');
        }

        //========== DEFINIR ESTADO PAGO ==========
        $paymentStatus = 'SIN_PAGO';
        $status = 'RESERVADO';
        if ($isCredit) {
            $paymentStatus  = 'SIN_PAGO';
            $status         = 'ALQUILADO';
        } elseif ($data['payment'] == 0) {
            $paymentStatus = 'SIN_PAGO';
        } elseif ($data['payment'] < $total) {
            $paymentStatus = 'PARCIAL';
        } else {
            $paymentStatus = 'TOTAL';
            $status = 'ALQUILADO';
        }

        //======== DEFINIR NOMBRE DEL CAMPO =========
        $campos = [$field->field];

        if ($juntarConIds->isNotEmpty()) {
            $extraFields = Field::whereIn('id', $juntarConIds)->pluck('field')->toArray();
            $campos = array_merge($campos, $extraFields);
        }
        $fieldNamesText = implode(' + ', $campos);


        //======== INSERTAR RESERVA ========
        $booking = new Booking([
            'field_id'                      =>  $field->id,
            'field_names'                   =>  $fieldNamesText,
            'schedule_id'                   =>  $schedule->id,
            'customer_id'                   =>  $_customer->id,
            'customer_name'                 =>  $_customer->name,
            'customer_document_number'      =>  $_customer->document_number,
            'customer_phone'                =>  $_customer->phone,
            'customer_type_document_name'   =>  $_customer->type_document_name,
            'customer_type_document_id'     =>  $_customer->type_identity_document_id,
            'date'                          =>  $data['date'],
            'total'                         =>  $total,
            'payment_status'                =>  $paymentStatus,
            'status'                        =>  $status,
            'nro_hours'                     =>  $nro_hours,
            'is_credit'                     =>  $isCredit ? 1 : 0,
            'modality'                      =>  $data['modality'] ?? '7v7',
            'start_time'                    =>  $booking_start_time,
            'end_time'                      =>  $booking_end_time
        ]);
        $booking->save();

        //====== GUARDAR DETALLE RESERVA =======
        $bookingDetail = new BookingDetail([
            'booking_id'    => $booking->id,
            'payment'       => $isCredit ? 0 : $data['payment'],
            'payment_type'  => $isCredit ? 'CREDITO' : $data['payment_type'],
        ]);


        if (!$isCredit && $data['payment_type'] != 'EFECTIVO' && isset($data['voucher'])) {
            $bookingDetail->voucher = $data['voucher']->store('vouchers', 'public');
        }

        $bookingDetail->save();

        //======== CREAR RESRVAS PARA LOS CAMPOS JUNTADOS ========
        foreach ($juntarConIds as $extraFieldId) {
            Booking::create([
                'field_id'          => $extraFieldId,
                'field_names'       => $fieldNamesText,
                'schedule_id'       => $schedule->id,
                'customer_id'       => $_customer->id,
                'date'              => $data['date'],
                'total'             => 0,
                'payment_status'    => $paymentStatus,
                'status'            => 'ADICIONAL',
                'nro_hours'         => $nro_hours,
                'is_credit'         => $isCredit ? 1 : 0,
                'modality'          => $data['modality']
            ]);
        }

        //========= QR ==========
        $company = Company::first();
        $data_qr = (object)[
            'ruc_emisor' => $company->ruc,
            'tipo_comprobante' => 'RESERVA',
            'serie' => 'R',
            'correlativo' => str_pad($booking->id, 8, '0', STR_PAD_LEFT),
            'total' => number_format($booking->total, 2, '.', ''),
            'fecha_emision' => now()->format('Y-m-d H:i:s'),
            'fecha_reserva' => now()->format('Y-m-d'),
            'tipo_documento_adquiriente' => $booking->customer->type_document_code,
            'nro_documento_adquieriente' => $booking->customer->document_number
        ];

        $res_qr = QRController::generateQr(json_encode($data_qr))->getData();
        if ($res_qr->success) {
            $booking->qr_route = $res_qr->data->ruta_qr;
            $booking->save();
        }

        if ($isCredit) {
            Credit::create([
                'booking_id' => $booking->id,
                'customer_id' => $_customer->id,
                'customer_name' => $_customer->name,
                'customer_document_number' => $_customer->document_number,
                'customer_phone' => $_customer->phone,
                'field_id' => $field->id,
                'field_name' => $field->field,
                'start_time' => $schedule->start_time,
                'end_time' => date("H:i:s", strtotime("+{$nro_hours} hours", strtotime($schedule->start_time))),
                'total_hours' => $nro_hours,
                'ruc_number' => $data['ruc_number'] ?? null,
                'razon_social' => $data['razon_social'] ?? null,
                'amount' => $total,
                'date' => $data['date'],
                'ball' => 0,
                'vest' => 0,
                'dni' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            //======== INCREMENTAR MONTO CIERRE Y VENTA DÍA EN LA CAJA ==========
            $this->s_petty_cash_movement->increaseClosingAmount($data['cajaAbierta']->id, $bookingDetail->payment);
        }

        return [
            'message'   =>  'Reserva creada exitosamente',
            'data'      =>  new ReservationResource($booking),
        ];
    }
}
