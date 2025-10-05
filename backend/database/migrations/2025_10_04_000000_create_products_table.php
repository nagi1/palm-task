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
            // NOTE: We intentionally do NOT index the full `url` column because at length 2048 (utf8mb4 up to 4 bytes/char)
            // the index would exceed MySQL's default innodb large prefix limits (max key length 3072 bytes) -> error 1071.
            // If fast lookup by URL is required later, add a separate hashed column, e.g.:
            //   $table->string('url_hash', 64)->nullable()->index(); and persist hash('sha256', $url) in the model.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
