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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('consumer')->after('email'); // consumer, food_provider, ngo, admin
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->after('profile_photo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('users', 'role')) $cols[] = 'role';
            if (Schema::hasColumn('users', 'phone')) $cols[] = 'phone';
            if (Schema::hasColumn('users', 'profile_photo')) $cols[] = 'profile_photo';
            if (Schema::hasColumn('users', 'address')) $cols[] = 'address';

            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
