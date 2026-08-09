<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ngo_web_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ngo_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('food_id')->constrained('foods')->onDelete('cascade');
            $table->integer('quantity_requested');
            $table->text('message')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, fulfilled
            $table->string('contact_name')->nullable();
            $table->string('pickup_time')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_no')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ngo_web_requests');
    }
};
