<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('live_tv_channel', 'partner_id')) {
        Schema::table('live_tv_channel', function (Blueprint $table) {
            $table->unsignedBigInteger('partner_id')->nullable()->after('id');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('set null');
        });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('live_tv_channel', 'partner_id')) {
        Schema::table('live_tv_channel', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropColumn('partner_id');
        });
    }
};
