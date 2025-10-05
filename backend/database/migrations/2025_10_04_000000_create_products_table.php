<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048)->nullable();
            $table->string('source', 32)->nullable();
            $table->string('asin', 32)->nullable();
            $table->string('title');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 8)->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['source', 'asin']);
            $table->index('url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
