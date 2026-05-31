<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marketplace_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('marketplace_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('product_id')->index();
            $table->string('sku_id')->nullable()->index();

            $table->string('name');
            $table->string('seller_sku')->nullable();

            $table->decimal('price', 18, 2)->nullable();
            $table->integer('stock')->nullable();

            $table->string('status')->nullable();
            $table->string('image')->nullable();

            $table->json('raw_data')->nullable();

            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['marketplace_account_id', 'product_id', 'sku_id'],
                'mp_product_sku_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_products');
    }
};
