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
        Schema::create('foods', function (Blueprint $table) {

            $table->id();

            $table->string('food_name');

            $table->string('category');

            $table->integer('quantity');

            $table->decimal('price', 8, 2);

            $table->dateTime('expiration_time');

            $table->string('pickup_window');

            $table->string('image')->nullable();

            $table->boolean('donation_status')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
