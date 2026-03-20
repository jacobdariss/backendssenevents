<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insérer le slug latest-videos s'il n'existe pas déjà
        $exists = DB::table('mobile_settings')->where('slug', 'latest-videos')->exists();
        if (!$exists) {
            $maxPosition = (int) DB::table('mobile_settings')->max('position');
            DB::table('mobile_settings')->insert([
                'name'       => 'Dernières Vidéos',
                'slug'       => 'latest-videos',
                'position'   => $maxPosition + 1,
                'value'      => '10',  // 10 vidéos par défaut
                'type'       => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Vider le cache setting
        \Illuminate\Support\Facades\Cache::forget('setting');
    }

    public function down(): void
    {
        DB::table('mobile_settings')->where('slug', 'latest-videos')->delete();
        \Illuminate\Support\Facades\Cache::forget('setting');
    }
};
