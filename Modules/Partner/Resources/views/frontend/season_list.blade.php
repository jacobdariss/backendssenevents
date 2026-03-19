@extends('backend.layouts.app')
@section('title') {{ __('episode.lbl_season') }} — {{ $tvshow->name }} @endsection

@section('content')
<x-back-button-component route="partner.tvshows" />

@if(session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="ph ph-monitor-play me-2"></i>{{ $tvshow->name }}
            <span class="text-muted fs-6 ms-2">— {{ __('episode.lbl_season') }}</span>
        </h4>
        <a href="{{ route('partner.tvshow.season.create', $tvshow->id) }}" class="btn btn-primary btn-sm">
            <i class="ph ph-plus-circle me-1"></i>{{ __('partner::partner.add_season') }}
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>{{ __('messages.name') }}</th>
                    <th>#</th>
                    <th>{{ __('messages.lbl_status') }}</th>
                    <th>{{ __('partner::partner.status_pending') }}</th>
                    <th>{{ __('messages.action') }}</th>
                </tr></thead>
                <tbody>
                    @forelse($seasons as $season)
                    <tr>
                        <td><strong>{{ $season->name }}</strong></td>
                        <td><span class="badge bg-secondary">S{{ $season->season_number }}</span></td>
                        <td>
                            @if($season->status)
                                <span class="badge bg-success">{{ __('messages.active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($season->approval_status === 'approved')
                                <span class="badge bg-success">{{ __('partner::partner.status_approved') }}</span>
                            @elseif($season->approval_status === 'rejected')
                                <span class="badge bg-danger">{{ __('partner::partner.status_rejected') }}</span>
                            @else
                                <span class="badge bg-warning text-dark">{{ __('partner::partner.status_pending') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('partner.tvshow.season.episodes', [$tvshow->id, $season->id]) }}" class="btn btn-info btn-sm">
                                    <i class="ph ph-film-strip me-1"></i>{{ __('partner::partner.episodes') }}
                                </a>
                                <a href="{{ route('partner.tvshow.season.edit', [$tvshow->id, $season->id]) }}" class="btn btn-warning-subtle btn-sm">
                                    <i class="ph ph-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('messages.no_record_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($seasons->hasPages())
    <div class="card-footer">{{ $seasons->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
