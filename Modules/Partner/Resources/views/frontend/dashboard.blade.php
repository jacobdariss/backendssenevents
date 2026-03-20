@extends('backend.layouts.app')

@section('title') {{ __('sidebar.dashboard') }} — {{ $partner->name }} @endsection

@section('content')

{{-- Quota ──────────────────────────────────────────────────────────── --}}
@if($stats['quota_max'] !== null)
<div class="card mb-3">
    <div class="card-body py-2">
        @php
            $pct = min(100, round($stats['quota_used'] / max(1, $stats['quota_max']) * 100));
            $color = $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
        @endphp
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-semibold"><i class="ph ph-database me-1"></i>{{ __('partner::partner.quota_usage') }}</span>
            <span class="small {{ $pct >= 90 ? 'text-danger fw-bold' : 'text-muted' }}">
                {{ $stats['quota_used'] }} / {{ $stats['quota_max'] }} {{ __('partner::partner.quota_videos') }}
                @if($pct >= 90) <i class="ph ph-warning ms-1"></i> @endif
            </span>
        </div>
        <div class="progress" style="height:8px">
            <div class="progress-bar bg-{{ $color }}" style="width:{{ $pct }}%"></div>
        </div>
        @if($pct >= 100)
        <div class="alert alert-danger mt-2 py-1 mb-0 small">
            <i class="ph ph-warning me-1"></i>{{ __('partner::partner.quota_exceeded_warning') }}
        </div>
        @endif
    </div>
</div>
@endif


<div class="d-flex align-items-center gap-3 mb-4">
    @if($partner->logo_url)
        @php $logoUrl = setBaseUrlWithFileName($partner->logo_url, 'image', 'partners'); @endphp
        <img src="{{ $logoUrl }}" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
    @else
        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
             style="width:48px;height:48px;font-size:20px;flex-shrink:0;">
            {{ strtoupper(substr($partner->name,0,1)) }}
        </div>
    @endif
    <div>
        <h4 class="mb-0">{{ $partner->name }}</h4>
        <small class="text-muted">{{ __('partner::partner.lbl_partner') }}</small>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-success-subtle text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-success">{{ $stats['videos_active'] }}</h3>
                <small class="text-muted">{{ __('partner::partner.lbl_videos_active') }}</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-warning-subtle text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-warning">{{ $stats['videos_pending'] }}</h3>
                <small class="text-muted">{{ __('partner::partner.status_pending') }}</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-secondary-subtle text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-secondary">{{ $stats['videos_inactive'] }}</h3>
                <small class="text-muted">{{ __('messages.inactive') }}</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-danger-subtle text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-danger">{{ $stats['videos_rejected'] }}</h3>
                <small class="text-muted">{{ __('partner::partner.status_rejected') }}</small>
            </div>
        </div>
    </div>
</div>

{{-- Catégories autorisées --}}
@if($partner->allowed_content_types)
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">{{ __('partner::partner.lbl_content_types') }}</h6>
    </div>
    <div class="card-body d-flex flex-wrap gap-2">
        @foreach($partner->allowed_content_types as $type)
            <span class="badge bg-primary px-3 py-2">{{ __('partner::partner.content_type_' . $type) }}</span>
        @endforeach
    </div>
</div>
@endif

{{-- Lien rapide --}}
<div class="d-flex gap-2">
    <a href="{{ route('partner.videos') }}" class="btn btn-primary">
        <i class="ph ph-video me-1"></i>{{ __('video.title') }}
    </a>
</div>

@endsection
