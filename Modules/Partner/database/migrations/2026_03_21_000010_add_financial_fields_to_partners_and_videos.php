<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('partners') && !Schema::hasColumn('partners', 'commission_rate')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->decimal('commission_rate', 5, 2)->nullable()->after('status');
                $table->string('revenue_model', 20)->default('flat')->after('commission_rate');
            });
        }

        if (Schema::hasTable('videos') && !Schema::hasColumn('videos', 'partner_proposed_price')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->decimal('partner_proposed_price', 10, 2)->nullable()->after('price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('partners') && Schema::hasColumn('partners', 'commission_rate')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->dropColumn(['commission_rate', 'revenue_model']);
            });
        }

        if (Schema::hasTable('videos') && Schema::hasColumn('videos', 'partner_proposed_price')) {
            Schema::table('videos', function (Blueprint $table) {
                $table->dropColumn('partner_proposed_price');
            });
        }
    }
};
