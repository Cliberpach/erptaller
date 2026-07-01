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
        Schema::table('company_invoice', function (Blueprint $table) {
            $table->string('api_user_gre', 120)->nullable()->after('secondary_password');
            $table->string('api_password_gre', 120)->nullable()->after('api_user_gre');

            $table->longText('token_reniec')->nullable()->after('environment');

            $table->char('department_id', 2)->nullable()->after('token_reniec');
            $table->foreign('department_id')->references('id')->on('departments');

            $table->char('province_id', 4)->nullable()->after('department_id');
            $table->foreign('province_id')->references('id')->on('provinces');

            $table->char('district_id', 6)->nullable()->after('province_id');
            $table->foreign('district_id')->references('id')->on('districts');

            $table->string('department_name', 160)->nullable()->after('district_id');
            $table->string('province_name', 160)->nullable()->after('department_name');
            $table->string('district_name', 160)->nullable()->after('province_name');
            $table->string('ubigeo', 20)->nullable()->after('district_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_invoice', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['province_id']);
            $table->dropForeign(['district_id']);

            $table->dropColumn([
                'api_user_gre',
                'api_password_gre',
                'token_reniec',
                'department_id',
                'province_id',
                'district_id',
                'department_name',
                'province_name',
                'district_name',
                'ubigeo',
            ]);
        });
    }
};
