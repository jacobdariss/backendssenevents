@extends('backend.layouts.app')

@section('title') {{ __('video.title') }} — {{ $partner->name }} @endsection

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="ph ph-video me-2"></i>{{ __('video.title') }}
        </h4>
    </div>

    {{-- Filtres --}}
    <div class="card-body border-bottom py-2">
        <form method="GET" action="{{ route('partner.videos') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="1" {{ $status == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                    <option value="0" {{ $status == '0' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="approval_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="pending"  {{ $approvalStatus == 'pending'  ? 'selected' : '' }}>{{ __('partner::partner.status_pending') }}</option>
                    <option value="approved" {{ $approvalStatus == 'approved' ? 'selected' : '' }}>{{ __('partner::partner.status_approved') }}</option>
                    <option value="rejected" {{ $approvalStatus == 'rejected' ? 'selected' : '' }}>{{ __('partner::partner.status_rejected') }}</option>
                </select>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ __('messages.name') }}</th>
                        <th>{{ __('messages.lbl_status') }}</th>
                        <th>{{ __('partner::partner.validation_title') }}</th>
                        <th>{{ __('messages.updated_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($videos as $video)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($video->thumbnail)
                                    <img src="{{ $video->thumbnail }}" style="width:40px;height:30px;object-fit:cover;border-radius:4px;">
                                @endif
                                <strong>{{ $video->name ?? $video->title ?? '—' }}</strong>
                            </div>
                        </td>
                        <td>
                            @if($video->status)
                                <span class="badge bg-success">{{ __('messages.active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @php $as = $video->approval_status ?? 'pending'; @endphp
                            @if($as === 'approved')
                                <span class="badge bg-success">{{ __('partner::partner.status_approved') }}</span>
                            @elseif($as === 'rejected')
                                <span class="badge bg-danger">{{ __('partner::partner.status_rejected') }}</span>
                                @if($video->rejection_reason)
                                    <p class="small text-muted mt-1 mb-0">
                                        <i class="ph ph-warning me-1"></i>{{ $video->rejection_reason }}
                                    </p>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark">{{ __('partner::partner.status_pending') }}</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $video->updated_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            {{ __('messages.no_record_found') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($videos->hasPages())
    <div class="card-footer">
        {{ $videos->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
