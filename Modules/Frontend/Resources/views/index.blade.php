@extends('frontend::layouts.master')

@section('title')
    {{ __('frontend.home') }}
@endsection

@section('content')

{{-- ── Sections dynamiques HomepageBuilder ────────────────────────────────── --}}
@foreach($homepageSections as $section)
    @include('frontend::components.section.dynamic_section', [
        'section'     => $section,
        'cachedResult'=> $cachedResult,
        'user_id'     => $user_id,
    ])
@endforeach

{{-- ── Sections personnalisées (custom sections ajoutées via MobileSetting) ── --}}
@if(isset($cachedResult['dynamic_data']) && count($cachedResult['dynamic_data']) > 0)
    @foreach($cachedResult['dynamic_data'] as $key => $dynamic_data)
        @if($dynamic_data['type'] == 'movie' && isenablemodule('movie') == 1)
            <div id="{{ $key }}-section" class="section-wraper scroll-section section-hidden">
                @if(isset($dynamic_data['data']) && count($dynamic_data['data']) > 0)
                    @include('frontend::components.section.entertainment', [
                        'data'  => $dynamic_data['data'],
                        'title' => $dynamic_data['name'] ?? __('frontend.latest_movie'),
                        'type'  => 'movie',
                        'slug'  => $key,
                    ])
                @endif
            </div>
        @elseif($dynamic_data['type'] == 'tvshow' && isenablemodule('tvshow') == 1)
            <div id="{{ $key }}-section" class="section-wraper scroll-section section-hidden">
                @if(isset($dynamic_data['data']) && count($dynamic_data['data']) > 0)
                    @include('frontend::components.section.entertainment', [
                        'data'  => $dynamic_data['data'],
                        'title' => $dynamic_data['name'] ?? __('frontend.popular_tvshow'),
                        'type'  => 'tvshow',
                        'slug'  => $key,
                    ])
                @endif
            </div>
        @elseif($dynamic_data['type'] == 'video' && isenablemodule('video') == 1)
            <div id="{{ $key }}-section" class="section-wraper scroll-section section-hidden">
                @if(isset($dynamic_data['data']) && count($dynamic_data['data']) > 0)
                    @include('frontend::components.section.video', [
                        'data'  => $dynamic_data['data'],
                        'title' => $dynamic_data['name'] ?? __('frontend.popular_video'),
                        'type'  => 'video',
                        'slug'  => $key,
                    ])
                @endif
            </div>
        @elseif($dynamic_data['type'] == 'channel' && isenablemodule('livetv') == 1)
            <div id="{{ $key }}-section" class="section-wraper scroll-section section-hidden">
                @if(isset($dynamic_data['data']) && count($dynamic_data['data']) > 0)
                    @include('frontend::components.section.tvchannel', [
                        'top_channel' => $dynamic_data['data'],
                        'title'       => $dynamic_data['name'] ?? __('frontend.top_channels'),
                        'slug'        => $key,
                    ])
                @endif
            </div>
        @endif
    @endforeach
@endif

@endsection

@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.section-hidden').forEach(s => {
        s.classList.remove('section-hidden');
        s.classList.add('section-visible');
    });
});
</script>
@endpush
