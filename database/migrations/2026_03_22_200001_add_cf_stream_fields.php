<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // entertainments
        if (Schema::hasTable('entertainments') && !Schema::hasColumn('entertainments', 'cf_stream_uid')) {
            Schema::table('entertainments', function (Blueprint $table) {
                $table->string('cf_stream_uid', 64)->nullable()->after('video_url_input');
                $table->enum('cf_stream_status', ['pending','processing','ready','error'])->nullable()->after('cf_stream_uid');
            });
        }

        // episodes
        if (Schema::hasTable('episodes') && !Schema::hasColumn('episodes', 'cf_stream_uid')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->string('cf_stream_uid', 64)->nullable()->after('video_url_input');
                $table->enum('cf_stream_status', ['pending','processing','ready','error'])->nullable()->after('cf_stream_uid');
            });
        }

        // videos
        if (Schema::hasTable('videos') && !Schema::hasColumn('videos', 'cf_stream_uid')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->string('cf_stream_uid', 64)->nullable()->after('video_url_input');
                $table->enum('cf_stream_status', ['pending','processing','ready','error'])->nullable()->after('cf_stream_uid');
            });
        }
    }

    public function down(): void
    {
        foreach (['entertainments', 'episodes', 'videos'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn(['cf_stream_uid', 'cf_stream_status']);
                });
            }
        }
    }
};
