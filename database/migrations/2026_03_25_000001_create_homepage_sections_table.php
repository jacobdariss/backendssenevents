<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('homepage_sections')) {
            Schema::create('homepage_sections', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('type')->default('entertainment');
                // entertainment, video, livetv, genre, banner, personality, language, payperview, continue_watching
                $table->string('content_type')->nullable();
                // movie, tvshow, video, channel, null (auto)
                $table->integer('position')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('platform')->default('both');
                // web, mobile, both
                $table->json('content_ids')->nullable();
                // IDs sélectionnés manuellement (null = auto)
                $table->integer('content_limit')->default(20);
                // Nb max d'éléments à afficher
                $table->string('sort_by')->default('created_at');
                // created_at, release_date, views, likes
                $table->json('settings')->nullable();
                // Paramètres supplémentaires (style, etc.)
                $table->timestamps();

                $table->index(['platform', 'is_active', 'position']);
            });
        }

        // Insérer les sections par défaut basées sur l'existant
        $sections = [
            ['slug'=>'banner',           'name'=>'Bannière principale',     'type'=>'banner',           'content_type'=>null,      'position'=>1,  'is_active'=>true,  'platform'=>'both', 'content_limit'=>10],
            ['slug'=>'continue-watching','name'=>'Continuer à regarder',    'type'=>'continue_watching','content_type'=>null,      'position'=>2,  'is_active'=>true,  'platform'=>'both', 'content_limit'=>20],
            ['slug'=>'top-10',           'name'=>'Top 10',                  'type'=>'entertainment',    'content_type'=>'movie',   'position'=>3,  'is_active'=>true,  'platform'=>'both', 'content_limit'=>10],
            ['slug'=>'latest-movies',    'name'=>'Derniers Films',          'type'=>'entertainment',    'content_type'=>'movie',   'position'=>4,  'is_active'=>true,  'platform'=>'both', 'content_limit'=>20],
            ['slug'=>'payperview',       'name'=>'Pay Per View',            'type'=>'payperview',       'content_type'=>null,      'position'=>5,  'is_active'=>true,  'platform'=>'both', 'content_limit'=>20],
            ['slug'=>'popular-movies',   'name'=>'Films Populaires',        'type'=>'entertainment',    'content_type'=>'movie',   'position'=>6,  'is_active'=>true,  'platform'=>'both', 'content_limit'=>20],
            ['slug'=>'popular-tvshows',  'name'=>'Séries Populaires',       'type'=>'entertainment',    'content_type'=>'tvshow',  'position'=>7,  'is_active'=>true,  'platform'=>'both', 'content_limit'=>20],
            ['slug'=>'latest-videos',    'name'=>'Dernières Vidéos',        'type'=>'video',            'content_type'=>'video',   'position'=>8,  'is_active'=>true,  'platform'=>'both', 'content_limit'=>12],
            ['slug'=>'top-channels',     'name'=>'Chaînes Live',            'type'=>'livetv',           'content_type'=>'channel', 'position'=>9,  'is_active'=>true,  'platform'=>'both', 'content_limit'=>10],
            ['slug'=>'genre',            'name'=>'Genres',                  'type'=>'genre',            'content_type'=>null,      'position'=>10, 'is_active'=>true,  'platform'=>'both', 'content_limit'=>12],
            ['slug'=>'popular-videos',   'name'=>'Vidéos Populaires',       'type'=>'video',            'content_type'=>'video',   'position'=>11, 'is_active'=>true,  'platform'=>'both', 'content_limit'=>20],
            ['slug'=>'personalities',    'name'=>'Personnalités',           'type'=>'personality',      'content_type'=>null,      'position'=>12, 'is_active'=>true,  'platform'=>'both', 'content_limit'=>10],
            ['slug'=>'free-movies',      'name'=>'Films Gratuits',          'type'=>'entertainment',    'content_type'=>'movie',   'position'=>13, 'is_active'=>true,  'platform'=>'both', 'content_limit'=>20],
            ['slug'=>'language',         'name'=>'Par Langue',              'type'=>'language',         'content_type'=>null,      'position'=>14, 'is_active'=>false, 'platform'=>'both', 'content_limit'=>10],
            ['slug'=>'trending-movies',  'name'=>'Tendances',               'type'=>'entertainment',    'content_type'=>'movie',   'position'=>15, 'is_active'=>true,  'platform'=>'both', 'content_limit'=>20],
            ['slug'=>'liked-movies',     'name'=>'Films Aimés',             'type'=>'entertainment',    'content_type'=>'movie',   'position'=>16, 'is_active'=>true,  'platform'=>'both', 'content_limit'=>20],
        ];

        foreach ($sections as $section) {
            DB::table('homepage_sections')->insertOrIgnore(array_merge($section, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
