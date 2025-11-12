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
        Schema::create('document_serializations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->unsignedBigInteger("document_type_id");
            $table->foreign("document_type_id")->references("id")->on("document_types")->onDelete("cascade");
            $table->string("serie", 10);
            $table->integer("number_limit");
            $table->string("destiny")->nullable();
            $table->string("default")->nullable();
            $table->string("description")->nullable();
            $table->string("start_number")->default(1);
            $table->integer("final_number");
            $table->char('initiated',2)->default('NO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_serializations');
    }
};
