@extends('backend.layouts.app')

@section('title') {{ __('partner::partner.edit_video') }} @endsection

@section('content')

<x-back-button-component route="partner.videos" />

{{ html()->form('PUT', route('partner.videos.update', $video->id))->attribute('enctype', 'multipart/form-data')->attribute('id', 'form-submit')->class('requires-validation')->open() }}

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if($video->approval_status === 'rejected' && $video->rejection_reason)
        <div class="alert alert-danger d-flex gap-2 mb-3">
            <i class="ph ph-warning fs-5 mt-1"></i>
            <div><strong>{{ __('partner::partner.rejection_reason') }} :</strong> {{ $video->rejection_reason }}</div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0"><i class="ph ph-pencil me-2"></i>{{ __('partner::partner.edit_video') }}</h5></div>
        <div class="card-body">
            <div class="row gy-3">

                {{-- Thumbnail --}}
                <div class="col-md-4 position-relative">
                    {{ html()->label(__('movie.lbl_thumbnail'), 'thumbnail')->class('form-label') }}
                    <div class="input-group btn-file-upload">
                        {{ html()->button('<i class="ph ph-image"></i> ' . __('messages.lbl_choose_image'))->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerThumbnail')->attribute('data-hidden-input', 'file_url_thumbnail') }}
                        {{ html()->text('thumbnail_input', $video->thumbnail_url)->class('form-control')->placeholder(__('placeholder.lbl_image'))->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerThumbnail') }}
                    </div>
                    <div class="uploaded-image" id="selectedImageContainerThumbnail">
                        @if($video->thumbnail_url)<img src="{{ $video->thumbnail_url }}" class="img-fluid mt-1 box-preview-image">@endif
                    </div>
                    {{ html()->hidden('thumbnail_url')->id('file_url_thumbnail')->value($video->thumbnail_url) }}
                </div>

                {{-- Poster --}}
                <div class="col-md-4 position-relative">
                    {{ html()->label(__('movie.lbl_poster'), 'poster')->class('form-label') }}
                    <div class="input-group btn-file-upload">
                        {{ html()->button('<i class="ph ph-image"></i> ' . __('messages.lbl_choose_image'))->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPoster')->attribute('data-hidden-input', 'file_url_poster') }}
                        {{ html()->text('poster_input', $video->poster_url)->class('form-control')->placeholder(__('placeholder.lbl_image'))->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPoster') }}
                    </div>
                    <div class="uploaded-image" id="selectedImageContainerPoster">
                        @if($video->poster_url)<img src="{{ $video->poster_url }}" class="img-fluid mt-1 box-preview-image">@endif
                    </div>
                    {{ html()->hidden('poster_url')->id('file_url_poster')->value($video->poster_url) }}
                </div>

                {{-- Poster TV --}}
                <div class="col-md-4 position-relative">
                    {{ html()->label(__('movie.lbl_poster_tv'), 'poster_tv')->class('form-label') }}
                    <div class="input-group btn-file-upload">
                        {{ html()->button('<i class="ph ph-image"></i> ' . __('messages.lbl_choose_image'))->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPosterTv')->attribute('data-hidden-input', 'file_url_poster_tv') }}
                        {{ html()->text('poster_tv_input', $video->poster_tv_url)->class('form-control')->placeholder(__('placeholder.lbl_image'))->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPosterTv') }}
                    </div>
                    <div class="uploaded-image" id="selectedImageContainerPosterTv">
                        @if($video->poster_tv_url)<img src="{{ $video->poster_tv_url }}" class="img-fluid mt-1 box-preview-image">@endif
                    </div>
                    {{ html()->hidden('poster_tv_url')->id('file_url_poster_tv')->value($video->poster_tv_url) }}
                </div>

                {{-- Title --}}
                <div class="col-md-6">
                    {{ html()->label(__('video.lbl_title') . ' <span class="text-danger">*</span>', 'name')->class('form-label') }}
                    {{ html()->text('name', old('name', $video->name))->class('form-control')->attribute('required') }}
                    @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                {{-- Access --}}
                <div class="col-md-6">
                    {{ html()->label(__('movie.lbl_movie_access'), 'access')->class('form-label') }}
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        @foreach(['free' => __('movie.lbl_free'), 'paid' => __('movie.lbl_paid'), 'pay-per-view' => __('messages.lbl_pay_per_view')] as $val => $label)
                        <label class="form-check form-check-inline form-control cursor-pointer w-auto m-0">
                            <div>
                                <input class="form-check-input" type="radio" name="access" value="{{ $val }}"
                                    onchange="showPlanSelection(this.value === 'paid')"
                                    {{ old('access', $video->access) == $val ? 'checked' : '' }}>
                                <span class="form-check-label">{{ $label }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Plan --}}
                <div class="col-md-6 {{ old('access', $video->access) == 'free' ? 'd-none' : '' }}" id="planSelection">
                    {{ html()->label(__('movie.lbl_select_plan'), 'plan_id')->class('form-label') }}
                    {{ html()->select('plan_id', $plan->pluck('name', 'id')->prepend(__('placeholder.lbl_select_plan'), ''), old('plan_id', $video->plan_id))->class('form-control select2')->id('plan_id') }}
                </div>

                {{-- Duration --}}
                <div class="col-md-3">
                    {{ html()->label(__('movie.lbl_duration') . ' <span class="text-danger">*</span>', 'duration')->class('form-label') }}
                    {{ html()->text('duration', old('duration', $video->duration))->class('form-control')->attribute('required') }}
                    @error('duration')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                {{-- Release date --}}
                <div class="col-md-3">
                    {{ html()->label(__('movie.lbl_release_date') . ' <span class="text-danger">*</span>', 'release_date')->class('form-label') }}
                    {{ html()->date('release_date', old('release_date', $video->release_date?->format('Y-m-d')))->class('form-control')->attribute('required') }}
                    @error('release_date')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                {{-- Upload type --}}
                <div class="col-md-4">
                    {{ html()->label(__('movie.lbl_video_upload_type') . ' <span class="text-danger">*</span>', 'video_upload_type')->class('form-label') }}
                    {{ html()->select('video_upload_type', $upload_url_type->pluck('name', 'name')->prepend(__('placeholder.lbl_select_video_type'), '')->merge(['Embedded' => 'Embedded']), old('video_upload_type', $video->video_upload_type))->class('form-control select2')->id('video_upload_type')->attribute('required') }}
                    @error('video_upload_type')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                {{-- Video URL / File / Embed wrapper (col-md-8 sur même ligne que le select) --}}
                <div class="col-md-8">

                {{-- Embed code --}}
                <div class="{{ !in_array(old('video_upload_type', $video->video_upload_type), ['Local', 'URL', 'YouTube', 'HLS', 'Vimeo', 'x265', '']) ? '' : 'd-none' }}" id="embed_code_input_section">
                    {{ html()->label(__('movie.lbl_embed_code'), 'embed_code')->class('form-label') }}
                    {{ html()->textarea('embed_code', old('embed_code', $video->video_url_input))->class('form-control')->id('embed_code')->placeholder('<iframe ...></iframe>') }}
                </div>

                {{-- Video URL --}}
                <div class="{{ in_array(old('video_upload_type', $video->video_upload_type), ['URL', 'YouTube', 'HLS', 'Vimeo', 'x265']) ? '' : 'd-none' }}" id="video_url_input_section">
                    {{ html()->label(__('movie.video_url_input') . ' <span class="text-danger">*</span>', 'video_url_input')->class('form-label') }}
                    {{ html()->text('video_url_input', old('video_url_input', $video->video_url_input))->class('form-control')->id('video_url_input')->placeholder(__('placeholder.video_url_input')) }}
                </div>

                {{-- Video file --}}
                <div class="{{ old('video_upload_type', $video->video_upload_type) === 'Local' ? '' : 'd-none' }}" id="video_file_input_section">
                    {{ html()->label(__('movie.video_file_input'), 'video_file')->class('form-label') }}
                    <div class="input-group btn-video-link-upload">
                        {{ html()->button(__('placeholder.lbl_select_file') . '<i class="ph ph-upload"></i>')->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerVideourl')->attribute('data-hidden-input', 'file_url_video') }}
                        {{ html()->text('video_file_input', old('video_file_input', $video->video_url_input))->class('form-control')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerVideourl')->attribute('data-hidden-input', 'file_url_video') }}
                    </div>
                    <div class="mt-2" id="selectedImageContainerVideourl"></div>
                    {{ html()->hidden('video_url_input')->id('file_url_video')->value(old('video_url_input', $video->video_url_input)) }}
                </div>

                </div>{{-- end video wrapper --}}

                <div class="col-12"></div>{{-- line break --}}

                {{-- Trailer URL type --}}
                <div class="col-md-4">
                    {{ html()->label(__('movie.lbl_trailer_url_type') . ' <span class="text-danger">*</span>', 'trailer_url_type')->class('form-label') }}
                    {{ html()->select('trailer_url_type', $upload_url_type->pluck('name', 'name')->prepend(__('placeholder.lbl_select_type'), ''), old('trailer_url_type', $video->trailer_url_type ?? ''))->class('form-control select2')->id('trailer_url_type')->attribute('required') }}
                    @error('trailer_url_type')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                {{-- Trailer URL / File / Embed (affiché dynamiquement) --}}
                <div class="col-md-8">
                    <div id="url_input">
                        {{ html()->label(__('movie.lbl_trailer_url') . ' <span class="text-danger">*</span>', 'trailer_url')->class('form-label') }}
                        {{ html()->text('trailer_url', old('trailer_url', $video->trailer_url ?? ''))->class('form-control')->placeholder(__('placeholder.lbl_trailer_url'))->id('trailer_url') }}
                    </div>
                    <div id="url_file_input" class="d-none">
                        {{ html()->label(__('movie.lbl_trailer_video'), 'trailer_video')->class('form-label') }}
                        <div class="input-group btn-video-link-upload">
                            {{ html()->button(__('placeholder.lbl_select_file') . '<i class="ph ph-upload"></i>')->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainertailerurl')->attribute('data-hidden-input', 'file_url_trailer') }}
                            {{ html()->text('trailer_input', old('trailer_url', $video->trailer_url ?? ''))->class('form-control')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainertailerurl')->attribute('data-hidden-input', 'file_url_trailer') }}
                        </div>
                        <div class="mt-2" id="selectedImageContainertailerurl"></div>
                        {{ html()->hidden('trailer_url')->id('file_url_trailer')->value(old('trailer_url', $video->trailer_url ?? '')) }}
                    </div>
                    <div id="trailer_embed_input_section" class="d-none">
                        {{ html()->label(__('movie.lbl_embed_code'), 'trailer_embedded')->class('form-label') }}
                        {{ html()->textarea('trailer_embedded', old('trailer_embedded'))->class('form-control')->id('trailer_embedded')->placeholder('<iframe ...></iframe>') }}
                    </div>
                </div>
                {{-- Description --}}
                <div class="col-md-12">
                    {{ html()->label(__('movie.lbl_description') . ' <span class="text-danger">*</span>', 'description')->class('form-label') }}
                    {{ html()->textarea('description', old('description', $video->description))->class('form-control')->id('description')->rows(5)->attribute('required') }}
                    @error('description')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('partner.videos') }}" class="btn btn-secondary me-2">{{ __('messages.cancel') }}</a>
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
function showPlanSelection(show) {
    const planDiv = document.getElementById('planSelection');
    if (planDiv) planDiv.classList.toggle('d-none', !show);
}

function handleVideoUrlTypeChange(val) {
    const fileSection  = document.getElementById('video_file_input_section');
    const urlSection   = document.getElementById('video_url_input_section');
    const embedSection = document.getElementById('embed_code_input_section');
    if (fileSection) fileSection.classList.add('d-none');
    if (urlSection) urlSection.classList.add('d-none');
    if (embedSection) embedSection.classList.add('d-none');
    if (val === 'Local') { if(fileSection) fileSection.classList.remove('d-none'); }
    else if (val === 'Embedded') { if(embedSection) embedSection.classList.remove('d-none'); }
    else if (val !== '') { if(urlSection) urlSection.classList.remove('d-none'); }
}

function handleTrailerUrlTypeChange(val) {
    const fileInput  = document.getElementById('url_file_input');
    const urlInput   = document.getElementById('url_input');
    const embedInput = document.getElementById('trailer_embed_input_section');
    const trailerUrl = document.querySelector('input[name="trailer_url"]');
    if (!fileInput || !urlInput || !embedInput) return;
    fileInput.classList.add('d-none');
    urlInput.classList.add('d-none');
    embedInput.classList.add('d-none');
    trailerUrl?.removeAttribute('required');
    switch(val) {
        case 'Local':    fileInput.classList.remove('d-none'); break;
        case 'Embedded': embedInput.classList.remove('d-none'); break;
        case 'URL': case 'YouTube': case 'HLS': case 'Vimeo': case 'x265':
            urlInput.classList.remove('d-none');
            trailerUrl?.setAttribute('required', 'required');
            break;
    }
}

$(document).ready(function () {
    // TinyMCE
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#description',
            plugins: 'link image code',
            toolbar: 'undo redo | styleselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify | removeformat | code | image',
        });
    }

    // Select2
    if ($.fn.select2) {
        $('.select2').select2({ language: { noResults: function() { return "{{ __('messages.no_results_found') }}"; } } });
    }

    // Video upload type — init + change
    const typeSelect = $('#video_upload_type');
    if (typeSelect.length) {
        handleVideoUrlTypeChange(typeSelect.val());
        typeSelect.on('change select2:select', function() { handleVideoUrlTypeChange($(this).val()); });
    }

    // Trailer URL type — init + change
    const trailerSelect = $('#trailer_url_type');
    if (trailerSelect.length) {
        handleTrailerUrlTypeChange(trailerSelect.val());
        trailerSelect.on('change select2:select', function() { handleTrailerUrlTypeChange($(this).val()); });
    }
});
</script>
@endpush
