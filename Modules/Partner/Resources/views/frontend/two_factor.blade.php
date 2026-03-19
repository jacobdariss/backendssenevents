@extends('frontend::layouts.auth_layout')

@section('title') {{ __('messages.two_factor_title') }} @endsection

@section('content')
<div id="login">
    <div class="vh-100" style="background: url('{{ asset('/dummy-images/login_banner.jpg') }}'); background-size: cover; background-repeat: no-repeat; min-height:500px; overflow-y:auto;">
        <div class="container">
            <div class="row justify-content-center align-items-center" style="min-height:100vh;">
                <div class="col-lg-5 col-md-8 col-sm-11 col-12 align-self-center">
                    <div class="user-login-card card my-5">
                        <div class="auth-heading">
                            <div class="text-center mb-4">
                                @php $logo = GetSettingValue('dark_logo') ? setBaseUrlWithFileName(GetSettingValue('dark_logo'),'image','logos') : asset('img/logo/dark_logo.png'); @endphp
                                <img src="{{ $logo }}" class="img-fluid mb-3" style="max-height:60px;">
                                <h5>{{ __('messages.two_factor_title') }}</h5>
                                <p class="text-muted small">{{ __('messages.two_factor_subtitle') }}</p>
                            </div>

                            @if(session('email_sent') === false)
                                <div class="alert alert-warning small">
                                    ⚠️ {{ __('messages.two_factor_email_not_configured') }}
                                </div>
                            @endif
                            @if(session('status'))
                                <div class="alert alert-success small">{{ session('status') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger small">
                                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                                </div>
                            @endif

                            <form action="{{ route('partner.2fa.verify') }}" method="POST">
                                @csrf
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="ph ph-lock-key"></i></span>
                                    <input type="text" name="otp" class="form-control text-center letter-spacing-3"
                                           placeholder="000000" maxlength="6" autocomplete="one-time-code"
                                           autofocus inputmode="numeric" pattern="[0-9]{6}" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    {{ __('messages.verify') }}
                                </button>
                            </form>

                            <form action="{{ route('partner.2fa.resend') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-link w-100 text-muted small p-0">
                                    {{ __('messages.resend_otp') }}
                                </button>
                            </form>

                            <div class="text-center mt-3">
                                <a href="{{ route('partner.login') }}" class="text-muted small">
                                    ← {{ __('messages.back_to_login') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
