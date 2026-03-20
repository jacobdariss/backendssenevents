<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class VideoPlayerSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'player_autoplay'           => '0',
            'player_muted_on_load'      => '0',
            'player_continue_watching'  => '1',
            'player_skip_intro'         => '1',
            'player_skip_intro_delay'   => '5',
            'player_default_quality'    => 'auto',
            'player_speed_control'      => '1',
            'player_download_enabled'   => '0',
            'player_subtitles_default'  => '0',
            'player_watermark_position' => 'top-right',
            'player_forward_seconds'    => '10',
        ];

        foreach ($defaults as $key => $value) {
            // Insérer uniquement si absent — ne pas écraser les valeurs existantes
            if (!Setting::where('name', $key)->exists()) {
                Setting::add($key, $value, 'string', null);
            }
        }
    }
}
