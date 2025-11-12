<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('field_id');
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('reservation_document_id')->nullable();
            $table->date('date');
            $table->float('total');
            $table->string('modality', 10)->nullable();
            $table->longText('qr_route')->nullable();

            $table->boolean('ball')->default(false)->comment('Indica si se pidió balón');
            $table->boolean('vest')->default(false)->comment('Indica si se pidió chaleco');
            $table->boolean('dni')->default(false)->comment('Indica si se dejó dni');

            $table->decimal('nro_hours', 10, 2)->unsigned()->comment('N° horas alquiler');

            $table->enum('payment_status', ['SIN_PAGO', 'PARCIAL', 'TOTAL']);

            $table->enum('status', ['LIBRE', 'RESERVADO', 'ALQUILADO','ADICIONAL']);
            
            $table->boolean('is_credit')->default(false);

            $table->string('customer_name',160);
            $table->string('customer_document_number',20);
            $table->string('customer_phone',20)->nullable();
            $table->string('customer_type_document_name',100);
            $table->unsignedBigInteger('customer_type_document_id');
            $table->string('sale_document_serie',20);
            $table->unsignedBigInteger('sale_document_id')->nullable();

            $table->enum('facturado', ['SI', 'NO'])->default('NO')->comment('Indica si una reserva fue facturada');

            $table->time('start_time')->comment('Hora inicio');
            $table->time('end_time')->comment('Hora fin');

            $table->timestamps();

            //$table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('field_id')->references('id')->on('fields');
            $table->foreign('schedule_id')->references('id')->on('schedules');
            $table->foreign('reservation_document_id')->references('id')->on('reservation_documents');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
