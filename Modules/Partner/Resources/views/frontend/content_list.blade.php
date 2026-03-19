@extends('backend.layouts.app')

@section('title') {{ $title }} — {{ $partner->name }} @endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="ph ph-film-strip me-2"></i>{{ $title }}</h4>
        @if(in_array($content_type, ['movie', 'livetv']) || ($content_type === 'tvshow' && $items->isNotEmpty()))
            @php
                $createRoute = match($content_type) {
                    'movie'  => route('partner.movies.create'),
                    'tvshow' => route('partner.tvshows.create'),
                    'livetv' => route('partner.livetv.create'),
                    default  => null,
                };
            @endphp
            @if($createRoute)
            <a href="{{ $createRoute }}" class="btn btn-primary btn-sm">
                <i class="ph ph-plus-circle me-1"></i>{{ __('messages.add') }}
            </a>
            @endif
        @endif
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
                        <th>{{ __('messages.action') }}</th>
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
                        <td>
                            @php
                                $editRoute = match($content_type) {
                                    'movie'  => route('partner.movies.edit',  $item->id),
                                    'tvshow' => route('partner.tvshows.edit', $item->id),
                                    'livetv' => route('partner.livetv.edit',  $item->id),
                                    default  => null,
                                };
                            @endphp
                            @if($editRoute)
                            <a href="{{ $editRoute }}" class="btn btn-warning-subtle btn-sm">
                                <i class="ph ph-pencil"></i>
                            </a>
                            @endif
                            @if($content_type === 'tvshow')
                            <a href="{{ route('partner.tvshow.seasons', $item->id) }}" class="btn btn-info btn-sm">
                                <i class="ph ph-stack me-1"></i>{{ __('partner::partner.seasons') }}
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-5">
                            @if($content_type === 'tvshow')
                                {{-- Guide pas-à-pas pour les séries TV --}}
                                <div class="text-center">
                                    <i class="ph ph-monitor-play text-muted" style="font-size:3rem;"></i>
                                    <h5 class="mt-3">{{ __('partner::partner.no_tvshow_yet') }}</h5>
                                    <p class="text-muted small mb-4">{{ __('partner::partner.tvshow_flow_desc') }}</p>

                                    <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                                        <a href="{{ route('partner.tvshows.create') }}" class="btn btn-primary">
                                            <i class="ph ph-plus-circle me-1"></i>
                                            <strong>1.</strong> {{ __('partner::partner.add_tvshow') }}
                                        </a>
                                        <i class="ph ph-arrow-right text-muted"></i>
                                        <span class="btn btn-outline-secondary disabled opacity-50">
                                            <strong>2.</strong> {{ __('partner::partner.add_season') }}
                                        </span>
                                        <i class="ph ph-arrow-right text-muted"></i>
                                        <span class="btn btn-outline-secondary disabled opacity-50">
                                            <strong>3.</strong> {{ __('partner::partner.add_episode') }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="text-center text-muted">{{ __('messages.no_record_found') }}</div>
                            @endif
                        </td>
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
