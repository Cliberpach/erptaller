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
        Schema::create('alerts', function (Blueprint $table) {

            $table->id();
            $table->string('name', 500);
            $table->string('description', 500)->nullable();

            $table->unsignedBigInteger('object_id');

            $table->enum('type_object', ['ORDEN_TRABAJO', 'COTIZACION', 'VENTA', 'PRODUCTO']);

            $table->date('notice_date')->comment('FECHA DE NOTIFICACION');
            $table->date('advance_date')->comment('FECHA ANTICIPADA');
            $table->unsignedBigInteger('advance_days');

            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO');

            //=========== AUDITORÍA ==========
            $table->unsignedBigInteger('creator_user_id');
            $table->unsignedBigInteger('editor_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->string('delete_user_name')->nullable();
            $table->string('editor_user_name')->nullable();
            $table->string('creator_user_name');

            //========= ÍNDICES ========
            $table->index('object_id');
            $table->index('creator_user_id');
            $table->index('notice_date');
            $table->index('advance_date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
