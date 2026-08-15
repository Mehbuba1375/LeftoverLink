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
        Schema::table('reservations', function (Blueprint $table) {
            $table->date('preferred_pickup_date')->nullable()->after('status');
            $table->time('preferred_pickup_time')->nullable()->after('preferred_pickup_date');
            $table->date('approved_pickup_date')->nullable()->after('preferred_pickup_time');
            $table->time('approved_pickup_time')->nullable()->after('approved_pickup_date');
            $table->enum('pickup_schedule_status', ['pending', 'approved', 'adjusted'])->default('pending')->after('approved_pickup_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_pickup_date',
                'preferred_pickup_time',
                'approved_pickup_date',
                'approved_pickup_time',
                'pickup_schedule_status',
            ]);
        });
    }
};
