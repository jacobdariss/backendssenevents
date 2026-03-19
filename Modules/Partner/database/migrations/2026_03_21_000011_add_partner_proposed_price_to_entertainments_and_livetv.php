<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('entertainments', 'partner_proposed_price')) { Schema::table('entertainments', function (Blueprint $table) {
            $table->decimal('partner_proposed_price', 10, 2)->nullable()->after('price')
                ->comment('Prix soumis par le partenaire avant validation admin');
        }); }

        if (!Schema::hasColumn('live_tv_channel', 'partner_proposed_price')) { Schema::table('live_tv_channel', function (Blueprint $table) {
            $table->decimal('partner_proposed_price', 10, 2)->nullable()->after('plan_id')
                ->comment('Prix soumis par le partenaire avant validation admin');
            $table->decimal('price', 10, 2)->nullable()->after('plan_id');
            $table->string('purchase_type', 20)->nullable()->after('price');
        }); }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('entertainments', 'partner_proposed_price')) { Schema::table('entertainments', function (Blueprint $table) {
            $table->dropColumn('partner_proposed_price');
        }); }

        if (!Schema::hasColumn('live_tv_channel', 'partner_proposed_price')) { Schema::table('live_tv_channel', function (Blueprint $table) {
            $table->dropColumn(['partner_proposed_price', 'price', 'purchase_type']);
        });
    }
};
