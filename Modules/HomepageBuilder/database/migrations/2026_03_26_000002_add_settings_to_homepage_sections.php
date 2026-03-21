<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('homepage_sections', 'settings')) {
            Schema::table('homepage_sections', function (Blueprint $table) {
                $table->json('settings')->nullable()->after('card_orientation');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('homepage_sections', 'settings')) {
            Schema::table('homepage_sections', function (Blueprint $table) {
                $table->dropColumn('settings');
            });
        }
    }
};
