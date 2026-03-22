@php $orientationClass = (!empty($orientation) && $orientation === "horizontal") ? "cards-horizontal" : ""; @endphp
<div class="streamit-block {{ $orientationClass }} {{ $presentClass ?? '' }}">
    <div class="d-flex align-items-center justify-content-between my-2 me-2">
        <h5 class="main-title text-capitalize mb-0">{{ $title }}</h5>

        @php
            $showViewAll = false;
            $viewAllRoute = route('videos');

            // Map slug to content list route
            if (!empty($slug) && $slug === 'popular_video') {
                $viewAllRoute = route('content.list', ['type' => 'most-watched-videos']);
                // For most-watched videos, show View All only if we have 9+ items
                $showViewAll = count($data) >= 9;
            } elseif (!empty($slug)) {
                // Custom video sections
                $viewAllRoute = route('custom-section', ['slug' => $slug]);
                $showViewAll = count($data) > 9;
            } else {
                // Other video sections: show View All when at least one item exists
                $showViewAll = count($data) > 0;
            }
        @endphp

        @if ($showViewAll)
            <a href="{{ $viewAllRoute }}" class="view-all-button text-decoration-none flex-none">
                <span>{{ __('frontend.view_all') }}</span>
                <i class="ph ph-caret-right"></i>
            </a>
        @endif
    </div>
      <div class="card-style-slider {{ count($data) <= 6 ? 'slide-data-less' : '' }}">
          @php
              $ipr = $itemsPerRow ?? 5;
              $diDesktop = $ipr - 0.5;
              $diLaptop  = min($ipr - 1, 4);
              $diTab     = min($ipr - 1.5, 3.5);
              $diMobSm   = min($ipr - 1.5, 3.5);
              $diMob     = 2.5;
          @endphp
          <div class="slick-general slick-general-video-section" data-items="{{ $ipr }}" data-items-desktop="{{ $diDesktop }}"
              data-items-laptop="{{ $diLaptop }}" data-items-tab="{{ $diTab }}" data-items-mobile-sm="{{ $diMobSm }}" data-items-mobile="{{ $diMob }}"
              data-speed="1000" data-autoplay="false" data-center="false" data-infinite="false" data-navigation="true"
              data-pagination="false" data-spacing="12">


              @include('frontend::components.card.card_video', ['values' => $data])


          </div>
      </div>
  </div>
