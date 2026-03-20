<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('homepage_sections', 'card_orientation')) {
                $table->enum('card_orientation', ['vertical', 'horizontal'])->default('vertical')->after('sort_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            if (Schema::hasColumn('homepage_sections', 'card_orientation')) {
                $table->dropColumn('card_orientation');
            }
        });
    }
};
