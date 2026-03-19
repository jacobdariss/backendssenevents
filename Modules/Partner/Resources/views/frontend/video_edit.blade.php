@extends('backend.layouts.app')

@section('title') {{ __('partner::partner.edit_video') }} @endsection

@section('content')

<x-back-button-component route="partner.videos" />

<form method="POST" action="{{ route('partner.videos.update', $video->id) }}" id="form-submit" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if($video->approval_status === 'rejected' && $video->rejection_reason)
        <div class="alert alert-danger d-flex gap-2 mb-3">
            <i class="ph ph-warning fs-5"></i>
            <div>
                <strong>{{ __('partner::partner.rejection_reason') }} :</strong>
                {{ $video->rejection_reason }}
            </div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><i class="ph ph-pencil me-2"></i>{{ __('partner::partner.edit_video') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">

                {{-- Thumbnail --}}
                <div class="col-md-6">
                    <label class="form-label">{{ __('movie.lbl_thumbnail') }}</label>
                    <div class="input-group btn-file-upload">
                        {{ html()->button('<i class="ph ph-image"></i> ' . __('messages.lbl_choose_image'))
                            ->class('input-group-text form-control')->type('button')
                            ->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')
                            ->attribute('data-image-container', 'selectedImageContainerThumbnail')
                            ->attribute('data-hidden-input', 'file_url_thumbnail') }}
                        {{ html()->text('thumbnail_input', $video->thumbnail_url)->class('form-control')->placeholder(__('placeholder.lbl_image'))
                            ->attribute('data-image-container', 'selectedImageContainerThumbnail') }}
                    </div>
                    <div class="uploaded-image mt-2" id="selectedImageContainerThumbnail">
                        @if($video->thumbnail_url)
                            <img src="{{ $video->thumbnail_url }}" class="img-fluid mt-1" style="max-height:80px;">
                        @endif
                    </div>
                    {{ html()->hidden('thumbnail_url')->id('file_url_thumbnail')->value($video->thumbnail_url) }}
                </div>

                {{-- Poster --}}
                <div class="col-md-6">
                    <label class="form-label">{{ __('movie.lbl_poster') }}</label>
                    <div class="input-group btn-file-upload">
                        {{ html()->button('<i class="ph ph-image"></i> ' . __('messages.lbl_choose_image'))
                            ->class('input-group-text form-control')->type('button')
                            ->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')
                            ->attribute('data-image-container', 'selectedImageContainerPoster')
                            ->attribute('data-hidden-input', 'file_url_poster') }}
                        {{ html()->text('poster_input', $video->poster_url)->class('form-control')->placeholder(__('placeholder.lbl_image'))
                            ->attribute('data-image-container', 'selectedImageContainerPoster') }}
                    </div>
                    <div class="uploaded-image mt-2" id="selectedImageContainerPoster">
                        @if($video->poster_url)
                            <img src="{{ $video->poster_url }}" class="img-fluid mt-1" style="max-height:80px;">
                        @endif
                    </div>
                    {{ html()->hidden('poster_url')->id('file_url_poster')->value($video->poster_url) }}
                </div>

                {{-- Title --}}
                <div class="col-md-6">
                    <label class="form-label">{{ __('video.lbl_title') }} <span class="text-danger">*</span></label>
                    {{ html()->text('name', old('name', $video->name))->class('form-control')->attribute('required') }}
                    @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                {{-- Duration --}}
                <div class="col-md-3">
                    <label class="form-label">{{ __('movie.lbl_duration') }} <span class="text-danger">*</span></label>
                    {{ html()->text('duration', old('duration', $video->duration))->class('form-control')->attribute('required') }}
                    @error('duration')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                {{-- Release date --}}
                <div class="col-md-3">
                    <label class="form-label">{{ __('movie.lbl_release_date') }} <span class="text-danger">*</span></label>
                    {{ html()->date('release_date', old('release_date', $video->release_date?->format('Y-m-d')))->class('form-control')->attribute('required') }}
                    @error('release_date')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                {{-- Access --}}
                <div class="col-md-4">
                    <label class="form-label">{{ __('movie.lbl_movie_access') }} <span class="text-danger">*</span></label>
                    {{ html()->select('access', ['paid' => __('movie.lbl_paid'), 'free' => __('movie.lbl_free')], old('access', $video->access))
                        ->class('form-control select2')->attribute('required') }}
                </div>

                {{-- Plan --}}
                <div class="col-md-4">
                    <label class="form-label">{{ __('movie.lbl_select_plan') }}</label>
                    {{ html()->select('plan_id', $plan->pluck('name', 'id')->prepend(__('movie.lbl_select'), ''), old('plan_id', $video->plan_id))
                        ->class('form-control select2') }}
                </div>

                {{-- Upload type --}}
                <div class="col-md-4">
                    <label class="form-label">{{ __('movie.lbl_video_upload_type') }} <span class="text-danger">*</span></label>
                    {{ html()->select('video_upload_type', $upload_url_type->pluck('name', 'name')->prepend(__('movie.lbl_select'), ''), old('video_upload_type', $video->video_upload_type))
                        ->class('form-control select2')->attribute('required') }}
                </div>

                {{-- Video URL --}}
                <div class="col-md-12">
                    <label class="form-label">{{ __('movie.video_url_input') }}</label>
                    <input type="text" name="video_url_input" class="form-control"
                           placeholder="{{ __('movie.video_url_input') }}"
                           value="{{ old('video_url_input', $video->video_url_input) }}">
                </div>

                {{-- Description --}}
                <div class="col-md-12">
                    <label class="form-label">{{ __('movie.lbl_description') }} <span class="text-danger">*</span></label>
                    {{ html()->textarea('description', old('description', $video->description))->class('form-control')->rows(4)->attribute('required') }}
                    @error('description')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">
                <i class="ph ph-paper-plane-tilt me-1"></i>{{ __('partner::partner.submit_for_validation') }}
            </button>
        </div>
    </div>

</form>

@include('components.media-modal', ['page_type' => $page_type, 'partnerFolder' => $partnerFolder])

@endsection
