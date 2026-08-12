<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->morphs('reviewable'); // reviewable_id, reviewable_type
            $table->tinyInteger('rating'); // 1–5
            $table->text('comment')->nullable();
            $table->string('packing_feedback')->nullable();
            $table->string('food_feedback')->nullable();
            $table->string('time_feedback')->nullable();
            $table->timestamps();

            // A user can only review the same entity once
            $table->unique(['reviewer_id', 'reviewable_id', 'reviewable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
