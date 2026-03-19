@extends('frontend::layouts.auth_layout')

@section('title') {{ __('partner::partner.login_title') }} @endsection

@section('content')
<div id="login">
    <div class="vh-100" style="background: url('{{ asset('/dummy-images/login_banner.jpg') }}'); background-size: cover; background-repeat: no-repeat; position: relative; min-height: 500px; overflow-y:auto;">
        <div class="container">
            <div class="row justify-content-center align-items-center" style="min-height:100vh;">
                <div class="col-lg-5 col-md-8 col-sm-11 col-12 align-self-center">
                    <div class="user-login-card card my-5">
                        <div class="auth-heading">
                            <div class="text-center mb-4">
                                @php $logo = GetSettingValue('dark_logo') ? setBaseUrlWithFileName(GetSettingValue('dark_logo'),'image','logos') : asset('img/logo/dark_logo.png'); @endphp
                                <a href="{{ route('user.login') }}">
                                    <img src="{{ $logo }}" class="img-fluid mb-3" style="max-height:60px;">
                                </a>
                                <h5>{{ __('partner::partner.login_title') }}</h5>
                                <p class="text-muted small">{{ __('partner::partner.login_subtitle') }}</p>
                            </div>

                            @if(session('success'))
                                <div class="alert alert-success small">{{ session('success') }}</div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                                </div>
                            @endif

                            <form action="{{ route('partner.login.store') }}" method="POST">
                                @csrf
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="ph ph-envelope"></i></span>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           placeholder="{{ __('messages.email') }}" value="{{ old('email') }}" required autofocus>
                                </div>
                                <div class="input-group mb-4">
                                    <span class="input-group-text"><i class="ph ph-lock"></i></span>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                           placeholder="{{ __('messages.password') }}" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    {{ __('messages.login') }}
                                </button>
                            </form>

                            <p class="text-center text-muted small mb-0">
                                {{ __('partner::partner.no_account') }}
                                <a href="{{ route('partner.register') }}">{{ __('partner::partner.register_title') }}</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
