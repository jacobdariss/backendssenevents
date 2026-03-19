@extends('backend.layouts.app')
@section('title') {{ __('episode.title') }} — {{ $season->name }} @endsection

@section('content')
<x-back-button-component :route="route('partner.tvshow.seasons', $tvshow->id)" />

@if(session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="ph ph-film-strip me-2"></i>{{ $tvshow->name }}
            <span class="badge bg-secondary ms-2">S{{ $season->season_number }}</span>
            <span class="text-muted fs-6 ms-2">— {{ __('episode.title') }}</span>
        </h4>
        <a href="{{ route('partner.tvshow.season.episode.create', [$tvshow->id, $season->id]) }}" class="btn btn-primary btn-sm">
            <i class="ph ph-plus-circle me-1"></i>{{ __('partner::partner.add_episode') }}
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>{{ __('messages.name') }}</th>
                    <th>#</th>
                    <th>{{ __('messages.lbl_status') }}</th>
                    <th>{{ __('partner::partner.validation_title') }}</th>
                    <th>{{ __('messages.action') }}</th>
                </tr></thead>
                <tbody>
                    @forelse($episodes as $episode)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($episode->poster_url)
                                    <img src="{{ setBaseUrlWithFileName($episode->poster_url, 'image', 'episode') }}" style="width:40px;height:30px;object-fit:cover;border-radius:4px;" onerror="this.style.display='none'">
                                @endif
                                <strong>{{ $episode->name }}</strong>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">E{{ $episode->episode_number ?? '?' }}</span></td>
                        <td>
                            @if($episode->status)
                                <span class="badge bg-success">{{ __('messages.active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @php $as = $episode->approval_status ?? 'pending'; @endphp
                            @if($as === 'approved') <span class="badge bg-success">{{ __('partner::partner.status_approved') }}</span>
                            @elseif($as === 'rejected')
                                <span class="badge bg-danger">{{ __('partner::partner.status_rejected') }}</span>
                                @if($episode->rejection_reason)
                                    <p class="small text-muted mt-1 mb-0">{{ $episode->rejection_reason }}</p>
                                @endif
                            @else <span class="badge bg-warning text-dark">{{ __('partner::partner.status_pending') }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('partner.tvshow.season.episode.edit', [$tvshow->id, $season->id, $episode->id]) }}" class="btn btn-warning-subtle btn-sm">
                                <i class="ph ph-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('messages.no_record_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($episodes->hasPages())
    <div class="card-footer">{{ $episodes->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
