<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['entertainments', 'videos', 'live_tv_channel'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'approval_status')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('approval_status', 20)->default('approved')->after('status');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['entertainments', 'videos', 'live_tv_channel'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'approval_status')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('approval_status');
                });
            }
        }
    }
};
