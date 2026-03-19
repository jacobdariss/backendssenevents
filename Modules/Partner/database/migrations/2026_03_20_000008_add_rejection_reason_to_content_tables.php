<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Motif de rejet sur les vidéos
        if (Schema::hasTable('videos') && !Schema::hasColumn('videos', 'rejection_reason')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('approval_status');
            });
        }

        // Motif de rejet sur les entertainments (films, séries)
        if (Schema::hasTable('entertainments') && !Schema::hasColumn('entertainments', 'rejection_reason')) {
            Schema::table('entertainments', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('approval_status');
            });
        }

        // Motif de rejet sur live_tv_channel
        if (Schema::hasTable('live_tv_channel') && !Schema::hasColumn('live_tv_channel', 'rejection_reason')) {
            Schema::table('live_tv_channel', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('approval_status');
            });
        }
    }

    public function down(): void
    {
        foreach (['videos', 'entertainments', 'live_tv_channel'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'rejection_reason')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('rejection_reason');
                });
            }
        }
    }
};
