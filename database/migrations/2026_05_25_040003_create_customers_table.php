<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('marketplace_customer_id')->nullable();
            $table->string('marketplace')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->json('marketplace_data')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'marketplace_customer_id', 'marketplace']);
            $table->index(['marketplace', 'marketplace_customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
