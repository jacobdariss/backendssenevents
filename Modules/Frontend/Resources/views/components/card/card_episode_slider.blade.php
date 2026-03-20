{{--
    Carte épisode légère pour le slider HomepageBuilder
    Variable : $value (array depuis HomepageSectionDataService::loadEpisodes)
--}}
<div class="slick-item">
    <div class="iq-card card-hover entainment-slick-card">

        <div class="block-images position-relative w-100">
            <a href="{{ $value['slug'] ? route('episode-details', ['id' => $value['slug']]) : (isset($value['show_slug']) && $value['show_slug'] ? route('tvshow-details', ['id' => $value['show_slug']]) : '#') }}"
               class="position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100"></a>

            <div class="image-box w-100 position-relative">
                <img src="{{ $value['poster_image'] ?? asset('images/no-image.jpg') }}"
                     alt="{{ $value['name'] }}"
                     class="img-fluid object-cover w-100 d-block border-0" loading="lazy">

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

                {{-- Badge numéro épisode + saison --}}
                @if(!empty($value['episode_number']) || !empty($value['season_label']))
                    <span class="badge bg-dark bg-opacity-75 position-absolute bottom-0 start-0 m-1 small">
                        @if(!empty($value['season_label'])){{ $value['season_label'] }} @endif
                        @if(!empty($value['episode_number']))· Ep.{{ $value['episode_number'] }}@endif
                    </span>
                @endif
            </div>
        </div>

        <div class="card-body p-2">
            {{-- Badge épisode uniquement, sans titre répété --}}
        </div>

    </div>
</div>
