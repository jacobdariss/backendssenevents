<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seasons') && !Schema::hasColumn('seasons', 'partner_proposed_price')) {
            Schema::table('seasons', function (Blueprint $table) {
                $table->decimal('partner_proposed_price', 10, 2)->nullable()->after('price');
            });
        }

        if (Schema::hasTable('episodes') && !Schema::hasColumn('episodes', 'partner_proposed_price')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->decimal('partner_proposed_price', 10, 2)->nullable()->after('price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('seasons') && Schema::hasColumn('seasons', 'partner_proposed_price')) {
            Schema::table('seasons', function (Blueprint $table) {
                $table->dropColumn('partner_proposed_price');
            });
        }

        if (Schema::hasTable('episodes') && Schema::hasColumn('episodes', 'partner_proposed_price')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->dropColumn('partner_proposed_price');
            });
        }
    }
};
