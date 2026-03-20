@extends('backend.layouts.app')

@section('title') {{ __('partner::partner.lbl_partner') }} — {{ $partner->name }} @endsection

@section('content')

<x-back-button-component route="backend.partners.index" />

<div class="row g-4">

    {{-- ── Colonne gauche : infos partenaire ── --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                @if($partner->logo_url)
                    @php $logoUrl = setBaseUrlWithFileName($partner->logo_url, 'image', 'partners'); @endphp
                    <img src="{{ $logoUrl }}" alt="{{ $partner->name }}"
                         class="rounded-circle mb-3" style="width:90px;height:90px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white mx-auto mb-3"
                         style="width:90px;height:90px;font-size:32px;">
                        {{ strtoupper(substr($partner->name,0,1)) }}
                    </div>
                @endif

                <h4 class="mb-1">{{ $partner->name }}</h4>
                <p class="text-muted small mb-3">{{ $partner->email }}</p>

                {{-- Statut --}}
                @if($partner->status)
                    <span class="badge bg-success px-3 py-2 mb-3">{{ __('messages.active') }}</span>
                @else
                    <span class="badge bg-secondary px-3 py-2 mb-3">{{ __('messages.inactive') }}</span>
                @endif

                <hr>

                <ul class="list-unstyled text-start small">
                    @if($partner->phone)
                    <li class="mb-2"><i class="ph ph-phone me-2 text-muted"></i>{{ $partner->phone }}</li>
                    @endif
                    @if($partner->website)
                    <li class="mb-2"><i class="ph ph-globe me-2 text-muted"></i>
                        <a href="{{ $partner->website }}" target="_blank">{{ $partner->website }}</a>
                    </li>
                    @endif
                    @if($partner->description)
                    <li class="mt-3 text-muted">{{ $partner->description }}</li>
                    @endif
                </ul>

                @if($partner->allowed_content_types)
                <hr>
                <p class="small fw-semibold mb-2">{{ __('partner::partner.lbl_content_types') }}</p>
                <div class="d-flex flex-wrap gap-1 justify-content-center">
                    @foreach($partner->allowed_content_types as $type)
                        <span class="badge bg-primary-subtle text-primary">
                            {{ __('partner::partner.content_type_' . $type) }}
                        </span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('backend.partners.edit', $partner->id) }}"
                   class="btn btn-warning btn-sm flex-fill">
                    <i class="ph ph-pencil me-1"></i>{{ __('messages.edit') }}
                </a>
            </div>
        </div>
    </div>

    {{-- ── Colonne droite : stats + compte utilisateur ── --}}
    <div class="col-md-8">

        {{-- Stats vidéos --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 bg-success-subtle">
                    <div class="card-body py-3">
                        <h3 class="mb-0 text-success">{{ $stats['videos_active'] }}</h3>
                        <small class="text-muted">{{ __('partner::partner.lbl_videos_active') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 bg-secondary-subtle">
                    <div class="card-body py-3">
                        <h3 class="mb-0 text-secondary">{{ $stats['videos_inactive'] }}</h3>
                        <small class="text-muted">{{ __('partner::partner.lbl_videos_inactive') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 bg-primary-subtle">
                    <div class="card-body py-3">
                        <h3 class="mb-0 text-primary">{{ $stats['movies_active'] }}</h3>
                        <small class="text-muted">{{ __('partner::partner.lbl_movies_active') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 bg-warning-subtle">
                    <div class="card-body py-3">
                        <h3 class="mb-0 text-warning">{{ $stats['total'] }}</h3>
                        <small class="text-muted">{{ __('partner::partner.lbl_videos_total') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Compte utilisateur --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="ph ph-user-circle me-2"></i>{{ __('partner::partner.lbl_account') }}
                </h6>
            </div>
            <div class="card-body">
                @if($partner->user)
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                             style="width:44px;height:44px;font-size:18px;flex-shrink:0;">
                            {{ strtoupper(substr($partner->user->first_name ?? 'P', 0, 1)) }}
                        </div>
                        <div class="flex-fill">
                            <p class="mb-0 fw-semibold">{{ $partner->user->full_name }}</p>
                            <p class="mb-0 text-muted small">{{ $partner->user->email }}</p>
                        </div>
                        <span class="badge bg-success">{{ __('partner::partner.account_linked') }}</span>
                    </div>
                @else
                    <div class="d-flex align-items-center justify-content-between">
                        <p class="text-muted mb-0">{{ __('partner::partner.no_account_linked') }}</p>
                        <a href="{{ route('backend.partners.edit', $partner->id) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="ph ph-plus me-1"></i>{{ __('partner::partner.lbl_create_account') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Contenu en attente de validation --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="ph ph-seal-check me-2"></i>{{ __('partner::partner.validation_title') }}
                </h6>
                <a href="{{ route('backend.partner-validation.index') }}"
                   class="btn btn-sm btn-outline-primary">
                    {{ __('messages.view') }} →
                </a>
            </div>
            <div class="card-body text-muted small">
                {{ __('partner::partner.validation_title') }} — {{ __('messages.view') }} {{ __('messages.all') }}
            </div>
        </div>

    </div>
</div>

@endsection
