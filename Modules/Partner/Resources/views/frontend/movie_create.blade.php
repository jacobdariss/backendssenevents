@extends('backend.layouts.app')

@section('title')
    @if($content_type === 'movie') {{ __('partner::partner.add_movie') }}
    @else {{ __('partner::partner.add_tvshow') }} @endif
@endsection

@section('content')

<x-back-button-component :route="$content_type === 'movie' ? 'partner.movies' : 'partner.tvshows'" />

@if(session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
@endif

{{ html()->form('POST', $content_type === 'movie' ? route('partner.movies.store') : route('partner.tvshows.store'))
    ->attribute('enctype', 'multipart/form-data')->attribute('id', 'form-submit')->open() }}

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ph ph-{{ $content_type === 'movie' ? 'film-strip' : 'monitor-play' }} me-2"></i>
                @if($content_type === 'movie') {{ __('partner::partner.add_movie') }}
                @else {{ __('partner::partner.add_tvshow') }} @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="row gy-3">

                {{-- Thumbnail --}}
                <div class="col-md-4 position-relative">
                    {{ html()->label(__('movie.lbl_thumbnail'), 'thumbnail')->class('form-label') }}
                    <div class="input-group btn-file-upload">
                        {{ html()->button('<i class="ph ph-image"></i> ' . __('messages.lbl_choose_image'))->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerThumbnail')->attribute('data-hidden-input', 'file_url_thumbnail') }}
                        {{ html()->text('thumbnail_input')->class('form-control')->placeholder(__('placeholder.lbl_image'))->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerThumbnail') }}
                    </div>
                    <div class="uploaded-image" id="selectedImageContainerThumbnail"></div>
                    {{ html()->hidden('thumbnail_url')->id('file_url_thumbnail')->value(old('thumbnail_url')) }}
                </div>

                {{-- Poster --}}
                <div class="col-md-4 position-relative">
                    {{ html()->label(__('movie.lbl_poster'), 'poster')->class('form-label') }}
                    <div class="input-group btn-file-upload">
                        {{ html()->button('<i class="ph ph-image"></i> ' . __('messages.lbl_choose_image'))->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPoster')->attribute('data-hidden-input', 'file_url_poster') }}
                        {{ html()->text('poster_input')->class('form-control')->placeholder(__('placeholder.lbl_image'))->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPoster') }}
                    </div>
                    <div class="uploaded-image" id="selectedImageContainerPoster"></div>
                    {{ html()->hidden('poster_url')->id('file_url_poster')->value(old('poster_url')) }}
                </div>

                {{-- Poster TV --}}
                <div class="col-md-4 position-relative">
                    {{ html()->label(__('movie.lbl_poster_tv'), 'poster_tv')->class('form-label') }}
                    <div class="input-group btn-file-upload">
                        {{ html()->button('<i class="ph ph-image"></i> ' . __('messages.lbl_choose_image'))->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPosterTv')->attribute('data-hidden-input', 'file_url_poster_tv') }}
                        {{ html()->text('poster_tv_input')->class('form-control')->placeholder(__('placeholder.lbl_image'))->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPosterTv') }}
                    </div>
                    <div class="uploaded-image" id="selectedImageContainerPosterTv"></div>
                    {{ html()->hidden('poster_tv_url')->id('file_url_poster_tv')->value(old('poster_tv_url')) }}
                </div>

                {{-- Title --}}
                <div class="col-md-6">
                    {{ html()->label(__('movie.lbl_name') . ' <span class="text-danger">*</span>', 'name')->class('form-label') }}
                    {{ html()->text('name', old('name'))->class('form-control')->placeholder(__('placeholder.lbl_movie_name'))->attribute('required') }}
                    @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                {{-- Release date --}}
                <div class="col-md-3">
                    {{ html()->label(__('movie.lbl_release_date') . ' <span class="text-danger">*</span>', 'release_date')->class('form-label') }}
                    {{ html()->date('release_date', old('release_date'))->class('form-control')->attribute('required') }}
                    @error('release_date')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                {{-- Duration (Films uniquement) --}}
                @if($content_type === 'movie')
                <div class="col-md-3">
                    {{ html()->label(__('movie.lbl_duration'), 'duration')->class('form-label') }}
                    {{ html()->text('duration', old('duration'))->class('form-control')->placeholder('01:45:00') }}
                </div>
                @endif

                {{-- Access --}}
                <div class="col-md-6">
                    {{ html()->label(__('movie.lbl_movie_access'), 'movie_access')->class('form-label') }}
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <label class="form-check form-check-inline form-control cursor-pointer w-auto m-0">
                            <div>
                                <input class="form-check-input" type="radio" name="movie_access" value="free"
                                    onchange="togglePpvFields(this.value)"
                                    {{ old('movie_access', 'free') == 'free' ? 'checked' : '' }}>
                                <span class="form-check-label">{{ __('movie.lbl_free') }}</span>
                            </div>
                        </label>
                        <label class="form-check form-check-inline form-control cursor-pointer w-auto m-0">
                            <div>
                                <input class="form-check-input" type="radio" name="movie_access" value="pay-per-view"
                                    onchange="togglePpvFields(this.value)"
                                    {{ old('movie_access') == 'pay-per-view' ? 'checked' : '' }}>
                                <span class="form-check-label">{{ __('messages.lbl_pay_per_view') }}</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- PPV Fields --}}
                <div class="col-12 {{ old('movie_access') == 'pay-per-view' ? '' : 'd-none' }}" id="ppv_fields">
                    <div class="row g-3">
                        <div class="col-md-3">
                            {{ html()->label(__('messages.lbl_price') . ' <span class="text-danger">*</span>', 'price')->class('form-label') }}
                            <div class="input-group">
                                <span class="input-group-text">FCFA</span>
                                {{ html()->number('price', old('price'))->class('form-control')->attribute('step', '0.01')->attribute('min', 0)->id('price') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            {{ html()->label(__('messages.purchase_type'), 'purchase_type')->class('form-label') }}
                            {{ html()->select('purchase_type', ['rental' => __('messages.lbl_rental'), 'onetime' => __('messages.lbl_one_time_purchase')], old('purchase_type', 'rental'))->class('form-control select2')->attribute('onchange', 'toggleAccessDurationPartner(this.value)') }}
                        </div>
                        <div class="col-md-3" id="access_duration_wrapper_partner">
                            {{ html()->label(__('messages.lbl_access_duration') . ' (jours)', 'access_duration')->class('form-label') }}
                            {{ html()->number('access_duration', old('access_duration'))->class('form-control')->attribute('min', 1)->attribute('placeholder', '7') }}
                        </div>
                        <div class="col-md-3">
                            {{ html()->label(__('messages.lbl_available_for') . ' (jours)', 'available_for')->class('form-label') }}
                            {{ html()->number('available_for', old('available_for'))->class('form-control')->attribute('min', 1)->attribute('placeholder', '30') }}
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info py-2 mb-0 small">
                                <i class="ph ph-info me-1"></i>{{ __('partner::partner.ppv_price_info') }}
                            </div>
                        </div>
                    </div>
                </div>

                @if($content_type === 'movie')
                {{-- Upload type + URL — Films uniquement --}}
                <div class="col-12"><div class="row g-3">
                <div class="col-md-4">
                    {{ html()->label(__('movie.lbl_video_upload_type') . ' <span class="text-danger">*</span>', 'video_upload_type')->class('form-label') }}
                    {{ html()->select('video_upload_type', $upload_url_type->pluck('name', 'name')->prepend(__('placeholder.lbl_select_video_type'), '')->merge(['Embedded' => 'Embedded']), old('video_upload_type', ''))->class('form-control select2')->id('video_upload_type')->attribute('required') }}
                    @error('video_upload_type')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-8">
                    <div class="d-none" id="embed_code_input_section">
                        {{ html()->label(__('movie.lbl_embed_code'), 'embed_code')->class('form-label') }}
                        {{ html()->textarea('embed_code', old('embed_code'))->class('form-control')->id('embed_code')->placeholder('<iframe ...></iframe>') }}
                    </div>
                    <div class="d-none" id="video_url_input_section">
                        {{ html()->label(__('movie.video_url_input') . ' <span class="text-danger">*</span>', 'video_url_input')->class('form-label') }}
                        {{ html()->text('video_url_input', old('video_url_input'))->class('form-control')->id('video_url_input')->placeholder(__('placeholder.video_url_input')) }}
                    </div>
                    <div class="d-none" id="video_file_input_section">
                        {{ html()->label(__('movie.video_file_input'), 'video_file')->class('form-label') }}
                        <div class="input-group btn-video-link-upload">
                            {{ html()->button(__('placeholder.lbl_select_file') . '<i class="ph ph-upload"></i>')->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerVideourl')->attribute('data-hidden-input', 'file_url_video') }}
                            {{ html()->text('video_file_input')->class('form-control')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerVideourl')->attribute('data-hidden-input', 'file_url_video') }}
                        </div>
                        <div class="mt-2" id="selectedImageContainerVideourl"></div>
                        {{ html()->hidden('video_url_input')->id('file_url_video') }}
                    </div>
                </div>
                </div></div>
                @endif

                {{-- Trailer --}}
                <div class="col-12"><div class="row g-3">
                <div class="col-md-4">
                    {{ html()->label(__('movie.lbl_trailer_url_type'), 'trailer_url_type')->class('form-label') }}
                    {{ html()->select('trailer_url_type', $upload_url_type->pluck('name', 'name')->prepend(__('placeholder.lbl_select_type'), ''), old('trailer_url_type', ''))->class('form-control select2')->id('trailer_url_type') }}
                </div>
                <div class="col-md-8">
                    <div id="url_input">
                        {{ html()->label(__('movie.lbl_trailer_url'), 'trailer_url')->class('form-label') }}
                        {{ html()->text('trailer_url', old('trailer_url'))->class('form-control')->id('trailer_url')->placeholder(__('placeholder.lbl_trailer_url')) }}
                    </div>
                    <div id="url_file_input" class="d-none">
                        {{ html()->label(__('movie.lbl_trailer_video'), 'trailer_video')->class('form-label') }}
                        <div class="input-group btn-video-link-upload">
                            {{ html()->button(__('placeholder.lbl_select_file') . '<i class="ph ph-upload"></i>')->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainertailerurl')->attribute('data-hidden-input', 'file_url_trailer') }}
                            {{ html()->text('trailer_input')->class('form-control')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainertailerurl')->attribute('data-hidden-input', 'file_url_trailer') }}
                        </div>
                        {{ html()->hidden('trailer_url')->id('file_url_trailer') }}
                    </div>
                    <div id="trailer_embed_input_section" class="d-none">
                        {{ html()->label(__('movie.lbl_embed_code'), 'trailer_embedded')->class('form-label') }}
                        {{ html()->textarea('trailer_embedded', old('trailer_embedded'))->class('form-control')->id('trailer_embedded')->placeholder('<iframe ...></iframe>') }}
                    </div>
                </div>
                </div></div>

                {{-- Description --}}
                <div class="col-md-12">
                    {{ html()->label(__('movie.lbl_description') . ' <span class="text-danger">*</span>', 'description')->class('form-label') }}
                    {{ html()->textarea('description', old('description'))->class('form-control')->id('description')->rows(5)->attribute('required') }}
                    @error('description')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ $content_type === 'movie' ? route('partner.movies') : route('partner.tvshows') }}" class="btn btn-secondary me-2">{{ __('messages.cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="ph ph-paper-plane-tilt me-1"></i>{{ __('partner::partner.submit_for_validation') }}
            </button>
        </div>
    </div>

{{ html()->form()->close() }}

@include('components.media-modal', ['page_type' => $page_type, 'partnerFolder' => $partnerFolder])

@endsection

@push('after-scripts')
<script>
function togglePpvFields(val) {
    const ppvDiv = document.getElementById('ppv_fields');
    if (ppvDiv) ppvDiv.classList.toggle('d-none', val !== 'pay-per-view');
    const priceInput = document.getElementById('price');
    if (priceInput) val === 'pay-per-view' ? priceInput.setAttribute('required', '') : priceInput.removeAttribute('required');
}
function toggleAccessDurationPartner(val) {
    const w = document.getElementById('access_duration_wrapper_partner');
    if (w) w.classList.toggle('d-none', val !== 'rental');
}
function handleVideoUrlTypeChange(val) {
    ['video_file_input_section','video_url_input_section','embed_code_input_section'].forEach(id => {
        const el = document.getElementById(id); if(el) el.classList.add('d-none');
    });
    if (val === 'Local') document.getElementById('video_file_input_section')?.classList.remove('d-none');
    else if (val === 'Embedded') document.getElementById('embed_code_input_section')?.classList.remove('d-none');
    else if (val !== '') document.getElementById('video_url_input_section')?.classList.remove('d-none');
}
function handleTrailerUrlTypeChange(val) {
    ['url_input','url_file_input','trailer_embed_input_section'].forEach(id => {
        const el = document.getElementById(id); if(el) el.classList.add('d-none');
    });
    if (val === 'Local') document.getElementById('url_file_input')?.classList.remove('d-none');
    else if (val === 'Embedded') document.getElementById('trailer_embed_input_section')?.classList.remove('d-none');
    else if (val !== '') document.getElementById('url_input')?.classList.remove('d-none');
}
$(document).ready(function () {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({ selector: '#description', plugins: 'link image code', toolbar: 'undo redo | styleselect | bold italic | link | removeformat | code' });
    }
    if ($.fn.select2) { $('.select2').select2({ language: { noResults: () => "{{ __('messages.no_results_found') }}" } }); }
    const ts = $('#video_upload_type'); if(ts.length) { handleVideoUrlTypeChange(ts.val()); ts.on('change select2:select', function(){ handleVideoUrlTypeChange($(this).val()); }); }
    const tr = $('#trailer_url_type'); if(tr.length) { handleTrailerUrlTypeChange(tr.val()); tr.on('change select2:select', function(){ handleTrailerUrlTypeChange($(this).val()); }); }
});
</script>
@endpush
