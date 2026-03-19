@extends('backend.layouts.app')
@section('title') {{ __('partner::partner.notifications') }} @endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="ph ph-bell me-2"></i>{{ __('partner::partner.notifications') }}
        @if($unreadCount > 0)
            <span class="badge bg-danger ms-2">{{ $unreadCount }}</span>
        @endif
    </h5>
    @if($unreadCount > 0)
    <form method="POST" action="{{ route('partner.notifications.read') }}">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-secondary">
            <i class="ph ph-checks me-1"></i>{{ __('partner::partner.mark_all_read') }}
        </button>
    </form>
    @endif
</div>

<div class="card">
    <div class="card-body p-0">
        @forelse($notifications as $notif)
        @php $data = $notif->data; $isRead = $notif->read_at !== null; @endphp
        <div class="d-flex align-items-start gap-3 p-3 border-bottom {{ !$isRead ? 'bg-primary bg-opacity-5' : '' }}">
            <div class="mt-1">
                @if(($data['status'] ?? '') === 'approved')
                    <div class="bg-success bg-opacity-10 rounded-circle p-2"><i class="ph ph-check-circle text-success fs-5"></i></div>
                @else
                    <div class="bg-danger bg-opacity-10 rounded-circle p-2"><i class="ph ph-x-circle text-danger fs-5"></i></div>
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold">
                    @if(($data['status'] ?? '') === 'approved')
                        <span class="text-success">✓ {{ __('partner::partner.status_approved') }}</span>
                    @else
                        <span class="text-danger">✗ {{ __('partner::partner.status_rejected') }}</span>
                    @endif
                    — {{ $data['content_name'] ?? '—' }}
                    <span class="badge bg-secondary ms-1 small">{{ $data['content_type'] ?? '' }}</span>
                </div>
                <p class="text-muted small mb-1">{{ $data['message'] ?? '' }}</p>
                @if(!empty($data['reason']))
                    <div class="alert alert-danger py-1 px-2 mb-1 small">
                        <i class="ph ph-warning me-1"></i>{{ $data['reason'] }}
                    </div>
                @endif
                <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                @if(!$isRead)<span class="badge bg-primary ms-2 small">Nouveau</span>@endif
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="ph ph-bell-slash fs-1 mb-3 d-block"></i>
            {{ __('partner::partner.no_notifications') }}
        </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
    <div class="card-footer">{{ $notifications->links() }}</div>
    @endif
</div>

@endsection
