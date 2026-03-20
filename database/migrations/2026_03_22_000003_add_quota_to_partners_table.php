<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('partners') && !Schema::hasColumn('partners', 'video_quota')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->unsignedInteger('video_quota')->nullable()->after('commission_rate')
                    ->comment('Max videos allowed. NULL = unlimited');
            });
        }
    }
    public function down(): void {
        if (Schema::hasTable('partners') && Schema::hasColumn('partners', 'video_quota')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->dropColumn('video_quota');
            });
        }
    }
};
