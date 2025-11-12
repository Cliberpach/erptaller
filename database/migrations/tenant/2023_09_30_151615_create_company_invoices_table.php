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
        Schema::create('company_invoices', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            
            $table->string('plan')->nullable();
            $table->string('environment')->nullable()->default('DEMO');

            $table->longText('token_code')->nullable();
            $table->BigInteger('invoice_id')->nullable();

            //========= UBIGEO =========
            $table->char('department_id', 2)->nullable();
            $table->foreign('department_id')->references('id')->on('departments');

            $table->char('province_id', 4)->nullable();
            $table->foreign('province_id')->references('id')->on('provinces');

            $table->char('district_id', 6)->nullable();
            $table->foreign('district_id')->references('id')->on('districts');

            $table->string('department_name',160)->nullable();
            $table->string('province_name',160)->nullable();
            $table->string('district_name',160)->nullable();
            $table->string('ubigeo',20)->nullable();

            $table->string('urbanization',160)->nullable();
            $table->string('local_code',10)->nullable();

            //======= SOL USER SOL PASS ========
            $table->string('secondary_user',120)->nullable();
            $table->string('secondary_password',120)->nullable();

            //======= API GUÍAS REMISIÓN ======
            $table->string('api_user_gre',120)->nullable();
            $table->string('api_password_gre',120)->nullable();

            //========= CERTIFICADO =======
            $table->string('certificate')->nullable();
            $table->string('certificate_url')->nullable();
            $table->longText('certificate_password')->nullable();

            $table->enum('status', ['ACTIVE', 'CANCELED'])->default('ACTIVE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_invoices');
    }
};
