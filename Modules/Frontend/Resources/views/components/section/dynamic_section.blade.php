{{--
    Composant générique pour les sections dynamiques HomepageBuilder
    Variables : $section (HomepageSection), $cachedResult (array), $user_id
--}}
@php
    $slug        = $section->slug;
    $name        = $section->name;
    $type        = $section->type;
    $ct          = $section->content_type;
    $orientation = $section->card_orientation ?? 'vertical';

    // Mapper le slug vers la clé cachedResult existante
    $slugToKey = [
        'banner'            => 'sliders',
        'continue-watching' => 'continue_watch',
        'top-10'            => 'top_10',
        'latest-movies'     => 'latest_movie',
        'payperview'        => 'payperview',
        'popular-movies'    => 'popular_movie',
        'popular-tvshows'   => 'popular_tvshow',
        'latest-videos'     => 'latest_videos',
        'popular-videos'    => 'popular_video',
        'top-channels'      => 'top-channels',
        'genre'             => 'genre',
        'personalities'     => 'popular_personality',
        'free-movies'       => 'free_movie',
        'language'          => 'popular_language',
        'trending-movies'   => 'trending_movie',
        'liked-movies'      => 'liked_movie',
    ];

    // Priorité 1 : données chargées directement par HomepageSectionDataService (indépendant de MobileSetting)
    // Priorité 2 : fallback sur cachedResult (pour banner, payperview, continue_watching, top_10, etc.)
    $directData = $section->_direct_data ?? null;

    if ($directData !== null) {
        $data = $directData;
    } else {
        $cacheKey = $slugToKey[$slug] ?? null;
        $data     = $cacheKey && isset($cachedResult[$cacheKey])
                    ? $cachedResult[$cacheKey]
                    : ($cachedResult[$slug] ?? null);
    }

    $sectionData = isset($data['data']) ? $data['data'] : (is_array($data) && !isset($data[0]) ? [] : ($data ?? []));
    $sectionName = isset($data['name']) ? $data['name'] : $name;

    $hasData = !empty($sectionData);
@endphp

@if($type === 'banner')
    @if(App\Models\MobileSetting::getCacheValueBySlug('banner') == 1 && isset($cachedResult['sliders']) && count($cachedResult['sliders']) > 0)
    <div id="banner-section" class="banner-section px-0">
        @include('frontend::components.section.banner', ['data' => $cachedResult['sliders']])
    </div>
    @endif

@elseif($type === 'continue_watching')
    @if($user_id && isset($cachedResult['continue_watch']) && count($cachedResult['continue_watch']) > 0)
    <div id="continue-watch-section" class="section-wraper scroll-section section-hidden">
        @include('frontend::components.section.continue_watch', ['continuewatchData' => $cachedResult['continue_watch']])
    </div>
    @endif

@elseif($type === 'entertainment' && $hasData)
    @if(($ct === 'movie' && isenablemodule('movie') == 1) || ($ct === 'tvshow' && isenablemodule('tvshow') == 1) || empty($ct))
    <div id="{{ $slug }}-section" class="section-wraper scroll-section section-hidden">
        @include('frontend::components.section.entertainment', [
            'data'        => $sectionData,
            'title'       => $sectionName,
            'type'        => $ct ?: 'movie',
            'slug'        => str_replace('-', '_', $slug),
            'orientation' => $orientation,
        ])
    </div>
    @endif

@elseif($type === 'video' && $hasData)
    @if(isenablemodule('video') == 1)
    <div id="{{ $slug }}-section" class="section-wraper scroll-section section-hidden">
        @include('frontend::components.section.video', [
            'data'        => $sectionData,
            'title'       => $sectionName,
            'type'        => 'video',
            'slug'        => str_replace('-', '_', $slug),
            'orientation' => $orientation,
        ])
    </div>
    @endif

@elseif($type === 'livetv' && $hasData)
    @if(isenablemodule('livetv') == 1)
    <div id="{{ $slug }}-section" class="section-wraper scroll-section section-hidden">
        @include('frontend::components.section.tvchannel', [
            'top_channel' => isset($sectionData['data']) ? $sectionData['data'] : $sectionData,
            'title'       => $sectionName,
        ])
    </div>
    @endif

@elseif($type === 'genre' && $hasData)
    <div id="{{ $slug }}-section" class="section-wraper scroll-section section-hidden">
        @include('frontend::components.section.geners', [
            'genres' => isset($sectionData['data']) ? $sectionData['data'] : $sectionData,
            'title'  => $sectionName,
            'slug'   => $slug,
        ])
    </div>

@elseif($type === 'personality' && $hasData)
    <div id="{{ $slug }}-section" class="section-wraper scroll-section section-hidden">
        @include('frontend::components.section.castcrew', [
            'data'  => $sectionData,
            'title' => $sectionName,
            'slug'  => $slug,
        ])
    </div>

@elseif($type === 'language' && $hasData)
    <div id="{{ $slug }}-section" class="section-wraper scroll-section section-hidden">
        @include('frontend::components.section.language', [
            'popular_language' => isset($sectionData['data']) ? $sectionData['data'] : $sectionData,
            'title'            => $sectionName,
        ])
    </div>

@elseif($type === 'payperview' && $hasData)
    <div id="{{ $slug }}-section" class="section-wraper scroll-section section-hidden">
        @include('frontend::components.section.payperview', [
            'data'  => $sectionData,
            'title' => $sectionName,
        ])
    </div>
@endif
