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
        Schema::create('alert_user', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('alert_id');
            $table->foreign('alert_id')->references('id')->on('alerts');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamp('notified_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->string('action_taken', 100)->nullable();
            $table->enum('notification_channel', ['WEB', 'EMAIL', 'PUSH', 'SMS'])->default('WEB');
            $table->timestamps();

            // Índices
            $table->unique(['alert_id', 'user_id'], 'unique_alert_user');
            $table->index(['user_id', 'read_at'], 'idx_user_unread');
            $table->index(['user_id', 'notified_at', 'read_at'], 'idx_user_pending');
            $table->index(['alert_id', 'notified_at', 'read_at'], 'idx_alert_stats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_user');
    }
};
