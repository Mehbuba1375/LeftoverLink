<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->string('unit')->default('kg'); // kg, pieces, boxes, liters
            $table->date('expiry_date')->nullable();
            $table->string('pickup_location')->nullable();
            $table->enum('status', ['available', 'requested', 'fulfilled', 'expired'])->default('available');
            $table->string('food_type')->nullable(); // cooked, raw, packaged
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_listings');
    }
};
