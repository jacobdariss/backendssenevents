{{--
    Carte épisode légère pour le slider HomepageBuilder — avec hover preview
    Variable : $value (array depuis HomepageSectionDataService::loadEpisodes)
--}}
@php
    $episodeUrl = $value['slug']
        ? route('episode-details', ['id' => $value['slug']])
        : (isset($value['show_slug']) && $value['show_slug']
            ? route('tvshow-details', ['id' => $value['show_slug']])
            : '#');

    $hoverData = [
        'id'               => $value['id'],
        'name'             => $value['name'] ?? '',
        'slug'             => $value['slug'] ?? '',
        'episode_slug'     => $value['slug'] ?? '',
        'poster_image'     => $value['poster_image'] ?? asset('images/no-image.jpg'),
        'duration'         => $value['duration'] ?? null,
        'description'      => $value['show_name'] ?? '',
        'type'             => 'episode',
        'access'           => $value['access'] ?? 'free',
        'is_pay_per_view'  => ($value['access'] ?? '') === 'pay-per-view',
        'is_purchased'     => \Modules\Entertainment\Models\Entertainment::isPurchased($value['id'], 'episode'),
        'imdb_rating'      => $value['imdb_rating'] ?? null,
        'release_date'     => $value['release_date'] ?? null,
        'episode_number'   => $value['episode_number'] ?? null,
        'entertainment_id' => $value['entertainment_id'] ?? null,
        'trailer_url'      => $value['trailer_url'] ?? '',
        'trailer_url_type' => $value['trailer_url_type'] ?? '',
    ];
@endphp
<div class="slick-item">
    <div class="iq-card card-hover entainment-slick-card"
         data-movie-id="{{ $value['id'] }}"
         data-movie-data="{{ json_encode($hoverData) }}"
         onmouseenter="openHoverModal(this)"
         onmouseleave="closeHoverModal(this)">

        <div class="block-images position-relative w-100"
             data-trailer-url="{{ $value['trailer_url'] ?? '' }}"
             data-trailer-type="{{ $value['trailer_url_type'] ?? '' }}">

            <a href="{{ $episodeUrl }}"
               class="position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100" style="z-index:1;"></a>

            <div class="image-box w-100 position-relative">
                <img src="{{ $value['poster_image'] ?? asset('images/no-image.jpg') }}"
                     alt="{{ $value['name'] }}"
                     class="img-fluid object-cover w-100 d-block border-0" loading="lazy">

                <div class="trailer-preview position-absolute top-0 start-0 w-100 h-100"></div>

                @if(($value['access'] ?? '') === 'pay-per-view')
                    <span class="product-rent">
                        <i class="ph ph-film-reel"></i>
                        {{ \Modules\Entertainment\Models\Entertainment::isPurchased($value['id'], 'episode') ? __('messages.rented') : __('messages.rent') }}
                    </span>
                @endif

                @if(!empty($value['imdb_rating']))
                    <span class="ratting-value">
                        <i class="ph ph-star"></i> {{ $value['imdb_rating'] }}
                    </span>
                @endif

                @if(!empty($value['episode_number']) || !empty($value['season_label']))
                    <span class="badge bg-dark bg-opacity-75 position-absolute bottom-0 start-0 m-1 small">
                        @if(!empty($value['season_label'])){{ $value['season_label'] }} @endif
                        @if(!empty($value['episode_number']))· Ep.{{ $value['episode_number'] }}@endif
                    </span>
                @endif
            </div>
        </div>

        <div class="card-body p-2"></div>

    </div>
</div>
