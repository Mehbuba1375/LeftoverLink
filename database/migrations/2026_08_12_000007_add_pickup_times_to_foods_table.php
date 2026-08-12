<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->time('pickup_start_time')->nullable()->after('expiration_time');
            $table->time('pickup_end_time')->nullable()->after('pickup_start_time');
        });

        // Safely populate existing listings with default start and end times
        DB::table('foods')->whereNull('pickup_start_time')->update([
            'pickup_start_time' => '10:00:00',
            'pickup_end_time' => '18:00:00',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn(['pickup_start_time', 'pickup_end_time']);
        });
    }
};
