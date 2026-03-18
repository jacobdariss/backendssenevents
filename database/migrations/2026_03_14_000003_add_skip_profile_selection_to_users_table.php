<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'skip_profile_selection')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('skip_profile_selection')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'skip_profile_selection')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('skip_profile_selection');
            });
        }
    }
};
