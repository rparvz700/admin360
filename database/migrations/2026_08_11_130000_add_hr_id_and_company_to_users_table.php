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
            if (!Schema::hasColumn('users', 'hr_id')) {
                $table->string('hr_id', 50)->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'company')) {
                $table->string('company', 50)->nullable()->after('hr_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company')) {
                $table->dropColumn('company');
            }
            if (Schema::hasColumn('users', 'hr_id')) {
                $table->dropColumn('hr_id');
            }
        });
    }
};
