<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ngo_food_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ngo_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('food_listing_id')->constrained('food_listings')->onDelete('cascade');
            $table->integer('quantity_requested');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'fulfilled'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ngo_food_requests');
    }
};
