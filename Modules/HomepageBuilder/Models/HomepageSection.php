<?php

namespace Modules\HomepageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class HomepageSection extends Model
{
    protected $table = 'homepage_sections';

    protected $fillable = [
        'slug', 'name', 'type', 'content_type',
        'position', 'is_active', 'platform',
        'content_ids', 'content_limit', 'sort_by', 'settings',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'content_ids'   => 'array',
        'settings'      => 'array',
        'position'      => 'integer',
        'content_limit' => 'integer',
    ];

    const CACHE_KEY     = 'homepage_sections';
    const CACHE_MINUTES = 60;

    public static function getActive(string $platform = 'web'): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::CACHE_KEY . '_' . $platform, self::CACHE_MINUTES * 60, function () use ($platform) {
            return self::where('is_active', true)
                ->where(function ($q) use ($platform) {
                    $q->where('platform', $platform)->orWhere('platform', 'both');
                })
                ->orderBy('position')
                ->get();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY . '_web');
        Cache::forget(self::CACHE_KEY . '_mobile');
        Cache::forget(self::CACHE_KEY . '_both');
        Cache::forget(self::CACHE_KEY . '_all');
    }

    public static function types(): array
    {
        return [
            'entertainment'     => 'Films / Séries',
            'video'             => 'Vidéos',
            'livetv'            => 'TV en direct',
            'genre'             => 'Genres',
            'banner'            => 'Bannière',
            'personality'       => 'Personnalités',
            'language'          => 'Langues',
            'payperview'        => 'Pay Per View',
            'continue_watching' => 'Continuer à regarder',
        ];
    }

    public static function contentTypes(): array
    {
        return [
            ''        => '— Automatique —',
            'movie'   => 'Films',
            'tvshow'  => 'Séries TV',
            'video'   => 'Vidéos courtes',
            'channel' => 'Chaînes Live',
        ];
    }

    public static function sortOptions(): array
    {
        return [
            'created_at'   => "Date d'ajout (plus récent)",
            'release_date' => 'Date de sortie',
            'views'        => 'Vues',
            'likes'        => 'Likes',
        ];
    }
}
