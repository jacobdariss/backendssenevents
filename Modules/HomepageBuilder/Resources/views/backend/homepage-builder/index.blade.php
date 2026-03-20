@extends('backend.layouts.app')
@section('title') Homepage Builder @endsection

@push('before-styles')
<style>
.hb-card{background:var(--bs-body-bg);border:1px solid var(--bs-border-color);border-radius:12px;margin-bottom:10px;transition:box-shadow .2s,opacity .2s;cursor:grab;user-select:none}
.hb-card:active{cursor:grabbing}
.hb-card.sortable-ghost{opacity:.35;box-shadow:0 0 0 2px #c0392b}
.hb-card.sortable-chosen{box-shadow:0 8px 24px rgba(0,0,0,.18)}
.hb-card.disabled-section{opacity:.5}
.drag-handle{cursor:grab;color:#aaa;padding:0 10px;font-size:20px}
.drag-handle:active{cursor:grabbing}
.save-order-btn{position:fixed;bottom:24px;right:24px;z-index:999;transition:opacity .3s}
.save-order-btn.hidden{opacity:0;pointer-events:none}
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Homepage Builder</h4>
        <p class="text-muted small mb-0">Glissez-déposez pour réordonner. Togglez pour activer/désactiver. S'applique sur le web et le mobile.</p>
    </div>
    <a href="{{ route('backend.homepage-builder.create') }}" class="btn btn-primary">
        <i class="ph ph-plus me-1"></i> Nouvelle section
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">● Actif</span>
    <span class="badge bg-secondary bg-opacity-10 text-secondary border">● Inactif</span>
    <span class="ms-auto text-muted small"><i class="ph ph-arrows-out-cardinal me-1"></i>Glisser pour réordonner</span>
</div>

<div id="sortable-sections">
@foreach($sections as $section)
@php
$typeLabels = \Modules\HomepageBuilder\Models\HomepageSection::types();
$icons = ['entertainment'=>'ph-film-strip','video'=>'ph-video','livetv'=>'ph-broadcast','genre'=>'ph-tag','banner'=>'ph-image','personality'=>'ph-user-circle','language'=>'ph-translate','payperview'=>'ph-currency-dollar','continue_watching'=>'ph-play-circle'];
$platformLabel = match($section->platform){'web'=>'Web','mobile'=>'Mobile',default=>'Web + Mobile'};
@endphp
<div class="hb-card {{ !$section->is_active ? 'disabled-section' : '' }}" data-id="{{ $section->id }}">
    <div class="d-flex align-items-center p-3 gap-3">
        <span class="drag-handle"><i class="ph ph-dots-six-vertical"></i></span>
        <span class="fw-bold text-muted" style="min-width:28px">#{{ $loop->iteration }}</span>
        <i class="ph {{ $icons[$section->type] ?? 'ph-squares-four' }} fs-4 text-primary"></i>
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <strong>{{ $section->name }}</strong>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size:11px">
                    {{ $typeLabels[$section->type] ?? $section->type }}
                </span>
                @if($section->content_type)
                <span class="badge bg-light text-dark border" style="font-size:11px">{{ $section->content_type }}</span>
                @endif
            </div>
            <div class="d-flex gap-3 mt-1 flex-wrap">
                <span class="text-muted small"><i class="ph ph-stack me-1"></i>Max {{ $section->content_limit }}</span>
                <span class="text-muted small"><i class="ph ph-sort-ascending me-1"></i>{{ $section->sort_by }}</span>
                @if($section->content_ids)
                <span class="text-muted small"><i class="ph ph-hand-pointing me-1"></i>{{ count($section->content_ids) }} sélectionnés</span>
                @endif
                <span class="badge bg-secondary bg-opacity-10 text-secondary border" style="font-size:10px">{{ $platformLabel }}</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input toggle-active" type="checkbox" data-id="{{ $section->id }}" {{ $section->is_active ? 'checked' : '' }}>
            </div>
            <a href="{{ route('backend.homepage-builder.edit', $section->id) }}" class="btn btn-sm btn-outline-secondary"><i class="ph ph-pencil"></i></a>
            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $section->id }}" data-name="{{ $section->name }}"><i class="ph ph-trash"></i></button>
        </div>
    </div>
</div>
@endforeach
</div>

<button id="save-order-btn" class="btn btn-primary btn-lg save-order-btn hidden shadow-lg">
    <i class="ph ph-floppy-disk me-2"></i>Sauvegarder l'ordre
</button>
@endsection

@push('after-scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container  = document.getElementById('sortable-sections');
    const saveBtn    = document.getElementById('save-order-btn');
    let orderChanged = false;

    Sortable.create(container, {
        animation: 200, handle: '.drag-handle',
        ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen',
        onEnd: function() { orderChanged = true; saveBtn.classList.remove('hidden'); }
    });

    saveBtn.addEventListener('click', function() {
        const positions = [...document.querySelectorAll('#sortable-sections .hb-card')]
            .map((c, i) => ({ id: c.dataset.id, position: i + 1 }));
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="ph ph-spinner me-2"></i>Sauvegarde...';
        fetch('{{ route("backend.homepage-builder.reorder") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ positions })
        }).then(r => r.json()).then(d => {
            if (d.status) {
                saveBtn.innerHTML = '<i class="ph ph-check me-2"></i>Sauvegardé !';
                saveBtn.classList.add('btn-success'); saveBtn.classList.remove('btn-primary');
                setTimeout(() => {
                    saveBtn.classList.add('hidden'); saveBtn.classList.remove('btn-success');
                    saveBtn.classList.add('btn-primary');
                    saveBtn.innerHTML = '<i class="ph ph-floppy-disk me-2"></i>Sauvegarder l\'ordre';
                    saveBtn.disabled = false; orderChanged = false;
                }, 2000);
            }
        }).catch(() => { saveBtn.innerHTML = 'Erreur'; saveBtn.disabled = false; });
    });

    document.querySelectorAll('.toggle-active').forEach(t => {
        t.addEventListener('change', function() {
            const id = this.dataset.id;
            const card = this.closest('.hb-card');
            fetch(`{{ url('app/homepage-builder') }}/${id}/toggle`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
            }).then(r => r.json()).then(d => card.classList.toggle('disabled-section', !d.is_active));
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Supprimer "' + this.dataset.name + '" ?')) return;
            fetch(`{{ url('app/homepage-builder') }}/${this.dataset.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
            }).then(r => r.json()).then(d => { if (d.status) this.closest('.hb-card').remove(); });
        });
    });

    window.addEventListener('beforeunload', e => { if (orderChanged) e.returnValue = 'Modifications non sauvegardées'; });
});
</script>
@endpush
