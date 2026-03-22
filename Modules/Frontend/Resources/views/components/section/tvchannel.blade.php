<div class="channel-block {{ $presentClass ?? '' }}">
    <div class="d-flex align-items-center justify-content-between my-2 me-2">
        <h5 class="main-title text-capitalize mb-0">{{ $title }}</h5>
        @php
            $showViewAll = false;
            $viewAllRoute = route('topChannelList');

            if (!empty($slug) && $slug !== 'top-channels') {
                $viewAllRoute = route('custom-section', ['slug' => $slug]);
                $showViewAll = count($top_channel) > 9;
            } else {
                $showViewAll = count($top_channel) > 0;
            }
        @endphp
        @if ($showViewAll)
            <a href="{{ $viewAllRoute }}"
                class="view-all-button text-decoration-none flex-none"><span>{{ __('frontend.view_all') }}</span> <i
                    class="ph ph-caret-right"></i></a>
        @endif
    </div>
    <div class="card-style-slider slide-data-less">
        @php
            $ipr = $itemsPerRow ?? 6;
            $diLaptop = min($ipr - 1.5, 4.5);
            $diTab    = min($ipr - 2.5, 3.5);
            $diMobSm  = min($ipr - 2.5, 3.5);
            $diMob    = 2.5;
        @endphp
        <div class="slick-general slick-general-topchannel" data-items="{{ $ipr }}" data-items-laptop="{{ $diLaptop }}" data-items-tab="{{ $diTab }}"
            data-items-mobile-sm="{{ $diMobSm }}" data-items-mobile="{{ $diMob }}" data-speed="1000" data-autoplay="false"
            data-center="false" data-infinite="false" data-navigation="true" data-pagination="false" data-spacing="12">
            @foreach ($top_channel as $data)
                <div class="slick-item">
                    <a href="{{ route('livetv-details', ['id' => $data['slug']]) }}"
                        class="channel-card d-flex align-content-center align-items-center justify-content-center rounded">
                        <img src="{{ $data['poster_image'] }}" alt="channel icon"
                            class="img-fluid object-cover rounded channel-img">
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
