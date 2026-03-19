@extends('backend.layouts.app')

@section('title') {{ __('messages.security') }} @endsection

@section('content')
<div class="row">
    <div class="col-md-8 col-lg-6">

        {{-- ── 2FA Admin ────────────────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2 text-primary"></i>
                        {{ __('messages.two_factor_title') }}
                    </h4>
                    <small class="text-muted">{{ __('messages.2fa_admin_desc') }}</small>
                </div>
            </div>
            <div class="card-body">
                <form id="form-2fa" method="POST" action="{{ route('backend.security.2fa.toggle') }}">
                    @csrf
                    <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                        <div>
                            <strong>{{ __('messages.2fa_admin_label') }}</strong>
                            <p class="text-muted small mb-0">{{ __('messages.2fa_admin_help') }}</p>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="admin_2fa_toggle"
                                   name="admin_2fa_enabled"
                                   value="1"
                                   style="width:3rem;height:1.5rem;"
                                   {{ setting('admin_2fa_enabled', true) ? 'checked' : '' }}
                                   onchange="toggle2FA(this)">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            {{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Lien vers Permissions ────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="ph ph-faders me-2 text-primary"></i>
                    {{ __('messages.permission_role') }}
                </h4>
            </div>
            <div class="card-body">
                <p class="text-muted">{{ __('messages.permission_role_desc') }}</p>
                <a href="{{ route('backend.permission-role.list') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-right me-1"></i>
                    {{ __('messages.manage_permissions') }}
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('after-scripts')
<script>
function toggle2FA(el) {
    // Feedback visuel immédiat
    const label = el.closest('.d-flex').querySelector('strong');
    if (!label) return;
}
</script>
@endpush
