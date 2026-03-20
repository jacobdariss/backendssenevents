@extends('backend.layouts.app')
@section('title') {{ $section ? 'Modifier' : 'Nouvelle' }} Section @endsection

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('backend.homepage-builder.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-arrow-left me-1"></i> Retour
    </a>
    <h4 class="mb-0">{{ $section ? 'Modifier : ' . $section->name : 'Nouvelle Section' }}</h4>
</div>

<form method="POST" action="{{ $section ? route('backend.homepage-builder.update', $section->id) : route('backend.homepage-builder.store') }}">
    @csrf
    @if($section) @method('PUT') @endif

    <div class="row g-4">
        {{-- Colonne gauche --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Configuration de la section</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom affiché <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $section?->name) }}" placeholder="Ex: Films à la Une" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type de section <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select select2" required>
                                @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $section?->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="content-type-wrap">
                            <label class="form-label fw-semibold">Sous-type de contenu</label>
                            <select name="content_type" id="content_type" class="form-select select2">
                                @foreach($contentTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('content_type', $section?->content_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Plateforme</label>
                            <select name="platform" class="form-select select2">
                                @foreach(['both' => 'Web + Mobile', 'web' => 'Web seulement', 'mobile' => 'Mobile seulement'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('platform', $section?->platform ?? 'both') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nb. max d'éléments</label>
                            <input type="number" name="content_limit" class="form-control" min="1" max="100"
                                   value="{{ old('content_limit', $section?->content_limit ?? 20) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Trier par</label>
                            <select name="sort_by" class="form-select select2">
                                @foreach($sortOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('sort_by', $section?->sort_by ?? 'created_at') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', $section?->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Section active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Colonne droite — Sélection de contenu --}}
        <div class="col-lg-5">
            <div class="card" id="content-picker-card">
                <div class="card-body">
                    <h5 class="card-title mb-1">Contenu sélectionné</h5>
                    <p class="text-muted small mb-3">Laissez vide pour une sélection automatique basée sur le tri choisi.</p>

                    <div id="content-picker-wrap">
                        @if(!empty($contentOptions))
                        <select name="content_ids[]" id="content_ids" class="form-control select2" multiple
                                data-placeholder="Chercher et sélectionner...">
                            @foreach($contentOptions as $item)
                            <option value="{{ $item['id'] }}"
                                {{ $section && $section->content_ids && in_array($item['id'], $section->content_ids) ? 'selected' : '' }}>
                                {{ $item['name'] }}{{ isset($item['type']) ? ' ('.$item['type'].')' : '' }}
                            </option>
                            @endforeach
                        </select>
                        @else
                        <p class="text-muted small fst-italic">Ce type de section ne supporte pas la sélection manuelle.</p>
                        @endif
                    </div>

                    @if($section && $section->content_ids)
                    <div class="mt-2">
                        <small class="text-success"><i class="ph ph-check-circle me-1"></i>{{ count($section->content_ids) }} éléments sélectionnés manuellement</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-3">
        <button type="submit" class="btn btn-primary px-4">
            <i class="ph ph-floppy-disk me-2"></i>{{ $section ? 'Mettre à jour' : 'Créer la section' }}
        </button>
        <a href="{{ route('backend.homepage-builder.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
@endsection

@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Rechargement des options contenu quand type ou content_type change
    const typeSelect    = document.getElementById('type');
    const ctypeSelect   = document.getElementById('content_type');
    const pickerWrap    = document.getElementById('content-picker-wrap');
    const noSelectTypes = ['banner', 'genre', 'personality', 'language', 'payperview', 'continue_watching'];

    function refreshContentOptions() {
        const type        = typeSelect.value;
        const contentType = ctypeSelect.value;

        if (noSelectTypes.includes(type)) {
            pickerWrap.innerHTML = '<p class="text-muted small fst-italic">Ce type de section ne supporte pas la sélection manuelle.</p>';
            return;
        }

        pickerWrap.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        fetch('{{ route("backend.homepage-builder.content-options") }}?type=' + type + '&content_type=' + contentType)
        .then(r => r.json())
        .then(items => {
            if (!items.length) {
                pickerWrap.innerHTML = '<p class="text-muted small fst-italic">Aucun contenu disponible.</p>';
                return;
            }
            let html = '<select name="content_ids[]" id="content_ids" class="form-control select2" multiple data-placeholder="Chercher et sélectionner...">';
            items.forEach(item => {
                const typeLabel = item.type ? ` (${item.type})` : '';
                html += `<option value="${item.id}">${item.name}${typeLabel}</option>`;
            });
            html += '</select>';
            pickerWrap.innerHTML = html;

            // Init Select2
            if (window.$ && $.fn.select2) {
                $('#content_ids').select2({ placeholder: 'Chercher et sélectionner...', allowClear: true, width: '100%' });
            }
        });
    }

    typeSelect.addEventListener('change', refreshContentOptions);
    ctypeSelect.addEventListener('change', refreshContentOptions);

    // Init Select2 initial si disponible
    if (window.$ && $.fn.select2) {
        $('#content_ids').select2({ placeholder: 'Chercher et sélectionner...', allowClear: true, width: '100%' });
    }
});
</script>
@endpush
