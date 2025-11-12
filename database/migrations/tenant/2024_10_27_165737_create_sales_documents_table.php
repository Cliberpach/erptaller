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
        Schema::create('sales_documents', function (Blueprint $table) {
            $table->id();

            //======== CLIENTE Y SUS DATOS HISTÓRICO =======
            $table->unsignedBigInteger('customer_id');
            //$table->foreign('customer_id')->references('id')->on('customers');

            $table->string('customer_name',160);
            $table->enum('customer_type_document', ['DNI', 'RUC']);
            $table->string('customer_document_number',20);
            $table->string('customer_document_code',4);
            $table->string('customer_phone',20)->nullable();

            //====== USUARIO REGISTRADOR Y SUS DATOS HISTÓRICOS =======
            $table->unsignedBigInteger('user_recorder_id');
            $table->foreign('user_recorder_id')->references('id')->on('users');

            $table->string('user_recorder_name',160);

            //========== CAJA EN LA QUE SE ESTÁ CREANDO LA VENTA =====
            $table->unsignedBigInteger('petty_cash_id');
            $table->foreign('petty_cash_id')->references('id')->on('petty_cashes');

            $table->string('petty_cash_name',160);

            //========= MOVIMIENTO DE LA CAJA EN LA CUAL SE GENERA LA VENTA =======
            $table->unsignedBigInteger('petty_cash_book_id');
            $table->foreign('petty_cash_book_id')->references('id')->on('petty_cash_books');

            //========= TIPO DE VENTA, CODIGO Y NOMBRE =======
            $table->enum('type_sale_code', ['80', '3','1']);
            $table->enum('type_sale_name', ['NOTA DE VENTA', 'BOLETA DE VENTA ELECTRÓNICA','FACTURA ELECTRÓNICA']);

            //====== MONTOS =========
            $table->decimal('igv_percentage', 14, 6)->unsigned();
            $table->decimal('subtotal', 14, 6)->unsigned();
            $table->decimal('igv_amount', 14, 6)->unsigned();
            $table->decimal('total', 14, 6)->unsigned();
            $table->string('legend',260);

            //======== PAGOS ======
            $table->unsignedBigInteger('method_pay_id_1');
            $table->foreign('method_pay_id_1')->references('id')->on('payment_methods');
            $table->decimal('amount_pay_1', 14, 6);

            $table->unsignedBigInteger('method_pay_id_2')->nullable();
            $table->foreign('method_pay_id_2')->references('id')->on('payment_methods');
            $table->decimal('amount_pay_2', 14, 6)->nullable();

            //========= SERIE Y CORRELATIVO =======
            $table->unsignedInteger('correlative');
            $table->string('serie',100);

            $table->enum('estado', ['ACEPTADO','PENDIENTE', 'ENVIADO', 'RECHAZADO'])->default('PENDIENTE');

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

            $table->enum('type', ['PRODUCTOS', 'RESERVAS']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_documents');
    }
};
