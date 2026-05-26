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
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('marketplace_product_id');
            $table->string('marketplace'); // shopee, tiktok, tokopedia, etc.
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('image_url')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default('active');
            $table->json('marketplace_data')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'marketplace_product_id', 'marketplace']);
            $table->index(['marketplace', 'marketplace_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
