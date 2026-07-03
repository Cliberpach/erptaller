<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alinea sunat_status con la referencia (InvoiceService): agrega OBSERVADO (cdr code 1-1999).
     * last_send_message guarda el último mensaje de SUNAT/error para mostrarlo en el listado
     * sin depender de la respuesta transitoria del request.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE sales_documents MODIFY sunat_status ENUM('ACEPTADO','PENDIENTE','ENVIADO','RECHAZADO','ANULADO','ANULADO PARCIAL','OBSERVADO') NOT NULL DEFAULT 'PENDIENTE'");

        Schema::table('sales_documents', function (Blueprint $table) {
            $table->longText('last_send_message')->nullable()->after('ruta_qr');
        });
    }

    public function down(): void
    {
        Schema::table('sales_documents', function (Blueprint $table) {
            $table->dropColumn('last_send_message');
        });

        DB::statement("ALTER TABLE sales_documents MODIFY sunat_status ENUM('ACEPTADO','PENDIENTE','ENVIADO','RECHAZADO','ANULADO','ANULADO PARCIAL') NOT NULL DEFAULT 'PENDIENTE'");
    }
};
