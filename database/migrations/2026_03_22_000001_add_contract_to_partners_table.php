<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('partners') && !Schema::hasColumn('partners', 'contract_url')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->string('contract_url')->nullable()->after('revenue_model');
                $table->string('contract_signed_at')->nullable()->after('contract_url');
                $table->enum('contract_status', ['none', 'pending', 'signed'])->default('none')->after('contract_signed_at');
            });
        }
    }
    public function down(): void {
        if (Schema::hasTable('partners')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->dropColumn(['contract_url', 'contract_signed_at', 'contract_status']);
            });
        }
    }
};
