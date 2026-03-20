<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('seasons') && !Schema::hasColumn('seasons', 'partner_id')) {
            Schema::table('seasons', function (Blueprint $table) {
                $table->unsignedBigInteger('partner_id')->nullable()->after('id');
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable()->after('partner_id');
                $table->text('rejection_reason')->nullable()->after('approval_status');
            });
        }
        if (Schema::hasTable('episodes') && !Schema::hasColumn('episodes', 'partner_id')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->unsignedBigInteger('partner_id')->nullable()->after('id');
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable()->after('partner_id');
                $table->text('rejection_reason')->nullable()->after('approval_status');
            });
        }
    }
    public function down(): void {
        foreach (['seasons', 'episodes'] as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) {
                    $table->dropColumn(['partner_id', 'approval_status', 'rejection_reason']);
                });
            }
        }
    }
};
