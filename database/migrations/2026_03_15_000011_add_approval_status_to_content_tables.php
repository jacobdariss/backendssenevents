<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entertainments', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('approved')->after('status');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('approved')->after('status');
        });

        Schema::table('live_tv_channel', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('approved')->after('status');
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
