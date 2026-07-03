<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mismo tratamiento que se le dio a sales_documents: sede_id denormalizado (evita join
     * contra la venta origen al listar/filtrar por sede), last_send_message para el mensaje
     * de envío/error, y OBSERVADO en el enum (cdr code 1-1999), igual que Invoice.
     */
    public function up(): void
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('sede_id')->nullable()->after('id');
            $table->foreign('sede_id')->references('id')->on('sedes')->onDelete('cascade');

            $table->longText('last_send_message')->nullable();
        });

        DB::statement("ALTER TABLE credit_notes MODIFY sunat_status ENUM('ACEPTADO','PENDIENTE','ENVIADO','RECHAZADO','OBSERVADO') NOT NULL DEFAULT 'PENDIENTE'");
    }

    public function down(): void
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropForeign(['sede_id']);
            $table->dropColumn(['sede_id', 'last_send_message']);
        });

        DB::statement("ALTER TABLE credit_notes MODIFY sunat_status ENUM('ACEPTADO','PENDIENTE','ENVIADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE'");
    }
};
