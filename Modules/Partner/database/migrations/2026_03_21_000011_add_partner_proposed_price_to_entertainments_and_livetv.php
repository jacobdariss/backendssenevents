<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('entertainments') && !Schema::hasColumn('entertainments', 'partner_proposed_price')) {
            Schema::table('entertainments', function (Blueprint $table) {
                $table->decimal('partner_proposed_price', 10, 2)->nullable()->after('price');
            });
        }

        if (Schema::hasTable('live_tv_channel') && !Schema::hasColumn('live_tv_channel', 'partner_proposed_price')) {
            Schema::table('live_tv_channel', function (Blueprint $table) {
                $table->decimal('partner_proposed_price', 10, 2)->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->string('purchase_type', 20)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('entertainments') && Schema::hasColumn('entertainments', 'partner_proposed_price')) {
            Schema::table('entertainments', function (Blueprint $table) {
                $table->dropColumn('partner_proposed_price');
            });
        }

        if (Schema::hasTable('live_tv_channel')) {
            $cols = array_filter(['partner_proposed_price', 'price', 'purchase_type'], function ($col) {
                return Schema::hasColumn('live_tv_channel', $col);
            });
            if (!empty($cols)) {
                Schema::table('live_tv_channel', function (Blueprint $table) use ($cols) {
                    $table->dropColumn(array_values($cols));
                });
            }
        }
    }
};
