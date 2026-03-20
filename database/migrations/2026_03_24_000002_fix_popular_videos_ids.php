<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // La section popular-videos peut contenir des IDs d'entertainments (films)
        // au lieu d'IDs de la table videos — conflit historique.
        // On remet la valeur à null pour forcer l'admin à resélectionner les bonnes vidéos.
        $setting = DB::table('mobile_settings')
            ->where('slug', 'popular-videos')
            ->whereNull('type') // Slug fixe, pas section custom
            ->first();

        if (!$setting || empty($setting->value)) return;

        $ids = json_decode($setting->value, true);
        if (!is_array($ids) || empty($ids)) return;

        // Vérifier si ces IDs existent dans la table videos
        $videoCount = DB::table('videos')->whereIn('id', $ids)->count();

        // Si aucun ID ne correspond à une vraie video → nettoyer
        if ($videoCount === 0) {
            DB::table('mobile_settings')
                ->where('slug', 'popular-videos')
                ->whereNull('type')
                ->update(['value' => null]);

            // Vider le cache setting pour que l'API mobile reflète le changement
            \Illuminate\Support\Facades\Cache::forget('setting');
        }
    }

    public function down(): void {}
};
