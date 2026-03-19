@extends('backend.layouts.app')
@section('title') {{ __('partner::partner.add_episode') }} @endsection

@section('content')
<x-back-button-component :route="route('partner.tvshow.season.episodes', [$tvshow->id, $season->id])" />

{{ html()->form('POST', route('partner.tvshow.season.episode.store', [$tvshow->id, $season->id]))->attribute('enctype', 'multipart/form-data')->attribute('id', 'form-submit')->open() }}

@if($errors->any())
    <div class="alert alert-danger mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="ph ph-film-strip me-2"></i>{{ __('partner::partner.add_episode') }}
            <span class="text-muted fs-6 ms-2">{{ $tvshow->name }} — S{{ $season->season_number }}</span>
        </h5>
    </div>
    <div class="card-body">
        <div class="row gy-3">

            {{-- Poster --}}
            <div class="col-md-6 position-relative">
                {{ html()->label(__('movie.lbl_poster'), 'poster')->class('form-label') }}
                <div class="input-group btn-file-upload">
                    {{ html()->button('<i class="ph ph-image"></i> ' . __('messages.lbl_choose_image'))->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPoster')->attribute('data-hidden-input', 'file_url_poster') }}
                    {{ html()->text('poster_input')->class('form-control')->placeholder(__('placeholder.lbl_image'))->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPoster') }}
                </div>
                <div class="uploaded-image" id="selectedImageContainerPoster"></div>
                {{ html()->hidden('poster_url')->id('file_url_poster')->value(old('poster_url')) }}
            </div>

            {{-- Poster TV --}}
            <div class="col-md-6 position-relative">
                {{ html()->label(__('movie.lbl_poster_tv'), 'poster_tv')->class('form-label') }}
                <div class="input-group btn-file-upload">
                    {{ html()->button('<i class="ph ph-image"></i> ' . __('messages.lbl_choose_image'))->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPosterTv')->attribute('data-hidden-input', 'file_url_poster_tv') }}
                    {{ html()->text('poster_tv_input')->class('form-control')->placeholder(__('placeholder.lbl_image'))->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPosterTv') }}
                </div>
                <div class="uploaded-image" id="selectedImageContainerPosterTv"></div>
                {{ html()->hidden('poster_tv_url')->id('file_url_poster_tv')->value(old('poster_tv_url')) }}
            </div>

            {{-- Title + Episode number --}}
            <div class="col-md-8">
                {{ html()->label(__('episode.lbl_episode_name') . ' <span class="text-danger">*</span>', 'name')->class('form-label') }}
                {{ html()->text('name', old('name'))->class('form-control')->attribute('required') }}
                @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
            </div>

            <div class="col-md-2">
                {{ html()->label(__('partner::partner.lbl_episode_number'), 'episode_number')->class('form-label') }}
                {{ html()->number('episode_number', old('episode_number', 1))->class('form-control')->attribute('min', 1) }}
            </div>

            <div class="col-md-2">
                {{ html()->label(__('movie.lbl_duration'), 'duration')->class('form-label') }}
                {{ html()->text('duration', old('duration'))->class('form-control')->placeholder('00:45:00') }}
            </div>

            {{-- Access --}}
            <div class="col-md-6">
                {{ html()->label(__('movie.lbl_movie_access'), 'access')->class('form-label') }}
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <label class="form-check form-check-inline form-control cursor-pointer w-auto m-0">
                        <div>
                            <input class="form-check-input" type="radio" name="access" value="free"
                                onchange="togglePpvFields(this.value)" {{ old('access', 'free') == 'free' ? 'checked' : '' }}>
                            <span class="form-check-label">{{ __('movie.lbl_free') }}</span>
                        </div>
                    </label>
                    <label class="form-check form-check-inline form-control cursor-pointer w-auto m-0">
                        <div>
                            <input class="form-check-input" type="radio" name="access" value="pay-per-view"
                                onchange="togglePpvFields(this.value)" {{ old('access') == 'pay-per-view' ? 'checked' : '' }}>
                            <span class="form-check-label">{{ __('messages.lbl_pay_per_view') }}</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- PPV --}}
            <div class="col-12 {{ old('access') == 'pay-per-view' ? '' : 'd-none' }}" id="ppv_fields">
                <div class="row g-3">
                    <div class="col-md-3">
                        {{ html()->label(__('messages.lbl_price') . ' <span class="text-danger">*</span>', 'price')->class('form-label') }}
                        <div class="input-group">
                            <span class="input-group-text">FCFA</span>
                            {{ html()->number('price', old('price'))->class('form-control')->attribute('step', '0.01')->attribute('min', 0)->id('price') }}
                        </div>
                    </div>
                    <div class="col-9 d-flex align-items-end">
                        <div class="alert alert-info py-2 mb-0 small w-100">
                            <i class="ph ph-info me-1"></i>{{ __('partner::partner.ppv_price_info') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upload type + URL --}}
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

            {{-- Description --}}
            <div class="col-md-12">
                {{ html()->label(__('movie.lbl_description'), 'description')->class('form-label') }}
                {{ html()->textarea('description', old('description'))->class('form-control')->id('description')->rows(4) }}
            </div>

        </div>
    </div>
    <div class="card-footer text-end">
        <a href="{{ route('partner.tvshow.season.episodes', [$tvshow->id, $season->id]) }}" class="btn btn-secondary me-2">{{ __('messages.cancel') }}</a>
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
}
function handleVideoUrlTypeChange(val) {
    ['video_file_input_section','video_url_input_section','embed_code_input_section'].forEach(id => {
        const el = document.getElementById(id); if(el) el.classList.add('d-none');
    });
    if (val === 'Local') document.getElementById('video_file_input_section')?.classList.remove('d-none');
    else if (val === 'Embedded') document.getElementById('embed_code_input_section')?.classList.remove('d-none');
    else if (val !== '') document.getElementById('video_url_input_section')?.classList.remove('d-none');
}
$(document).ready(function () {
    if (typeof tinymce !== 'undefined') { tinymce.init({ selector: '#description', plugins: 'link image code', toolbar: 'undo redo | bold italic | link | code' }); }
    if ($.fn.select2) { $('.select2').select2({ language: { noResults: () => "{{ __('messages.no_results_found') }}" } }); }
    const ts = $('#video_upload_type'); if(ts.length) { handleVideoUrlTypeChange(ts.val()); ts.on('change select2:select', function(){ handleVideoUrlTypeChange($(this).val()); }); }
});
    // Synchroniser TinyMCE avant soumission
    document.getElementById('form-submit')?.addEventListener('submit', function() {
        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
    });

</script>
@endpush
