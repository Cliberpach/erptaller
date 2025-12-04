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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('bank_id')->comment('Banco ID');
            $table->string('bank_name', 160)->comment('Nombre del banco');
            $table->string('bank_abbreviation', 120)->comment('Sigla del banco');

            $table->string('account_number', 100)->comment('Número de cuenta');
            $table->string('cci', 100)->nullable()->comment('CCI');
            $table->string('phone', 20)->nullable()->comment('Celular');
            $table->string('holder', 200)->comment('Titular');
            $table->string('currency', 160)->comment('Moneda');

            $table->unsignedBigInteger('creator_user_id')->nullable();
            $table->string('creator_user_name', 200)->nullable();
            $table->unsignedBigInteger('editor_user_id')->nullable();
            $table->string('editor_user_name', 200)->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable()->comment('Usuario que elimina');
            $table->string('delete_user_name', 200)->nullable()->comment('Nombre del usuario que elimina');

            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO')->comment('Estado');

            $table->boolean('editable')->default(1)->comment('Editable');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
