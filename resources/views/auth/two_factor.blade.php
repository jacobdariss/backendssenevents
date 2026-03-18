<x-auth-layout>
    @section('title')
        @lang('messages.two_factor_title')
    @endsection
    <x-slot name="title">
        @lang('messages.two_factor_title')
    </x-slot>

    <x-auth-card>
        <x-slot name="logo">
            @php
                $logo = GetSettingValue('dark_logo') ? setBaseUrlWithFileName(GetSettingValue('dark_logo'), 'image', 'logos') : asset('img/logo/dark_logo.png');
            @endphp
            <a href="{{ route('user.login') }}">
                <img src="{{ $logo }}" class="img-fluid logo h-4 mb-4">
            </a>
        </x-slot>

        <x-auth-session-status class="mb-4" :status="session('status')" />
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <div class="text-center mb-4">
            <h5 class="fw-semibold">{{ __('messages.two_factor_title') }}</h5>
            <p class="text-muted small">{{ __('messages.two_factor_subtitle') }}</p>
        </div>

        <form method="POST" action="{{ route('admin.two-factor.store') }}" id="two-factor-form">
            @csrf

            <div>
                <x-label for="otp" :value="__('messages.otp_mail_label')" />
                <x-input id="otp"
                    type="text"
                    name="otp"
                    inputmode="numeric"
                    maxlength="6"
                    placeholder="000000"
                    autocomplete="one-time-code"
                    autofocus
                    class="text-center fs-4 letter-spacing-otp"
                />
            </div>
            <div class="invalid-feedback" id="otp-error" style="display: none;">
                {{ __('validation.required', ['attribute' => __('messages.otp_mail_label')]) }}
            </div>

            <div class="mt-4">
                <button type="submit" id="submit-btn" class="btn btn-primary w-100">
                    {{ __('messages.verify_otp') }}
                </button>
            </div>
        </form>

        <div class="text-center mt-3">
            <form method="POST" action="{{ route('admin.two-factor.resend') }}">
                @csrf
                <button type="submit" class="btn btn-link btn-sm p-0 text-muted">
                    {{ __('messages.resend_otp') }}
                </button>
            </form>
        </div>

        <div class="text-center mt-2">
            <a href="{{ route('admin-login') }}" class="btn btn-link btn-sm p-0 text-muted">
                &larr; {{ __('frontend.login') }}
            </a>
        </div>

        <x-slot name="extra"></x-slot>
    </x-auth-card>

    <style>
        .letter-spacing-otp { letter-spacing: 0.5rem; font-family: "Courier New", Courier, monospace; }
    </style>

    <script>
        document.getElementById('two-factor-form').addEventListener('submit', function (e) {
            const otp = document.getElementById('otp');
            const otpError = document.getElementById('otp-error');
            otpError.style.display = 'none';
            otp.classList.remove('is-invalid');

            if (!otp.value.trim() || !/^\d{6}$/.test(otp.value.trim())) {
                otpError.style.display = 'block';
                otp.classList.add('is-invalid');
                e.preventDefault();
                return;
            }

            document.getElementById('submit-btn').classList.add('disabled');
            document.getElementById('submit-btn').innerText = '{{ __('messages.verify_otp') }}...';
        });

        // Allow only digits
        document.getElementById('otp').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    </script>
</x-auth-layout>
