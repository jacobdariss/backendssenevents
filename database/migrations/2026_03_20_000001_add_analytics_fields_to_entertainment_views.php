<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('entertainment_views')) {
            Schema::table('entertainment_views', function (Blueprint $table) {
                if (!Schema::hasColumn('entertainment_views', 'content_type'))
                    $table->string('content_type', 20)->nullable()->after('entertainment_id');
                if (!Schema::hasColumn('entertainment_views', 'episode_id'))
                    $table->unsignedBigInteger('episode_id')->nullable()->after('content_type');
                if (!Schema::hasColumn('entertainment_views', 'video_id'))
                    $table->unsignedBigInteger('video_id')->nullable()->after('episode_id');
                if (!Schema::hasColumn('entertainment_views', 'partner_id'))
                    $table->unsignedBigInteger('partner_id')->nullable()->after('video_id');
                if (!Schema::hasColumn('entertainment_views', 'watch_time'))
                    $table->unsignedInteger('watch_time')->default(0)->after('partner_id');
                if (!Schema::hasColumn('entertainment_views', 'device_type'))
                    $table->string('device_type', 20)->nullable()->after('watch_time');
                if (!Schema::hasColumn('entertainment_views', 'platform'))
                    $table->string('platform', 30)->nullable()->after('device_type');
                if (!Schema::hasColumn('entertainment_views', 'country_code'))
                    $table->string('country_code', 5)->nullable()->after('platform');
                if (!Schema::hasColumn('entertainment_views', 'ip_address'))
                    $table->string('ip_address', 45)->nullable()->after('country_code');
            });
        }
    }
    public function down(): void {}
};
