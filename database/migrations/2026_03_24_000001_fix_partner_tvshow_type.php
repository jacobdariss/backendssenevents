<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Corriger les séries partenaires créées avec type='tv_show' au lieu de 'tvshow'
        // Le type standard dans toute la plateforme est 'tvshow'
        DB::table('entertainments')
            ->where('type', 'tv_show')
            ->whereNotNull('partner_id')
            ->update(['type' => 'tvshow']);

        // Corriger aussi les saisons et épisodes liés si nécessaire
        // (les saisons n'ont pas de type mais les épisodes non plus)
    }

    public function down(): void
    {
        // Pas de rollback — tvshow est le type correct
    }
};
