@extends('backend.layouts.app')

@section('title') {{ $title }} — {{ $partner->name }} @endsection

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="ph ph-film-strip me-2"></i>{{ $title }}</h4>
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
                    @forelse($items as $item)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @php
                                $rawThumb = $item->thumbnail_url ?? $item->poster_url ?? $item->thumb_url ?? null;
                                $contentType = $content_type === 'movie' ? 'movie' : ($content_type === 'tvshow' ? 'tvshow' : ($content_type === 'livetv' ? 'livetvchannel' : 'video'));
                                $thumb = $rawThumb ? setBaseUrlWithFileName($rawThumb, 'image', $contentType) : null;
                            @endphp
                                @if($thumb)
                                    <img src="{{ $thumb }}" style="width:40px;height:30px;object-fit:cover;border-radius:4px;">
                                @endif
                                <strong>{{ $item->name ?? '—' }}</strong>
                            </div>
                        </td>
                        <td>
                            @if($item->status)
                                <span class="badge bg-success">{{ __('messages.active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @php $as = $item->approval_status ?? 'pending'; @endphp
                            @if($as === 'approved')
                                <span class="badge bg-success">{{ __('partner::partner.status_approved') }}</span>
                            @elseif($as === 'rejected')
                                <span class="badge bg-danger">{{ __('partner::partner.status_rejected') }}</span>
                                @if($item->rejection_reason)
                                    <p class="small text-muted mt-1 mb-0"><i class="ph ph-warning me-1"></i>{{ $item->rejection_reason }}</p>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark">{{ __('partner::partner.status_pending') }}</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $item->updated_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">{{ __('messages.no_record_found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($items->hasPages())
    <div class="card-footer">{{ $items->withQueryString()->links() }}</div>
    @endif
</div>

@endsection
