<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('consumer')->after('email');
            }
            if (!Schema::hasColumn('users', 'organization_name')) {
                $table->string('organization_name')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('organization_name');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('users', 'role')) $cols[] = 'role';
            if (Schema::hasColumn('users', 'organization_name')) $cols[] = 'organization_name';
            if (Schema::hasColumn('users', 'phone')) $cols[] = 'phone';
            if (Schema::hasColumn('users', 'address')) $cols[] = 'address';
            
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
