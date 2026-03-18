<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'default_profile_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('default_profile_id')->nullable()->after('skip_profile_selection');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'default_profile_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('default_profile_id');
            });
        }
    }
};
