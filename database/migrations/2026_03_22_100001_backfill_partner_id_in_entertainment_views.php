<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill partner_id depuis entertainments pour les vues existantes
        DB::statement("
            UPDATE entertainment_views ev
            INNER JOIN entertainments e ON e.id = ev.entertainment_id
            SET ev.partner_id = e.partner_id
            WHERE ev.partner_id IS NULL
              AND ev.entertainment_id IS NOT NULL
              AND e.partner_id IS NOT NULL
        ");

        // Backfill depuis episodes pour les vues avec episode_id
        DB::statement("
            UPDATE entertainment_views ev
            INNER JOIN episodes ep ON ep.id = ev.episode_id
            SET ev.partner_id = ep.partner_id
            WHERE ev.partner_id IS NULL
              AND ev.episode_id IS NOT NULL
              AND ep.partner_id IS NOT NULL
        ");

        // Backfill depuis videos pour les vues avec video_id
        DB::statement("
            UPDATE entertainment_views ev
            INNER JOIN videos v ON v.id = ev.video_id
            SET ev.partner_id = v.partner_id
            WHERE ev.partner_id IS NULL
              AND ev.video_id IS NOT NULL
              AND v.partner_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        // Non réversible — ne pas effacer les partner_id
    }
};
