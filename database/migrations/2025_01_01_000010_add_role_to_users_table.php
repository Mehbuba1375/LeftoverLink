<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['donor', 'ngo', 'admin'])->default('donor')->after('email');
            $table->string('organization_name')->nullable()->after('role');
            $table->string('phone')->nullable()->after('organization_name');
            $table->text('address')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'organization_name', 'phone', 'address']);
        });
    }
};
