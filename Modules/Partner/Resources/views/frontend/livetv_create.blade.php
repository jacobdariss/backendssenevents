@extends('backend.layouts.app')
@section('title') {{ __('partner::partner.add_livetv') }} @endsection

@section('content')
<x-back-button-component route="partner.livetv" />

@if(session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
@endif

{{ html()->form('POST', route('partner.livetv.store'))->attribute('enctype', 'multipart/form-data')->attribute('id', 'form-submit')->open() }}

@if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0"><i class="ph ph-broadcast me-2"></i>{{ __('partner::partner.add_livetv') }}</h5></div>
    <div class="card-body">
        <div class="row gy-3">

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

            {{-- Name --}}
            <div class="col-md-6">
                {{ html()->label(__('movie.lbl_name') . ' <span class="text-danger">*</span>', 'name')->class('form-label') }}
                {{ html()->text('name', old('name'))->class('form-control')->attribute('required') }}
                @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
            </div>

            {{-- Category --}}
            <div class="col-md-6">
                {{ html()->label(__('livetv.title') . ' <span class="text-danger">*</span>', 'category_id')->class('form-label') }}
                {{ html()->select('category_id', $tvcategory->pluck('name', 'id')->prepend(__('placeholder.lbl_select'), ''), old('category_id'))->class('form-control select2')->attribute('required') }}
                @error('category_id')<span class="text-danger small">{{ $message }}</span>@enderror
            </div>

            {{-- Access --}}
            <div class="col-md-6">
                {{ html()->label(__('movie.lbl_movie_access'), 'access')->class('form-label') }}
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <label class="form-check form-check-inline form-control cursor-pointer w-auto m-0">
                        <div>
                            <input class="form-check-input" type="radio" name="access" value="free"
                                onchange="togglePpvFields(this.value)"
                                {{ old('access', 'free') == 'free' ? 'checked' : '' }}>
                            <span class="form-check-label">{{ __('movie.lbl_free') }}</span>
                        </div>
                    </label>
                    <label class="form-check form-check-inline form-control cursor-pointer w-auto m-0">
                        <div>
                            <input class="form-check-input" type="radio" name="access" value="pay-per-view"
                                onchange="togglePpvFields(this.value)"
                                {{ old('access') == 'pay-per-view' ? 'checked' : '' }}>
                            <span class="form-check-label">{{ __('messages.lbl_pay_per_view') }}</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- PPV Fields --}}
            <div class="col-12 {{ old('access') == 'pay-per-view' ? '' : 'd-none' }}" id="ppv_fields">
                <div class="row g-3">
                    <div class="col-md-4">
                        {{ html()->label(__('messages.lbl_price') . ' <span class="text-danger">*</span>', 'price')->class('form-label') }}
                        <div class="input-group">
                            <span class="input-group-text">FCFA</span>
                            {{ html()->number('price', old('price'))->class('form-control')->attribute('step', '0.01')->attribute('min', 0)->id('price') }}
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="alert alert-info py-2 mb-0 small mt-4">
                            <i class="ph ph-info me-1"></i>{{ __('partner::partner.ppv_price_info') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stream type --}}
            <div class="col-md-4">
                {{ html()->label(__('messages.lbl_type'), 'type')->class('form-label') }}
                <div class="d-flex gap-3">
                    <label class="form-check form-control cursor-pointer w-auto m-0">
                        <input class="form-check-input" type="radio" name="type" value="t_url"
                            onchange="showStreamtypeSelection('t_url')" {{ old('type', 't_url') == 't_url' ? 'checked' : '' }}>
                        <span class="form-check-label">{{ __('messages.lbl_url') }}</span>
                    </label>
                    <label class="form-check form-control cursor-pointer w-auto m-0">
                        <input class="form-check-input" type="radio" name="type" value="t_embedded"
                            onchange="showStreamtypeSelection('t_embedded')" {{ old('type') == 't_embedded' ? 'checked' : '' }}>
                        <span class="form-check-label">{{ __('messages.lbl_embedded') }}</span>
                    </label>
                </div>
            </div>

            <div class="col-md-4 {{ old('type', 't_url') == 't_url' ? '' : 'd-none' }}" id="type_url">
                {{ html()->label(__('movie.lbl_stream_type'), 'stream_type')->class('form-label') }}
                {{ html()->select('stream_type', $url->pluck('name', 'value')->prepend(__('placeholder.lbl_select_video_type'), ''), old('stream_type'))->class('form-control select2') }}
            </div>

            <div class="col-md-4 {{ old('type') == 't_embedded' ? '' : 'd-none' }}" id="type_embedded">
                {{ html()->label(__('movie.lbl_stream_type'), 'stream_type_embedded')->class('form-label') }}
                {{ html()->select('stream_type', $embedded->pluck('name', 'value')->prepend(__('placeholder.lbl_select_video_type'), ''), old('stream_type'))->class('form-control select2') }}
            </div>

            <div class="col-md-8">
                <div id="stream_url_section" class="{{ old('type', 't_url') == 't_url' ? '' : 'd-none' }}">
                    {{ html()->label(__('movie.server_url') . ' <span class="text-danger">*</span>', 'server_url')->class('form-label') }}
                    {{ html()->text('server_url', old('server_url'))->class('form-control')->placeholder('https://...') }}
                </div>
                <div id="stream_embedded_section" class="{{ old('type') == 't_embedded' ? '' : 'd-none' }}">
                    {{ html()->label(__('movie.lbl_embed_code') . ' <span class="text-danger">*</span>', 'embedded')->class('form-label') }}
                    {{ html()->textarea('embedded', old('embedded'))->class('form-control')->placeholder('<iframe ...></iframe>') }}
                </div>
            </div>

            {{-- Description --}}
            <div class="col-md-12">
                {{ html()->label(__('movie.lbl_description') . ' <span class="text-danger">*</span>', 'description')->class('form-label') }}
                {{ html()->textarea('description', old('description'))->class('form-control')->id('description')->rows(5)->attribute('required') }}
                @error('description')<span class="text-danger small">{{ $message }}</span>@enderror
            </div>

        </div>
    </div>
    <div class="card-footer text-end">
        <a href="{{ route('partner.livetv') }}" class="btn btn-secondary me-2">{{ __('messages.cancel') }}</a>
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
function showStreamtypeSelection(type) {
    document.getElementById('type_url')?.classList.toggle('d-none', type !== 't_url');
    document.getElementById('type_embedded')?.classList.toggle('d-none', type !== 't_embedded');
    document.getElementById('stream_url_section')?.classList.toggle('d-none', type !== 't_url');
    document.getElementById('stream_embedded_section')?.classList.toggle('d-none', type !== 't_embedded');
}
$(document).ready(function () {
    if (typeof tinymce !== 'undefined') { tinymce.init({ selector: '#description', plugins: 'link image code', toolbar: 'undo redo | bold italic | link | code' }); }
    if ($.fn.select2) { $('.select2').select2({ language: { noResults: () => "{{ __('messages.no_results_found') }}" } }); }
    const t = $('input[name="type"]:checked').val() || 't_url';
    showStreamtypeSelection(t);
});
</script>
@endpush
