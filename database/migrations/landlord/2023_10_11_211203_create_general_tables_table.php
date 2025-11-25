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
        Schema::create('general_tables', function (Blueprint $table) {
            $table->id();

            $table->string('name',160);
            $table->string('description',160)->nullable();
            $table->string('symbol',6);
            $table->string('parameter',6);

            $table->enum('status', ['ACTIVO', 'ANULADO'])->default('ACTIVO');
            $table->boolean('editable')->default(true);

            $table->unsignedBigInteger('creator_user_id')->nullable();
            $table->unsignedBigInteger('editor_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->string('delete_user_name')->nullable();
            $table->string('editor_user_name')->nullable();
            $table->string('create_user_name')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_tables');
    }
};
