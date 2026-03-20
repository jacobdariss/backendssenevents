{{--
    Section Homepage : slider d'épisodes sélectionnés manuellement
    Variables : $data (array), $title, $slug, $orientation
--}}
@php
    $orientationClass = (!empty($orientation) && $orientation === 'horizontal') ? 'cards-horizontal' : '';
    $slickClass       = 'slick-episode-homepage-' . $slug;
@endphp

<div class="streamit-block {{ $orientationClass }}">
    <div class="d-flex align-items-center justify-content-between my-2 me-2">
        <h5 class="main-title text-capitalize mb-0">{{ $title }}</h5>
    </div>

    <div class="card-style-slider {{ count($data) <= 6 ? 'slide-data-less' : '' }}">
        <div class="slick-general {{ $slickClass }}"
             data-items="6.5" data-items-desktop="5.5" data-items-laptop="4.5"
             data-items-tab="3.5" data-items-mobile-sm="3.5" data-items-mobile="2.5"
             data-speed="1000" data-autoplay="false" data-center="false"
             data-infinite="false" data-navigation="true" data-pagination="false"
             data-spacing="12">

            @foreach($data as $value)
                @include('frontend::components.card.card_episode_slider', ['value' => $value])
            @endforeach

        </div>
    </div>
</div>
