<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('entertainment_views')) return;

        Schema::table('entertainment_views', function (Blueprint $table) {
            // Index principal pour les requêtes analytics par partenaire et période
            if (!$this->indexExists('entertainment_views', 'ev_partner_date_idx'))
                $table->index(['partner_id', 'created_at'], 'ev_partner_date_idx');

            // Index pour les requêtes analytics par type de contenu
            if (!$this->indexExists('entertainment_views', 'ev_type_date_idx'))
                $table->index(['content_type', 'created_at'], 'ev_type_date_idx');

            // Index pour les stats par device
            if (!$this->indexExists('entertainment_views', 'ev_device_idx'))
                $table->index(['device_type'], 'ev_device_idx');

            // Index pour les stats par plateforme
            if (!$this->indexExists('entertainment_views', 'ev_platform_idx'))
                $table->index(['platform'], 'ev_platform_idx');

            // Index pour les spectateurs uniques
            if (!$this->indexExists('entertainment_views', 'ev_user_date_idx'))
                $table->index(['user_id', 'created_at'], 'ev_user_date_idx');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('entertainment_views')) return;

        Schema::table('entertainment_views', function (Blueprint $table) {
            foreach (['ev_partner_date_idx','ev_type_date_idx','ev_device_idx','ev_platform_idx','ev_user_date_idx'] as $idx) {
                try { $table->dropIndex($idx); } catch (\Exception $e) {}
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $conn = Schema::getConnection();
        $db   = $conn->getDatabaseName();
        return (bool) $conn->selectOne(
            "SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1",
            [$db, $table, $index]
        );
    }
};
