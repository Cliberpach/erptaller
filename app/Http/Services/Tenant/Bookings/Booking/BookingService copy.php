<?php

namespace App\Http\Services\Tenant\Bookings\Booking;

use App\Http\Resources\ReservationResource;

class BookingService
{


     public function __construct()
    {
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
]
*/
    public function store(BookStoreRequest $request)
    {
        DB::beginTransaction();
        try {

            $document_number    =   $request->get('document_number');
            $_customer          =   null;
            //======= SI ES CLIENTE VARIOS =======
            if($document_number === '99999999'){
                $_customer                              =   new Customer();
                $_customer->name                        =   $request->get('name');
                $_customer->phone                       =   $request->get('phone');
                $_customer->document_number             =   $document_number;
                $_customer->type_identity_document_id   =   '1';
                $_customer->type_document_name          =   'DOCUMENTO NACIONAL DE IDENTIDAD';
                $_customer->type_document_abbreviation  =   'DNI';
                $_customer->type_document_code          =   '01';
                $_customer->ruc_number                  =   $request->get('ruc_number');
                $_customer->razon_social                =   $request->get('razon_social');
                $_customer->save();
            }else{
                $_customer = Customer::firstOrCreate(
                    ['document_number' => $request['document_number']],
                    [
                        'name' => $request['name'],
                        'phone' => $request['phone'],
                        'type_identity_document_id' => '1',
                        'type_document_name' => 'DOCUMENTO NACIONAL DE IDENTIDAD',
                        'type_document_abbreviation' => 'DNI',
                        'type_document_code' => '01',
                        'ruc_number' => $request['ruc_number'] ?? null,
                        'razon_social' => $request['razon_social'] ?? null
                    ]
                );
            }

            $cajaAbierta = PettyCashBook::where('status', 'open')->first();
            if ($cajaAbierta->closing_amount === null) {
                $cajaAbierta->closing_amount = $cajaAbierta->initial_amount;
            }

            $field = Field::findOrFail($request['field_id']);
            $configuration = Configuration::find(1);
            $isCredit = $request->filled('credit') && $request->input('credit') == '1';

            $existingBooking = Booking::where('customer_id', $_customer->id)
                ->where('field_id', $request['field_id'])
                ->where('schedule_id', $request['schedule_id'])
                ->where('date', $request['date'])
                ->first();

            if ($existingBooking) {
                if ($existingBooking->payment_status == 'TOTAL') {
                    return response()->json(['message' => 'La reserva ya ha sido pagada completamente.'], 400);
                }

                $remainingAmount = round($existingBooking->total - $existingBooking->bookingDetails()->sum('payment'), 2);

                if ($request['payment'] == 0) {
                    return response()->json(['message' => 'No puede realizar un pago de 0.00 en Finalizar Reserva!!!'], 400);
                }

                if ($request['payment'] > $remainingAmount) {
                    return response()->json(['message' => 'El monto excede el pago pendiente. Debes pagar como máximo: ' . $remainingAmount], 400);
                }

                $paymentStatus = $request['payment'] == $remainingAmount ? 'TOTAL' : 'PARCIAL';
                $existingBooking->payment_status = $paymentStatus;
                $existingBooking->status = $paymentStatus == 'TOTAL' ? 'ALQUILADO' : 'RESERVADO';
                $existingBooking->save();

                if ($paymentStatus === 'TOTAL') {
                    $reservasJuntadas = Booking::where('customer_id', $_customer->id)
                        ->where('schedule_id', $request['schedule_id'])
                        ->where('date', $request['date'])
                        ->where('total', 0)
                        ->where('status', 'ADICIONAL')
                        ->get();

                    foreach ($reservasJuntadas as $reserva) {
                        $reserva->payment_status = 'TOTAL';
                        $reserva->save();
                    }
                }

                $bookingDetail = new BookingDetail([
                    'booking_id' => $existingBooking->id,
                    'payment' => $request['payment'],
                    'payment_type' => $request['payment_type'],
                ]);

                if ($request->payment_type != 'EFECTIVO' && $request->hasFile('voucher')) {
                    $bookingDetail->voucher = $request->file('voucher')->store('vouchers', 'public');
                }

                $cajaAbierta->closing_amount += $bookingDetail->payment;
                $cajaAbierta->sale_day += $bookingDetail->payment;
                $cajaAbierta->save();

                $bookingDetail->save();

                DB::commit();

                return response()->json([
                    'message' => 'Pago registrado exitosamente',
                    'data' => new ReservationResource($existingBooking),
                ], 200);
            }


            $schedule = Schedule::find($request['schedule_id']);
            $nro_hours = $request->get('nro_hours');
            $new_start_time = $schedule->start_time;
            $new_end_time_calculated = date("H:i:s", strtotime("+{$nro_hours} hours", strtotime($new_start_time)));
            $configurationTimeNight = Carbon::createFromFormat('H:i', $configuration->property);
            $scheduleTime = Carbon::createFromFormat('H:i:s', $schedule->start_time);

            $price_field = $scheduleTime >= $configurationTimeNight ? $field->night_price : $field->day_price;
            $total = $price_field * $nro_hours;

            // Verificar conflicto en campo principal
            $reservasPrincipales = DB::select('select
            s.start_time,
            SEC_TO_TIME( TIME_TO_SEC(s.start_time) + (b.nro_hours * 3600) ) as new_end_time
            from bookings as b
            inner join schedules as s on s.id = b.schedule_id
            where b.date = ? and b.field_id = ?', [$request->date, $field->id]);

            foreach ($reservasPrincipales as $booking) {
                $existing_start = strtotime($booking->start_time);
                $existing_end = strtotime($booking->new_end_time);
                $new_start = strtotime($new_start_time);
                $new_end = strtotime($new_end_time_calculated);

                if ($new_start < $existing_end && $new_end > $existing_start) {
                    return response()->json(['success' => false, 'message' => 'El horario seleccionado entra en conflicto con una reserva ya existente.'], 400);
                }
            }

            // === Procesar campos a juntar ===
            $juntarConIds = collect(explode(',', $request->get('juntar_con_ids', '')))
                ->filter(fn($id) => is_numeric($id))
                ->map(fn($id) => (int) $id);

            foreach ($juntarConIds as $extraFieldId) {
                $conflicts = DB::select('select
                s.start_time,
                SEC_TO_TIME( TIME_TO_SEC(s.start_time) + (b.nro_hours * 3600) ) as new_end_time
                from bookings as b
                inner join schedules as s on s.id = b.schedule_id
                where b.date = ? and b.field_id = ?', [$request->date, $extraFieldId]);

                foreach ($conflicts as $booking) {
                    $existing_start = strtotime($booking->start_time);
                    $existing_end = strtotime($booking->new_end_time);
                    $new_start = strtotime($new_start_time);
                    $new_end = strtotime($new_end_time_calculated);

                    if ($new_start < $existing_end && $new_end > $existing_start) {
                        return response()->json([
                            'success' => false,
                            'message' => 'El campo adicional ' . $extraFieldId . ' tiene conflicto de horario.',
                        ], 400);
                    }
                }

                // Sumar precio de campo adicional
                $extraField = Field::find($extraFieldId);
                $priceExtra = $scheduleTime >= $configurationTimeNight ? $extraField->night_price : $extraField->day_price;
                $total += $priceExtra * $nro_hours;
            }

            if ($request['payment'] > $total) {
                return response()->json(['message' => 'El monto excede el total. Debes pagar como máximo: ' . $total], 400);
            }

            $modalidad = $request->get('modality');

            if ($modalidad === '9v9' && $juntarConIds->count() !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Para la modalidad 9vs9 debes seleccionar exactamente 1 campo adicional.',
                ], 400);
            }

            if ($modalidad === '11vs11' && $juntarConIds->count() !== 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Para la modalidad 11vs11 debes seleccionar exactamente 2 campos adicionales.',
                ], 400);
            }

