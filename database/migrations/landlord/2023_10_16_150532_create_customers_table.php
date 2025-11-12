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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('type_identity_document_id');
            //$table->foreign('type_identity_document_id')->references('id')->on('types_identity_documents');

            $table->string('type_document_name',100);
            $table->string('type_document_abbreviation',20);
            $table->string('type_document_code',4);

            $table->string('document_number',20);
            $table->string('name',160);
            $table->string('phone',20)->nullable();

            //DATOS DE RUC (NULLABLE)

            $table->string('ruc_number')->nullable();
            $table->string('razon_social')->nullable();


            $table->string('address',160)->nullable();
            $table->string('email',160)->nullable();

            $table->char('department_id', 2)->nullable();
            //$table->foreign('department_id')->references('id')->on('departments');

            $table->char('province_id', 4)->nullable();
            //$table->foreign('province_id')->references('id')->on('provinces');

            $table->char('district_id', 6)->nullable();
            //$table->foreign('district_id')->references('id')->on('districts');

            $table->string('department_name',160)->nullable();
            $table->string('province_name',160)->nullable();
            $table->string('district_name',160)->nullable();

            $table->string('zone',160)->nullable();
            $table->string('ubigeo',20)->nullable();

            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
