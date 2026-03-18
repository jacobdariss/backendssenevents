@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection

@section('content')
    <x-back-button-component route="backend.partners.index" />
    {{ html()->form('POST', route('backend.partners.store'))->attribute('enctype', 'multipart/form-data')->attribute('id', 'form-submit')->class('requires-validation')->attribute('novalidate', 'novalidate')->open() }}
    <div class="card">
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-12 col-xl-3 position-relative">
                    {{ html()->label(__('partner::partner.lbl_logo'), 'logo')->class('form-label') }}
                    <div class="input-group btn-file-upload">
                        {{ html()->button('<i class="ph ph-image"></i>' . __('messages.lbl_choose_image'))->class('input-group-text form-control')->type('button')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerLogo')->attribute('data-hidden-input', 'logo_url_input') }}
                        {{ html()->text('logo_input')->class('form-control')->placeholder(__('placeholder.lbl_image'))->attribute('aria-label', 'Logo')->attribute('data-bs-toggle', 'modal')->attribute('data-bs-target', '#exampleModal')->attribute('data-image-container', 'selectedImageContainerLogo') }}
                    </div>
                    <div class="uploaded-image" id="selectedImageContainerLogo">
                        @if (old('logo_url'))
                            <img src="{{ old('logo_url') }}" class="img-fluid mb-2" style="max-width: 100px; max-height: 100px;">
                        @endif
                    </div>
                    {{ html()->hidden('logo_url')->id('logo_url_input')->value(old('logo_url')) }}
                </div>
                <div class="col-xl-9">
                    <div class="row gy-3">
                        <div class="col-md-6">
                            {{ html()->label(__('messages.name') . ' <span class="text-danger">*</span>', 'name')->class('form-label') }}
                            {{ html()->text('name', old('name'))->class('form-control')->id('name')->placeholder(__('partner::partner.placeholder_name'))->attribute('required', 'required')->attribute('maxlength', '255') }}
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            {{ html()->label(__('partner::partner.lbl_email'), 'email')->class('form-label') }}
                            {{ html()->email('email', old('email'))->class('form-control')->id('email')->placeholder(__('partner::partner.placeholder_email')) }}
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            {{ html()->label(__('partner::partner.lbl_phone'), 'phone')->class('form-label') }}
                            {{ html()->text('phone', old('phone'))->class('form-control')->id('phone')->placeholder(__('partner::partner.placeholder_phone')) }}
                            @error('phone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            {{ html()->label(__('partner::partner.lbl_website'), 'website')->class('form-label') }}
                            {{ html()->text('website', old('website'))->class('form-control')->id('website')->placeholder(__('partner::partner.placeholder_website')) }}
                            @error('website')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            {{ html()->label(__('partner::partner.lbl_description'), 'description')->class('form-label') }}
                            {{ html()->textarea('description', old('description'))->class('form-control')->id('description')->placeholder(__('partner::partner.placeholder_description'))->rows(4) }}
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            {{ html()->label(__('messages.lbl_status'), 'status')->class('form-label') }}
                            <div class="d-flex justify-content-between align-items-center form-control">
                                {{ html()->label(__('messages.active'), 'status')->class('form-label mb-0') }}
                                <div class="form-check form-switch">
                                    {{ html()->hidden('status', 0) }}
                                    {{ html()->checkbox('status', old('status', 1))->class('form-check-input')->id('status')->value(1) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- User account creation --}}
    <div class="card mt-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="ph ph-user-circle me-2"></i>{{ __('partner::partner.lbl_create_account') }}</h6>
                <div class="form-check form-switch mb-0">
                    {{ html()->hidden('create_account', 0) }}
                    {{ html()->checkbox('create_account', false, 1)->class('form-check-input')->id('create_account_toggle') }}
                </div>
            </div>
            <small class="text-muted">{{ __('partner::partner.lbl_create_account_help') }}</small>
        </div>
        <div class="card-body" id="account_fields" style="display:none;">
            <div class="row gy-3">
                <div class="col-md-6">
                    {{ html()->label(__('messages.first_name'), 'account_first_name')->class('form-label') }}
                    {{ html()->text('account_first_name', old('account_first_name'))->class('form-control')->id('account_first_name') }}
                    @error('account_first_name')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-6">
                    {{ html()->label(__('messages.last_name'), 'account_last_name')->class('form-label') }}
                    {{ html()->text('account_last_name', old('account_last_name'))->class('form-control')->id('account_last_name') }}
                    @error('account_last_name')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-6">
                    {{ html()->label(__('messages.email') . ' <span class="text-danger">*</span>', 'account_email')->class('form-label') }}
                    {{ html()->email('account_email', old('account_email'))->class('form-control')->id('account_email') }}
                    @error('account_email')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-6">
                    {{ html()->label(__('messages.password') . ' <span class="text-danger">*</span>', 'account_password')->class('form-label') }}
                    {{ html()->password('account_password')->class('form-control')->id('account_password') }}
                    @error('account_password')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-6">
                    {{ html()->label(__('messages.password_confirmation'), 'account_password_confirmation')->class('form-label') }}
                    {{ html()->password('account_password_confirmation')->class('form-control') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Allowed content types --}}
    @php
        $contentTypes = [
            'video'   => __('partner::partner.content_type_video'),
            'movie'   => __('partner::partner.content_type_movie'),
            'tvshow'  => __('partner::partner.content_type_tvshow'),
            'livetv'  => __('partner::partner.content_type_livetv'),
        ];
    @endphp
    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="ph ph-check-square me-2"></i>{{ __('partner::partner.lbl_content_types') }}</h6>
            <small class="text-muted">{{ __('partner::partner.lbl_content_types_help') }}</small>
        </div>
        <div class="card-body">
            <div class="row gy-2">
                @foreach ($contentTypes as $value => $label)
                    <div class="col-md-3 col-sm-6">
                        <label class="form-check form-control cursor-pointer d-flex align-items-center gap-2 mb-0">
                            <input class="form-check-input m-0" type="checkbox" name="content_types[]"
                                value="{{ $value }}"
                                {{ in_array($value, old('content_types', [])) ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="d-grid d-sm-flex justify-content-sm-end gap-3 mt-3">
        {{ html()->submit(trans('messages.save'))->class('btn btn-md btn-primary float-right')->id('submit-button') }}
    </div>
    {{ html()->form()->close() }}

    @php $page_type = 'partners'; @endphp
    @include('components.media-modal')
@endsection

@push('after-scripts')
<script>
    document.getElementById('create_account_toggle').addEventListener('change', function () {
        document.getElementById('account_fields').style.display = this.checked ? 'block' : 'none';
    });
</script>
@endpush
