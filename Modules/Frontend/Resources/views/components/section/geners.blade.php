<div class="movie-geners-block {{ $presentClass ?? '' }}">
   <div class="d-flex align-items-center justify-content-between my-2 me-2">
         <h5 class="main-title text-capitalize mb-0">{{ $title }}</h5>
         <a href="{{route('genresList')}}" class="view-all-button text-decoration-none flex-none"><span>{{__('frontend.view_all')}}</span> <i class="ph ph-caret-right"></i></a>
      </div>

      @php

      $baseClass = 'slick-general';

      if ($slug == 'gener-section') {
          $additionalClass = 'slick-general-gener-section';
      } elseif ($slug == 'favorite-genres') {
          $additionalClass = 'slick-general-favorite-genres';
      } else {
          $additionalClass = '';
      }

      $class = trim("$baseClass $additionalClass");

  @endphp


   <div class="card-style-slider slide-data-less">
      @php
          $ipr = $itemsPerRow ?? 6;
          $diDesktop = $ipr - 0.5;
          $diLaptop  = min($ipr - 1.5, 4.5);
          $diTab     = min($ipr - 2.5, 3.5);
          $diMobSm   = min($ipr - 2.5, 3.5);
          $diMob     = 2.5;
      @endphp
      <div class="{{  $class }} " data-items="{{ $ipr }}" data-items-desktop="{{ $diDesktop }}" data-items-laptop="{{ $diLaptop }}" data-items-tab="{{ $diTab }}" data-items-mobile-sm="{{ $diMobSm }}"
         data-items-mobile="{{ $diMob }}" data-speed="1000" data-autoplay="false" data-center="false" data-infinite="false"
         data-navigation="true" data-pagination="false" data-spacing="12">
         @foreach($genres as $key => $value)
            <div class="swiper-slide">
                  <a href="{{ route('movies.genre', $value['id']) }}" class="text-center genres-card d-block position-relative">
                     <img src="{{ $value['poster_image'] }}" alt="genres img" class="object-cover rounded genres-img">
                     <span class="h6 mb-0 geners-title line-count-1"> {{ $value['name'] }} </span>
                  </a>
            </div>
         @endforeach
      </div>
   </div>
</div>
