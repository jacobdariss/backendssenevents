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

{{-- Sections MobileSetting legacy supprimées — tout passe par HomepageBuilder --}}

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
