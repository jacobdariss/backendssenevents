@extends('backend.layouts.app')

@section('title')
    {{ __('partner::partner.validation_title') }}
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="ph ph-seal-check me-2"></i>{{ __('partner::partner.validation_title') }}</h4>
    @if($pendingCount > 0)
        <span class="badge bg-warning text-dark fs-6">{{ $pendingCount }} {{ __('partner::partner.pending_badge') }}</span>
    @endif
</div>

{{-- Filtres --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('backend.partner-validation.index') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label mb-1 small">{{ __('messages.type') }}</label>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" {{ $type == 'all' ? 'selected' : '' }}>{{ __('messages.all') }}</option>
                    <option value="movie" {{ $type == 'movie' ? 'selected' : '' }}>{{ __('movie.title') }}</option>
                    <option value="tvshow" {{ $type == 'tvshow' ? 'selected' : '' }}>{{ __('movie.tvshows') }}</option>
                    <option value="video" {{ $type == 'video' ? 'selected' : '' }}>{{ __('video.title') }}</option>
                    <option value="livetv" {{ $type == 'livetv' ? 'selected' : '' }}>{{ __('frontend.livetv') }}</option>
                    <option value="season" {{ $type == 'season' ? 'selected' : '' }}>{{ __('episode.lbl_season') }}</option>
                    <option value="episode" {{ $type == 'episode' ? 'selected' : '' }}>{{ __('episode.title') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-1 small">{{ __('messages.lbl_status') }}</label>
                <select name="approval_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>{{ __('partner::partner.status_pending') }}</option>
                    <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>{{ __('partner::partner.status_approved') }}</option>
                    <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>{{ __('partner::partner.status_rejected') }}</option>
                </select>
            </div>
        </form>
    </div>
</div>

@php
    $allItems = collect()
        ->merge($movies->map(fn($m)  => ['item' => $m, 'content_type' => 'movie',  'label' => __('movie.title')]))
        ->merge($tvshows->map(fn($t) => ['item' => $t, 'content_type' => 'tvshow', 'label' => __('movie.tvshows')]))
        ->merge($videos->map(fn($v)  => ['item' => $v, 'content_type' => 'video',  'label' => __('video.title')]))
        ->merge($livetvs->map(fn($l)   => ['item' => $l, 'content_type' => 'livetv',  'label' => __('frontend.livetv')]))
        ->merge(($seasons  ?? collect())->map(fn($s) => ['item' => $s, 'content_type' => 'season',  'label' => __('episode.lbl_season')]))
        ->merge(($episodes ?? collect())->map(fn($e) => ['item' => $e, 'content_type' => 'episode', 'label' => __('episode.title')]));
@endphp

@if(!empty($migrationNeeded))
    <div class="alert alert-warning d-flex align-items-center gap-3">
        <i class="ph ph-warning fs-4"></i>
        <div>
            <strong>Migration requise.</strong>
            Exécutez <code>php artisan migrate</code> sur votre serveur pour activer ce module.
            <br><small class="text-muted">Les colonnes <code>approval_status</code> n'ont pas encore été créées en base de données.</small>
        </div>
    </div>
@elseif($allItems->isEmpty())
    <div class="alert alert-info">{{ __('partner::partner.no_content_to_validate') }}</div>
@else
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('messages.name') }}</th>
                        <th>{{ __('partner::partner.lbl_partner') }}</th>
                        <th>{{ __('messages.type') }}</th>
                        <th>{{ __('messages.lbl_price') }}</th>
                        <th>{{ __('messages.lbl_status') }}</th>
                        <th>{{ __('messages.created_at') }}</th>
                        <th class="text-end">{{ __('messages.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allItems as $row)
                    @php $item = $row['item']; @endphp
                    <tr id="row-{{ $row['content_type'] }}-{{ $item->id }}">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($item->poster_url ?? $item->thumbnail_url ?? null)
                                    <img src="{{ asset($item->poster_url ?? $item->thumbnail_url) }}" class="rounded" style="width:40px;height:40px;object-fit:cover;" onerror="this.style.display='none'">
                                @endif
                                <span class="fw-medium">{{ $item->name }}</span>
                            </div>
                        </td>
                        <td>
                            @if($item->partner)
                                <span class="badge bg-info text-white">{{ $item->partner->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary">{{ $row['label'] }}</span></td>
                        <td>
                            @if($item->access === 'pay-per-view')
                                @if($item->partner_proposed_price)
                                    <span class="badge bg-warning text-dark fw-bold">{{ number_format($item->partner_proposed_price, 0) }} FCFA</span>
                                    <small class="d-block text-muted mt-1">{{ __('partner::partner.proposed_by_partner') }}</small>
                                @endif
                                @if($item->price && $item->price != $item->partner_proposed_price)
                                    <span class="badge bg-success fw-bold">{{ number_format($item->price, 0) }} FCFA</span>
                                    <small class="d-block text-muted mt-1">{{ __('partner::partner.price_set_by_admin') }}</small>
                                @endif
                            @else
                                <span class="text-muted small">{{ __('movie.lbl_free') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->approval_status === 'pending')
                                <span class="badge bg-warning text-dark">{{ __('partner::partner.status_pending') }}</span>
                            @elseif($item->approval_status === 'approved')
                                <span class="badge bg-success">{{ __('partner::partner.status_approved') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('partner::partner.status_rejected') }}</span>
                                @if(!empty($item->rejection_reason))
                                    <p class="small text-muted mt-1 mb-0"><i class="ph ph-warning me-1"></i>{{ $item->rejection_reason }}</p>
                                @endif
                            @endif
                        </td>
                        <td class="small text-muted">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end flex-wrap" id="actions-{{ $row['content_type'] }}-{{ $item->id }}">
                                {{-- Bouton Éditer --}}
                                @php
                                    $editUrl = match($row['content_type']) {
                                        'movie'   => route('backend.movies.edit',          $item->id),
                                        'tvshow'  => route('backend.tvshows.edit',         $item->id),
                                        'video'   => route('backend.videos.edit',          $item->id),
                                        'episode' => route('backend.episodes.edit',        $item->id),
                                        'season'  => route('backend.entertainments.edit',  $item->entertainment_id ?? $item->id),
                                        'livetv'  => route('backend.livetvs.edit', $item->id),
                                        default   => null,
                                    };
                                @endphp
                                @if($editUrl)
                                <a href="{{ $editUrl }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="ph ph-pencil me-1"></i>{{ __('messages.edit') }}
                                </a>
                                @endif
                                @if($item->approval_status !== 'approved')
                                <button class="btn btn-sm btn-success btn-approve"
                                    data-type="{{ $row['content_type'] }}"
                                    data-id="{{ $item->id }}"
                                    data-access="{{ $item->access ?? 'free' }}"
                                    data-proposed-price="{{ $item->partner_proposed_price ?? '' }}"
                                    data-name="{{ $item->name }}"
                                    title="{{ __('partner::partner.approve') }}">
                                    <i class="ph ph-check-circle"></i> {{ __('partner::partner.approve') }}
                                </button>
                                @endif
                                @if($item->approval_status !== 'rejected')
                                <button class="btn btn-sm btn-danger btn-reject"
                                    data-type="{{ $row['content_type'] }}"
                                    data-id="{{ $item->id }}"
                                    title="{{ __('partner::partner.reject') }}">
                                    <i class="ph ph-x-circle"></i> {{ __('partner::partner.reject') }}
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

{{-- Modal d'approbation avec prix --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ph ph-check-circle text-success me-2"></i>{{ __('partner::partner.approve') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3" id="approve-content-name"></p>

                {{-- Section PPV (visible seulement si access = pay-per-view) --}}
                <div id="approve-ppv-section">
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="ph ph-currency-dollar me-1"></i>
                        {{ __('partner::partner.admin_price_review_info') }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('partner::partner.proposed_by_partner') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">FCFA</span>
                            <input type="text" id="approve-proposed-price" class="form-control" disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            {{ __('partner::partner.price_set_by_admin') }}
                            <span class="text-muted small">({{ __('messages.optional') }} — laisser vide = prix partenaire)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">FCFA</span>
                            <input type="number" id="approve-final-price" class="form-control"
                                   step="0.01" min="0" placeholder="{{ __('partner::partner.leave_blank_keep_proposed') }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-success" id="approve-confirm-btn">
                    <i class="ph ph-check-circle me-1"></i>{{ __('partner::partner.approve') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function handleAction(url, rowId) {
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status) {
                const row = document.getElementById(rowId);
                if (row) {
                    row.style.transition = 'opacity .3s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(() => showToast('{{ __("messages.something_went_wrong") }}', 'danger'));
    }

    function showToast(msg, type) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} position-fixed bottom-0 end-0 m-3`;
        toast.style.zIndex = 9999;
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    let pendingApproveData = null;

    document.querySelectorAll('.btn-approve').forEach(btn => {
        btn.addEventListener('click', function () {
            const type          = this.dataset.type;
            const id            = this.dataset.id;
            const access        = this.dataset.access;
            const proposedPrice = this.dataset.proposedPrice;
            const name          = this.dataset.name;

            pendingApproveData = { type, id, rowId: 'row-' + type + '-' + id };

            // Remplir la modal
            document.getElementById('approve-content-name').textContent = name;
            const ppvSection = document.getElementById('approve-ppv-section');

            if (access === 'pay-per-view') {
                ppvSection.style.display = '';
                document.getElementById('approve-proposed-price').value = proposedPrice ? Number(proposedPrice).toLocaleString('fr-FR') + ' FCFA' : '—';
                document.getElementById('approve-final-price').value = '';
            } else {
                ppvSection.style.display = 'none';
            }

            new bootstrap.Modal(document.getElementById('approveModal')).show();
        });
    });

    document.getElementById('approve-confirm-btn').addEventListener('click', function () {
        if (!pendingApproveData) return;
        const finalPrice = document.getElementById('approve-final-price').value;
        const body = {};
        if (finalPrice) body.final_price = finalPrice;

        fetch('{{ url("app/partner-validation/approve") }}/' + pendingApproveData.type + '/' + pendingApproveData.id, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(r => r.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('approveModal'))?.hide();
            if (data.status) {
                const row = document.getElementById(pendingApproveData.rowId);
                if (row) { row.style.transition='opacity .3s'; row.style.opacity='0'; setTimeout(()=>row.remove(),300); }
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(() => showToast('{{ __("messages.something_went_wrong") }}', 'danger'));
    });

    document.querySelectorAll('.btn-reject').forEach(btn => {
        btn.addEventListener('click', function () {
            const type   = this.dataset.type;
            const id     = this.dataset.id;
            const rowId  = 'row-' + type + '-' + id;
            const reason = prompt('{{ __("partner::partner.rejection_reason") }} ({{ __("messages.optional") }})');
            if (reason === null) return; // annulé

            fetch('{{ url("app/partner-validation/reject") }}/' + type + '/' + id, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ rejection_reason: reason })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    const row = document.getElementById(rowId);
                    if (row) { row.style.transition='opacity .3s'; row.style.opacity='0'; setTimeout(()=>row.remove(),300); }
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(() => showToast('{{ __("messages.something_went_wrong") }}', 'danger'));
        });
    });
});
</script>
@endpush
