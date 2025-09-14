<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('col')->create('collections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organization_id');
            $table->string('storage_type')->default('');
            $table->string('collection_name')->comment('s3bucket_name/sharepointsite_name');
            $table->text('collection_description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('published_collection_name')->nullable();
            $table->tinyInteger('is_synced')->default(0);
            $table->tinyInteger('is_cloud_collection')->default(0);
            $table->string('collection_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('col')->dropIfExists('collections');
    }
};