            $paymentStatus = 'SIN_PAGO';
            $status = 'RESERVADO';
            if ($isCredit) {
                $paymentStatus = 'SIN_PAGO';
                $status = 'ALQUILADO';
            } elseif ($request['payment'] == 0) {
                $paymentStatus = 'SIN_PAGO';
            } elseif ($request['payment'] < $total) {
                $paymentStatus = 'PARCIAL';
            } else {
                $paymentStatus = 'TOTAL';
                $status = 'ALQUILADO';
            }

            // Crear reserva principal
            $campos = [$field->field];

            if ($juntarConIds->isNotEmpty()) {
                $extraFields = Field::whereIn('id', $juntarConIds)->pluck('field')->toArray();
                $campos = array_merge($campos, $extraFields);
            }

            $fieldNamesText = implode(' + ', $campos);

            $booking = new Booking([
                'field_id' => $field->id,
                'field_names' => $fieldNamesText,
                'schedule_id' => $schedule->id,
                'customer_id' => $_customer->id,
                'customer_name' =>  $_customer->name,
                'customer_document_number'  =>  $_customer->document_number,
                'customer_phone'    =>  $_customer->phone,
                'customer_type_document_name'   =>  $_customer->type_document_name,
                'customer_type_document_id'     =>  $_customer->type_identity_document_id,
                'date' => $request['date'],
                'total' => $total,
                'payment_status' => $paymentStatus,
                'status' => $status,
                'nro_hours' => $nro_hours,
                'is_credit' => $isCredit ? 1 : 0,
                'modality' => $request->get('modality') ?? '7v7',
            ]);


            $booking->save();

            $bookingDetail = new BookingDetail([
                'booking_id' => $booking->id,
                'payment' => $isCredit ? 0 : $request['payment'],
                'payment_type' => $isCredit ? 'CREDITO' : $request['payment_type'],
            ]);

            if (!$isCredit && $request->payment_type != 'EFECTIVO' && $request->hasFile('voucher')) {
                $bookingDetail->voucher = $request->file('voucher')->store('vouchers', 'public');
            }

            $cajaAbierta->closing_amount += $bookingDetail->payment;
            $cajaAbierta->sale_day += $bookingDetail->payment;
            $cajaAbierta->save();

            $bookingDetail->save();

            // Crear reservas adicionales (mismo horario) para marcar ocupados
            foreach ($juntarConIds as $extraFieldId) {
                Booking::create([
                    'field_id' => $extraFieldId,
                    'field_names' => $fieldNamesText,
                    'schedule_id' => $schedule->id,
                    'customer_id' => $_customer->id,
                    'date' => $request['date'],
                    'total' => 0,
                    'payment_status' => $paymentStatus,
                    'status' => 'ADICIONAL',
                    'nro_hours' => $nro_hours,
                    'is_credit' => $isCredit ? 1 : 0,
                    'modality' => $request->get('modality')
                ]);
            }


            // QR
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
                    'ruc_number' => $request['ruc_number'] ?? null,
                    'razon_social' => $request['razon_social'] ?? null,
                    'amount' => $total,
                    'date' => $request['date'],
                    'ball' => 0,
                    'vest' => 0,
                    'dni' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Reserva creada exitosamente',
                'data' => new ReservationResource($booking),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear la reserva: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear la reserva',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

}
