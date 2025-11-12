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
        Schema::create('module_children_companies', function (Blueprint $table) {
            $table->unsignedBigInteger("module_id");
            $table->foreign("module_id")->references("id")->on("modules")->onDelete("cascade");

            $table->unsignedBigInteger("module_child_id");
            $table->foreign("module_child_id")->references("id")->on("module_children")->onDelete("cascade");

            $table->unsignedBigInteger("module_grand_child_id")->nullable();
            $table->foreign("module_grand_child_id")->references("id")->on("module_grand_children")->onDelete("cascade");

            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->nullable();

            $table->primary(['module_id', 'module_child_id', 'company_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_children_companies');
    }
};
