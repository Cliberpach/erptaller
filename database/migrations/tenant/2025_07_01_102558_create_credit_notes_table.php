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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales_documents');

            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->unsignedBigInteger('type_sale_id');
            $table->string('type_sale_code', 160);
            $table->string('type_sale_name', 160);

            $table->string('customer_name', 160);
            $table->enum('customer_type_document', ['DNI', 'RUC']);
            $table->string('customer_document_number', 20);
            $table->string('customer_document_code', 4);
            $table->string('customer_phone', 20)->nullable();

            $table->string('warehouse_name', 120);

            //====== MONTOS =========
            $table->decimal('igv_percentage', 14, 6)->unsigned();
            $table->decimal('subtotal', 14, 6)->unsigned();
            $table->decimal('igv_amount', 14, 6)->unsigned();
            $table->decimal('total', 14, 6)->unsigned();
            $table->string('legend', 260);


            $table->string('tipDocAfectado');
            $table->string('numDocfectado');
            $table->string('codMotivo');
            $table->string('desMotivo');

            $table->date('fechaEmision')->nullable();
            $table->string('tipoMoneda')->default('PEN');

            $table->decimal('mtoOperGravadas', 15, 2)->unsigned();
            $table->decimal('mtoIGV', 15, 2)->unsigned();
            $table->decimal('totalImpuestos', 15, 2)->unsigned();
            $table->decimal('mtoImpVenta', 15, 2)->unsigned();
            $table->string('ublVersion')->default('2.1');

            $table->string('obsevation', 200)->nullable();

            //========= SERIE Y CORRELATIVO =======
            $table->unsignedInteger('correlative');
            $table->string('serie', 100);

            $table->enum('sunat_status', ['ACEPTADO', 'PENDIENTE', 'ENVIADO', 'RECHAZADO'])->default('PENDIENTE');

            //======= FACTURACIÓN ========
            $table->tinyInteger('response_cdrZip')->nullable();
            $table->tinyInteger('response_success')->nullable();
            $table->string('response_error_code', 255)->nullable();
            $table->string('response_error_message', 255)->nullable();
            $table->string('cdr_response_id', 255)->nullable();
            $table->string('cdr_response_code', 255)->nullable();
            $table->string('cdr_response_description', 255)->nullable();
            $table->longText('cdr_response_notes')->nullable();
            $table->longText('cdr_response_reference')->nullable();
            $table->longText('ruta_cdr')->nullable();
            $table->longText('ruta_xml')->nullable();
            $table->longText('ruta_qr')->nullable();


            //======== AUDITORÍA ==========
            $table->unsignedBigInteger('creator_user_id')->nullable();
            $table->unsignedBigInteger('editor_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->string('delete_user_name')->nullable();
            $table->string('editor_user_name')->nullable();
            $table->string('creator_user_name')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
