<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Set approval_status = 'approved' for all content without a partner_id
        // These are admin-created contents and should not appear in partner validation queue
        foreach (['entertainments', 'videos', 'live_tv_channel'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'approval_status')) {
                DB::table($table)
                    ->whereNull('partner_id')
                    ->where('approval_status', 'pending')
                    ->update(['approval_status' => 'approved']);
            }
        }

        foreach (['seasons', 'episodes'] as $table) {
            if (Schema::hasTable($table)
                && Schema::hasColumn($table, 'approval_status')
                && Schema::hasColumn($table, 'partner_id')) {
                DB::table($table)
                    ->whereNull('partner_id')
                    ->where('approval_status', 'pending')
                    ->update(['approval_status' => 'approved']);
            }
        }
    }

    public function down(): void {}
};
