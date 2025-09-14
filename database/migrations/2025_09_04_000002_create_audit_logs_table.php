<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('col')->create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('action_id');
            $table->unsignedBigInteger('user_id');
            $table->longText('old_data')->nullable();
            $table->longText('new_data')->nullable();
            $table->longText('action_description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->string('action_type');
            $table->foreign('action_id', 'action_id_index')->references('id')->on('actions')->restrictOnDelete()->restrictOnUpdate();
            $table->index('action_id', 'action_id_index');
        });
    }

    public function down(): void
    {
        Schema::connection('col')->dropIfExists('audit_logs');
    }
};
