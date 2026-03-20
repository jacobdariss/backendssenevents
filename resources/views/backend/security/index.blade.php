@extends('setting::backend.setting.index')

@section('title') {{ __('messages.security') }} @endsection

@section('settings-content')

<h3 class="mb-4">
    <i class="fas fa-shield-alt me-2"></i>{{ __('messages.security') }}
</h3>

{{-- ── 2FA Admin ──────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            {{ __('messages.two_factor_title') }}
        </h5>
        <small class="text-muted">{{ __('messages.2fa_admin_desc') }}</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('backend.security.2fa.toggle') }}">
            @csrf
            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif
            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                <div>
                    <strong>{{ __('messages.2fa_admin_label') }}</strong>
                    <p class="text-muted small mb-0">{{ __('messages.2fa_admin_help') }}</p>
                </div>
                <div class="form-check form-switch ms-3">
                    <input class="form-check-input"
                           type="checkbox"
                           name="admin_2fa_enabled"
                           value="1"
                           style="width:3rem;height:1.5rem;"
                           {{ setting('admin_2fa_enabled', true) ? 'checked' : '' }}>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    {{ __('messages.save') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── 2FA Partenaire ─────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            {{ __('partner::partner.lbl_partner') }} — {{ __('messages.two_factor_title') }}
        </h5>
        <small class="text-muted">{{ __('messages.2fa_partner_desc') }}</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('backend.security.2fa.toggle') }}">
            @csrf
            <input type="hidden" name="setting_key" value="partner_2fa_enabled">
            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                <div>
                    <strong>{{ __('messages.2fa_partner_label') }}</strong>
                    <p class="text-muted small mb-0">{{ __('messages.2fa_partner_help') }}</p>
                </div>
                <div class="form-check form-switch ms-3">
                    <input class="form-check-input"
                           type="checkbox"
                           name="admin_2fa_enabled"
                           value="1"
                           style="width:3rem;height:1.5rem;"
                           {{ setting('partner_2fa_enabled', true) ? 'checked' : '' }}>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Autorisation & Rôle ─────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="ph ph-faders me-2"></i>{{ __('messages.permission_role') }}
        </h5>
    </div>
    <div class="card-body">
        <p class="text-muted">{{ __('messages.permission_role_desc') }}</p>
        <a href="{{ route('backend.permission-role.list') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-right me-1"></i>
            {{ __('messages.manage_permissions') }}
        </a>
    </div>
</div>

@endsection
