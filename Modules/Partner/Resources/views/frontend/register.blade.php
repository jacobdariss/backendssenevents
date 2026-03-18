@extends('frontend::layouts.auth_layout')

@section('title')
    {{ __('partner.register_title') }}
@endsection

@section('content')
<div id="login">
    <div class="vh-100"
        style="background: url('{{ asset('/dummy-images/login_banner.jpg') }}'); background-size: cover; background-repeat: no-repeat; position: relative; min-height: 500px; overflow-y:auto;">
        <div class="container">
            <div class="row justify-content-center align-items-center height-self-center" style="min-height:100vh;">
                <div class="col-lg-6 col-md-9 col-sm-11 col-12 align-self-center">
                    <div class="user-login-card card my-5">
                        <div class="auth-heading">
                            <div class="text-center mb-4">
                                @php
                                    $logo = GetSettingValue('dark_logo') ? setBaseUrlWithFileName(GetSettingValue('dark_logo'),'image','logos') : asset('img/logo/dark_logo.png');
                                @endphp
                                <a href="{{ route('user.login') }}">
                                    <img src="{{ $logo }}" class="img-fluid h-4 mb-3" style="max-height:60px;">
                                </a>
                                <h5>{{ __('partner.register_title') }}</h5>
                                <p class="text-muted small">{{ __('partner.register_subtitle') }}</p>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('partner.register.store') }}" method="POST">
                                @csrf

                                <h6 class="text-muted mb-3 border-bottom pb-2">{{ __('partner.register_section_account') }}</h6>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ph ph-user"></i></span>
                                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                                                placeholder="{{ __('messages.first_name') }}" value="{{ old('first_name') }}" required>
                                        </div>
                                        @error('first_name')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ph ph-user"></i></span>
                                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                                                placeholder="{{ __('messages.last_name') }}" value="{{ old('last_name') }}" required>
                                        </div>
                                        @error('last_name')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="ph ph-envelope"></i></span>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                        placeholder="{{ __('messages.email') }}" value="{{ old('email') }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="ph ph-lock"></i></span>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="{{ __('messages.password') }}" required minlength="8">
                                </div>

                                <div class="input-group mb-4">
                                    <span class="input-group-text"><i class="ph ph-lock"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control"
                                        placeholder="{{ __('messages.password_confirmation') }}" required>
                                </div>

                                <h6 class="text-muted mb-3 border-bottom pb-2">{{ __('partner.register_section_company') }}</h6>

                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="ph ph-buildings"></i></span>
                                    <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                                        placeholder="{{ __('partner.lbl_company_name') }}" value="{{ old('company_name') }}" required>
                                    @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="ph ph-phone"></i></span>
                                    <input type="text" name="phone" class="form-control"
                                        placeholder="{{ __('partner.lbl_phone') }}" value="{{ old('phone') }}">
                                </div>

                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="ph ph-globe"></i></span>
                                    <input type="url" name="website" class="form-control"
                                        placeholder="{{ __('partner.lbl_website') }}" value="{{ old('website') }}">
                                </div>

                                <div class="mb-4">
                                    <textarea name="description" class="form-control" rows="3"
                                        placeholder="{{ __('partner.placeholder_description') }}">{{ old('description') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    {{ __('partner.register_submit') }}
                                </button>

                                <p class="text-center mt-3 small">
                                    {{ __('partner.already_have_account') }}
                                    <a href="{{ route('user.login') }}">{{ __('frontend.sign_in') }}</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
