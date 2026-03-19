@extends('backend.layouts.app')
@section('title') {{ __('partner::partner.edit_season') }} @endsection

@section('content')
<x-back-button-component :route="route('partner.tvshow.seasons', $tvshow->id)" />

{{ html()->form('PUT', route('partner.tvshow.season.update', [$tvshow->id, $season->id]))->attribute('enctype', 'multipart/form-data')->attribute('id', 'form-submit')->open() }}

@method('PUT')

@if($season->approval_status === 'rejected' && $season->rejection_reason)
    <div class="alert alert-danger d-flex gap-2 mb-3">
        <i class="ph ph-warning fs-5 mt-1"></i>
        <div><strong>{{ __('partner::partner.rejection_reason') }} :</strong> {{ $season->rejection_reason }}</div>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="ph ph-plus-circle me-2"></i>{{ __('partner::partner.edit_season') }} — <span class="text-muted">{{ $tvshow->name }}</span></h5>
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
                <div class="uploaded-image" id="selectedImageContainerPoster">@if($season->poster_url)<img src="{{ $season->poster_url }}" class="img-fluid mt-1 box-preview-image">@endif</div>
                {{ html()->hidden('poster_url')->id('file_url_poster')->value(old('poster_url', $season->poster_url)) }}
            </div>

            {{-- Poster TV --}}
            <div class="col-md-6 position-relative">
                {{ html()->label(__('movie.lbl_poster_tv'), 'poster_tv')->class('form-label') }}
                <div class="input-group btn-file-upload">
                    {{ html()->button('<i class="ph ph-image"></i> ' . __('messages.lbl_choose_image'))->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPosterTv')->attribute('data-hidden-input', 'file_url_poster_tv') }}
                    {{ html()->text('poster_tv_input')->class('form-control')->placeholder(__('placeholder.lbl_image'))->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerPosterTv') }}
                </div>
                <div class="uploaded-image" id="selectedImageContainerPosterTv">@if($season->poster_tv_url)<img src="{{ $season->poster_tv_url }}" class="img-fluid mt-1 box-preview-image">@endif</div>
                {{ html()->hidden('poster_tv_url')->id('file_url_poster_tv')->value(old('poster_tv_url', $season->poster_tv_url)) }}
            </div>

            {{-- Name --}}
            <div class="col-md-8">
                {{ html()->label(__('movie.lbl_name') . ' <span class="text-danger">*</span>', 'name')->class('form-label') }}
                {{ html()->text('name', old('name', $season->name))->class('form-control')->attribute('required') }}
                @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
            </div>

            {{-- Season number --}}
            <div class="col-md-4">
                {{ html()->label(__('partner::partner.lbl_season_number') . ' <span class="text-danger">*</span>', 'season_number')->class('form-label') }}
                {{ html()->number('season_number', old('season_number', $season->season_number))->class('form-control')->attribute('min', 1)->attribute('required') }}
                @error('season_number')<span class="text-danger small">{{ $message }}</span>@enderror
            </div>

            {{-- Access --}}
            <div class="col-md-6">
                {{ html()->label(__('movie.lbl_movie_access'), 'access')->class('form-label') }}
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <label class="form-check form-check-inline form-control cursor-pointer w-auto m-0">
                        <div>
                            <input class="form-check-input" type="radio" name="access" value="free"
                                onchange="togglePpvFields(this.value)" {{ old('access', $season->access ?? 'free') == 'free' ? 'checked' : '' }}>
                            <span class="form-check-label">{{ __('movie.lbl_free') }}</span>
                        </div>
                    </label>
                    <label class="form-check form-check-inline form-control cursor-pointer w-auto m-0">
                        <div>
                            <input class="form-check-input" type="radio" name="access" value="pay-per-view"
                                onchange="togglePpvFields(this.value)" {{ old('access', $season->access) == 'pay-per-view' ? 'checked' : '' }}>
                            <span class="form-check-label">{{ __('messages.lbl_pay_per_view') }}</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- PPV --}}
            <div class="col-12 {{ old('access', $season->access) == 'pay-per-view' ? '' : 'd-none' }}" id="ppv_fields">
                <div class="row g-3">
                    <div class="col-md-4">
                        {{ html()->label(__('messages.lbl_price') . ' <span class="text-danger">*</span>', 'price')->class('form-label') }}
                        <div class="input-group">
                            <span class="input-group-text">FCFA</span>
                            {{ html()->number('price', old('price', $season->price))->class('form-control')->attribute('step', '0.01')->attribute('min', 0)->id('price') }}
                        </div>
                    </div>
                    <div class="col-8 d-flex align-items-end">
                        <div class="alert alert-info py-2 mb-0 small w-100">
                            <i class="ph ph-info me-1"></i>{{ __('partner::partner.ppv_price_info') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div class="col-md-12">
                {{ html()->label(__('movie.lbl_description'), 'description')->class('form-label') }}
                {{ html()->textarea('description', old('description', $season->description))->class('form-control')->id('description')->rows(4) }}
            </div>

        </div>
    </div>
    <div class="card-footer text-end">
        <a href="{{ route('partner.tvshow.seasons', $tvshow->id) }}" class="btn btn-secondary me-2">{{ __('messages.cancel') }}</a>
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
$(document).ready(function () {
    if (typeof tinymce !== 'undefined') { tinymce.init({ selector: '#description', plugins: 'link image code', toolbar: 'undo redo | bold italic | link | code' }); }
    if ($.fn.select2) { $('.select2').select2(); }
});
</script>
@endpush
