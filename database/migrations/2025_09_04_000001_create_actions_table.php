<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('col')->create('actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unique('name', 'name_unique_index');
            $table->index('name', 'idx_name');
        });
    }

    public function down(): void
    {
        Schema::connection('col')->dropIfExists('actions');
    }
};
