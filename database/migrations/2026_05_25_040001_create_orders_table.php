<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('marketplace_order_id');
            $table->string('marketplace'); // shopee, tiktok, tokopedia, etc.
            $table->string('order_number');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('shipping_address');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method');
            $table->string('payment_status')->default('pending');
            $table->string('order_status')->default('pending');
            $table->timestamp('order_date');
            $table->json('marketplace_data')->nullable();
            $table->timestamps();

            $table->unique(
                ['company_id', 'marketplace_order_id', 'marketplace'],
                'uq_mp_order_company_market_order_platform'
            );
            $table->index(
                ['marketplace', 'marketplace_order_id'],
                'idx_mp_order_platform_order_id'
            );
            $table->index(
                ['order_status', 'order_date'],
                'idx_mp_order_status_date'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_order');
    }
};
