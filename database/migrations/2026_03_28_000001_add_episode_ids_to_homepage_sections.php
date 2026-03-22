<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('homepage_sections', 'episode_ids')) {
                // Épisodes sélectionnés manuellement pour une section tvshow
                $table->json('episode_ids')->nullable()->after('content_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            if (Schema::hasColumn('homepage_sections', 'episode_ids')) {
                $table->dropColumn('episode_ids');
            }
        });
    }
};
