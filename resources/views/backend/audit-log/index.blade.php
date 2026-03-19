@extends('backend.layouts.app')
@section('title') Journal d'audit @endsection

@section('content')

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-end">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Rechercher...">
            </div>
            <div>
                <select name="action" class="form-select form-select-sm">
                    <option value="">Toutes les actions</option>
                    @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
            <a href="{{ route('backend.audit-log.index') }}" class="btn btn-sm btn-secondary">Réinitialiser</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr>
                <th>Date</th>
                <th>Utilisateur</th>
                <th>Action</th>
                <th>Contenu</th>
                <th>Type</th>
                <th>IP</th>
                <th>Détails</th>
            </tr></thead>
            <tbody>
                @forelse($logs as $log)
                @php
                    $badgeClass = match($log->action) {
                        'content_approved' => 'bg-success',
                        'content_rejected' => 'bg-danger',
                        'partner_deleted'  => 'bg-warning text-dark',
                        default            => 'bg-secondary',
                    };
                @endphp
                <tr>
                    <td class="small text-muted">{{ $log->created_at->format('d/m H:i') }}</td>
                    <td class="small fw-semibold">{{ $log->user_name ?? '—' }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $log->action }}</span></td>
                    <td class="small">{{ $log->model_name ?? '—' }}</td>
                    <td class="small text-muted">{{ $log->model_type ?? '—' }}</td>
                    <td class="small text-muted">{{ $log->ip_address ?? '—' }}</td>
                    <td class="small text-muted">{{ $log->details ? Str::limit($log->details, 60) : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun enregistrement</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-footer">{{ $logs->links() }}</div>
    @endif
</div>

@endsection
