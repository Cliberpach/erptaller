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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('ruc')->unique();
            $table->string('business_name')->unique();
            $table->string('abbreviated_business_name')->unique()->nullable();

            $table->string('domain')->nullable();
            $table->string('files_route',200)->nullable();
            $table->string('tenant_id')->nullable();

            $table->string('logo_url')->nullable();
            $table->string('logo')->nullable();
            $table->longText('base64_logo')->nullable();

            $table->string('lat')->nullable();
            $table->string('lng')->nullable();

            $table->string('fiscal_address')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('email')->nullable();

            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('web')->nullable();

            // Facturacion Electronica
            $table->enum('invoicing_status', [0, 1])->default(0);
            $table->enum('status', [0, 1])->nullable()->default(1);
            $table->decimal('igv', 10, 4)->unsigned()->default(18);


            // Plan
            $table->enum('plan', [1, 2, 3]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
