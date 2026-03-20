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
                $table->string('type');            // movie, tvshow, video, channel, genre, banner, personality, custom
                $table->string('platform')->default('both'); // web, mobile, both
                $table->boolean('enabled')->default(true);
                $table->unsignedInteger('position')->default(0);
                $table->json('config')->nullable();  // IDs sélectionnés, count, paramètres
                $table->string('component')->nullable(); // nom du composant blade
                $table->string('view_all_url')->nullable();
                $table->timestamps();

                $table->index(['platform', 'enabled', 'position']);
            });
        }

        // Seeder des sections par défaut
        $sections = [
            ['slug'=>'banner',            'name'=>'Bannière Principale',    'type'=>'banner',       'component'=>'banner',         'position'=>1,  'config'=>null],
            ['slug'=>'continue-watching', 'name'=>'Continuer à Regarder',  'type'=>'special',      'component'=>'continue_watch', 'position'=>2,  'config'=>null],
            ['slug'=>'top-10',            'name'=>'Top 10',                 'type'=>'movie',        'component'=>'top_10_movie',   'position'=>3,  'config'=>null],
            ['slug'=>'latest-movies',     'name'=>'Derniers Films',         'type'=>'movie',        'component'=>'entertainment',  'position'=>4,  'config'=>null],
            ['slug'=>'pay-per-view',      'name'=>'Pay Per View',           'type'=>'special',      'component'=>'payperview',     'position'=>5,  'config'=>null],
            ['slug'=>'popular-language',  'name'=>'Langues Populaires',     'type'=>'language',     'component'=>'language',       'position'=>6,  'config'=>null],
            ['slug'=>'popular-movies',    'name'=>'Films Populaires',       'type'=>'movie',        'component'=>'entertainment',  'position'=>7,  'config'=>null],
            ['slug'=>'popular-tvshows',   'name'=>'Séries Populaires',      'type'=>'tvshow',       'component'=>'entertainment',  'position'=>8,  'config'=>null],
            ['slug'=>'top-channels',      'name'=>'Chaînes TV en Direct',   'type'=>'channel',      'component'=>'tvchannel',      'position'=>9,  'config'=>null],
            ['slug'=>'popular-videos',    'name'=>'Vidéos Populaires',      'type'=>'video',        'component'=>'video',          'position'=>10, 'config'=>null],
            ['slug'=>'latest-videos',     'name'=>'Dernières Vidéos',       'type'=>'video',        'component'=>'video',          'position'=>11, 'config'=>json_encode(['count'=>10])],
            ['slug'=>'genre',             'name'=>'Genres',                 'type'=>'genre',        'component'=>'geners',         'position'=>12, 'config'=>null],
            ['slug'=>'free-movies',       'name'=>'Films Gratuits',         'type'=>'movie',        'component'=>'entertainment',  'position'=>13, 'config'=>null],
            ['slug'=>'personalities',     'name'=>'Personnalités',          'type'=>'personality',  'component'=>'castcrew',       'position'=>14, 'config'=>null],
            ['slug'=>'trending-movies',   'name'=>'Tendances',              'type'=>'movie',        'component'=>'entertainment',  'position'=>15, 'config'=>null],
            ['slug'=>'liked-movies',      'name'=>'Films Likés',            'type'=>'movie',        'component'=>'entertainment',  'position'=>16, 'config'=>null],
        ];

        foreach ($sections as $s) {
            if (!DB::table('homepage_sections')->where('slug', $s['slug'])->exists()) {
                DB::table('homepage_sections')->insert(array_merge($s, [
                    'platform'   => 'both',
                    'enabled'    => true,
                    'view_all_url' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
