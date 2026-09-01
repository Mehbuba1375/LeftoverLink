<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the payments table for SSLCommerz payment integration.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('food_id')->nullable()->constrained('foods')->onDelete('set null');
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->onDelete('set null');
            $table->string('tran_id')->unique();
            $table->string('val_id')->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('BDT');
            $table->enum('status', ['PENDING', 'VALIDATED', 'FAILED', 'CANCELLED', 'REFUNDED'])->default('PENDING');
            $table->string('payment_method')->default('SSLCommerz');
            $table->string('card_type')->nullable();
            $table->string('card_no')->nullable();
            $table->string('bank_tran_id')->nullable();
            $table->timestamp('pay_time')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
