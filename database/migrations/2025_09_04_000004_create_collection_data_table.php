<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('col')->create('collection_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('collection_id');
            $table->string('file_name');
            $table->bigInteger('size');
            $table->string('bucket_sp_site_name')->nullable();
            $table->string('file_id', 500);
            $table->string('last_modified', 30);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('collection_id', 'collection_data_collection_id_foreign')->references('id')->on('collections')->onDelete('cascade')->onUpdate('cascade');
            $table->index('collection_id', 'collection_data_collection_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::connection('col')->dropIfExists('collection_data');
    }
};
