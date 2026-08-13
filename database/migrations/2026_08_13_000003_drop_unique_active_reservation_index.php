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
        try {
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                $indices = DB::select("PRAGMA index_list('reservations')");
                $hasIndex = collect($indices)->pluck('name')->contains('unique_active_reservation');
                if ($hasIndex) {
                    DB::statement('DROP INDEX unique_active_reservation');
                }
            } else {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->dropUnique('unique_active_reservation');
                });
            }
        } catch (\Throwable $e) {
            // Ignore if index does not exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unique(['user_id', 'food_id', 'status'], 'unique_active_reservation');
        });
    }
};
