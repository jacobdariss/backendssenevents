<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entertainments', function (Blueprint $table) {
            if (!Schema::hasColumn('entertainments', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->after('partner_id');
            }
        });

        Schema::table('videos', function (Blueprint $table) {
            if (!Schema::hasColumn('videos', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->after('partner_id');
            }
        });

        Schema::table('live_tv_channel', function (Blueprint $table) {
            if (!Schema::hasColumn('live_tv_channel', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->after('partner_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('entertainments', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });

        Schema::table('live_tv_channel', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
