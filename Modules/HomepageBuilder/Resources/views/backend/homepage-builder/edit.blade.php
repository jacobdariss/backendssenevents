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

    @if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

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

                    {{-- Orientation des vignettes --}}
                    <div class="mt-4" id="orientation-wrap">
                        <label class="form-label fw-semibold">Orientation des vignettes</label>
                        <div class="d-flex gap-3">
                            <div class="orientation-card {{ old('card_orientation', $section?->card_orientation ?? 'vertical') === 'vertical' ? 'active' : '' }}"
                                 data-value="vertical" onclick="setOrientation('vertical')">
                                <div class="orientation-preview orientation-vertical">
                                    <div class="orientation-thumb"></div>
                                    <div class="orientation-thumb"></div>
                                    <div class="orientation-thumb"></div>
                                </div>
                                <span>Verticale</span>
                            </div>
                            <div class="orientation-card {{ old('card_orientation', $section?->card_orientation ?? 'vertical') === 'horizontal' ? 'active' : '' }}"
                                 data-value="horizontal" onclick="setOrientation('horizontal')">
                                <div class="orientation-preview orientation-horizontal">
                                    <div class="orientation-thumb"></div>
                                    <div class="orientation-thumb"></div>
                                    <div class="orientation-thumb"></div>
                                </div>
                                <span>Horizontale</span>
                            </div>
                        </div>
                        <input type="hidden" name="card_orientation" id="card_orientation"
                               value="{{ old('card_orientation', $section?->card_orientation ?? 'vertical') }}">
                    </div>

                    {{-- Présentation des cartes --}}
                    @php $settings = old('settings', $section?->settings ?? []); @endphp
                    <div class="mt-4" id="presentation-wrap">
                        <label class="form-label fw-semibold">Présentation des cartes</label>

                        {{-- Taille des cartes --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Taille des cartes</label>
                            <div class="d-flex gap-2">
                                @foreach(['small' => 'Petit', 'medium' => 'Moyen', 'large' => 'Grand'] as $val => $lbl)
                                <div class="presentation-option {{ ($settings['card_size'] ?? 'medium') === $val ? 'active' : '' }}"
                                     data-field="card_size" data-value="{{ $val }}" onclick="setPresentation('card_size', '{{ $val }}')">
                                    <div class="presentation-icon presentation-size-{{ $val }}">
                                        <div class="p-thumb"></div><div class="p-thumb"></div><div class="p-thumb"></div>
                                    </div>
                                    <span>{{ $lbl }}</span>
                                </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="settings[card_size]" id="setting_card_size"
                                   value="{{ $settings['card_size'] ?? 'medium' }}">
                        </div>

                        {{-- Taille des badges --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Badges (premium, location, note)</label>
                            <div class="d-flex gap-2">
                                @foreach(['small' => 'Discret', 'medium' => 'Normal', 'large' => 'Visible'] as $val => $lbl)
                                <div class="presentation-option {{ ($settings['badge_size'] ?? 'medium') === $val ? 'active' : '' }}"
                                     data-field="badge_size" data-value="{{ $val }}" onclick="setPresentation('badge_size', '{{ $val }}')">
                                    <div class="presentation-icon">
                                        <span class="badge-preview badge-preview-{{ $val }}"><i class="ph ph-crown"></i></span>
                                    </div>
                                    <span>{{ $lbl }}</span>
                                </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="settings[badge_size]" id="setting_badge_size"
                                   value="{{ $settings['badge_size'] ?? 'medium' }}">
                        </div>

                        {{-- Effet hover --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Effet au survol</label>
                            <div class="d-flex gap-2">
                                @foreach(['none' => 'Aucun', 'subtle' => 'Subtil', 'zoom' => 'Zoom + Elévation'] as $val => $lbl)
                                <div class="presentation-option {{ ($settings['hover_effect'] ?? 'subtle') === $val ? 'active' : '' }}"
                                     data-field="hover_effect" data-value="{{ $val }}" onclick="setPresentation('hover_effect', '{{ $val }}')">
                                    <div class="presentation-icon hover-icon-{{ $val }}">
                                        <div class="p-thumb"></div>
                                    </div>
                                    <span>{{ $lbl }}</span>
                                </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="settings[hover_effect]" id="setting_hover_effect"
                                   value="{{ $settings['hover_effect'] ?? 'subtle' }}">
                        </div>

                        {{-- Nombre de cartes par ligne --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Cartes par ligne (desktop)</label>
                            <select name="settings[items_per_row]" class="form-select form-select-sm" style="max-width:200px">
                                @foreach([3 => '3', 4 => '4', 5 => '5 (défaut)', 6 => '6', 7 => '7'] as $val => $lbl)
                                <option value="{{ $val }}" {{ ($settings['items_per_row'] ?? 5) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
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

            {{-- Panneau Saisons / Épisodes (affiché uniquement si type=entertainment & content_type=tvshow) --}}
            <div class="card mt-3" id="episode-picker-card" style="display:none!important">
                <div class="card-body">
                    <h5 class="card-title mb-1">
                        <i class="ph ph-film-strip me-1 text-primary"></i>Sélection Saisons / Épisodes
                    </h5>
                    <p class="text-muted small mb-3">
                        Sélectionnez une série, puis des saisons pour filtrer les épisodes à afficher.
                    </p>

                    {{-- Étape 1 : Série --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">1. Série TV</label>
                        <select id="ep_tvshow_select" class="form-select select2-tvshow" data-placeholder="Choisir une série...">
                            <option value=""></option>
                            @php
                                $tvshowOptions = \Modules\Entertainment\Models\Entertainment::where('status',1)
                                    ->where('type','tvshow')->whereNull('deleted_at')
                                    ->orderBy('name')->get(['id','name']);
                                $preselectedTvshow = $episodePickerData['tvshows'][0]['id'] ?? null;
                            @endphp
                            @foreach($tvshowOptions as $tvshow)
                            <option value="{{ $tvshow->id }}" {{ $preselectedTvshow == $tvshow->id ? 'selected' : '' }}>
                                {{ $tvshow->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Étape 2 : Saisons --}}
                    <div class="mb-3" id="ep_seasons_wrap" style="{{ $episodePickerData ? '' : 'display:none' }}">
                        <label class="form-label fw-semibold small">2. Saisons</label>
                        <select id="ep_seasons_select" class="form-select select2-seasons" multiple
                                data-placeholder="Sélectionner une ou plusieurs saisons...">
                            @if($episodePickerData)
                                @foreach($episodePickerData['seasons'] as $s)
                                <option value="{{ $s['id'] }}" selected>{{ $s['name'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Étape 3 : Épisodes --}}
                    <div class="mb-3" id="ep_episodes_wrap" style="{{ $episodePickerData ? '' : 'display:none' }}">
                        <label class="form-label fw-semibold small">3. Épisodes à afficher</label>
                        <div id="ep_loading" class="text-center py-2" style="display:none">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                        <select name="episode_ids[]" id="ep_episodes_select" class="form-select select2-episodes"
                                multiple data-placeholder="Sélectionner les épisodes...">
                            @if($episodePickerData)
                                @foreach($episodePickerData['episodes'] as $ep)
                                <option value="{{ $ep['id'] }}"
                                    {{ in_array($ep['id'], $episodePickerData['episode_ids']) ? 'selected' : '' }}>
                                    {{ $ep['name'] }}
                                </option>
                                @endforeach
                            @endif
                        </select>
                        @if($episodePickerData)
                        <div class="mt-2">
                            <small class="text-success">
                                <i class="ph ph-check-circle me-1"></i>
                                <span id="ep_count_label">{{ count($episodePickerData['episode_ids']) }} épisode(s) sélectionné(s)</span>
                            </small>
                        </div>
                        @endif
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-0" id="ep_hint">
                        <i class="ph ph-info me-1"></i>
                        Si aucun épisode n'est sélectionné, la section affichera les séries (comportement standard).
                    </div>
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
// URLs injectées depuis PHP — évite les route() inline sujets au cache de routes
const HB_URLS = {
    contentOptions : '{{ $ajaxUrls["contentOptions"] }}',
    tvshowSeasons  : '{{ $ajaxUrls["tvshowSeasons"] }}',
    seasonEpisodes : '{{ $ajaxUrls["seasonEpisodes"] }}',
};

document.addEventListener('DOMContentLoaded', function() {

    const typeSelect    = document.getElementById('type');
    const ctypeSelect   = document.getElementById('content_type');
    const pickerWrap    = document.getElementById('content-picker-wrap');
    const episodeCard   = document.getElementById('episode-picker-card');
    const noSelectTypes = ['banner', 'genre', 'personality', 'language', 'payperview', 'continue_watching'];

    // ── Affichage conditionnel du panneau épisodes ──────────────────────────
    function toggleEpisodePicker() {
        const isEntertainment = typeSelect.value === 'entertainment';
        const isTvshow        = ctypeSelect.value === 'tvshow';
        if (isEntertainment && isTvshow) {
            episodeCard.style.removeProperty('display');
            episodeCard.style.display = '';
            episodeCard.removeAttribute('style'); // retire le !important inline
            episodeCard.classList.remove('d-none');
        } else {
            episodeCard.style.setProperty('display', 'none', 'important');
            // Vider la sélection épisodes quand on quitte tvshow
            const epSel = document.getElementById('ep_episodes_select');
            if (epSel && window.$ && $.fn.select2) {
                $(epSel).val([]).trigger('change');
            }
        }
    }

    // Init au chargement
    @if($section && $section->type === 'entertainment' && $section->content_type === 'tvshow')
        episodeCard.style.removeProperty('display');
        episodeCard.classList.remove('d-none');
    @endif

    typeSelect.addEventListener('change', toggleEpisodePicker);
    ctypeSelect.addEventListener('change', toggleEpisodePicker);

    // ── Rechargement des options contenu quand type/content_type change ─────
    function refreshContentOptions() {
        const type        = typeSelect.value;
        const contentType = ctypeSelect.value;

        if (noSelectTypes.includes(type)) {
            pickerWrap.innerHTML = '<p class="text-muted small fst-italic">Ce type de section ne supporte pas la sélection manuelle.</p>';
            return;
        }

        pickerWrap.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        fetch(HB_URLS.contentOptions + '?type=' + type + '&content_type=' + contentType)
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
            if (window.$ && $.fn.select2) {
                $('#content_ids').select2({ placeholder: 'Chercher et sélectionner...', allowClear: true, width: '100%' });
            }
        });
    }

    typeSelect.addEventListener('change', refreshContentOptions);
    ctypeSelect.addEventListener('change', refreshContentOptions);

    // Init Select2 initial
    if (window.$ && $.fn.select2) {
        $('#content_ids').select2({ placeholder: 'Chercher et sélectionner...', allowClear: true, width: '100%' });
    }

    // ── Orientation & Présentation : masquer pour types sans vignettes ────
    const noCardTypes = ['banner', 'genre', 'language', 'personality', 'continue_watching'];
    function togglePresentationWraps() {
        const hide = noCardTypes.includes(typeSelect.value);
        const orientWrap = document.getElementById('orientation-wrap');
        const presentWrap = document.getElementById('presentation-wrap');
        if (orientWrap) orientWrap.style.display = hide ? 'none' : '';
        if (presentWrap) presentWrap.style.display = hide ? 'none' : '';
    }
    togglePresentationWraps();
    typeSelect.addEventListener('change', togglePresentationWraps);

    // ── Episode Picker : AJAX cascadant ──────────────────────────────────────
    const tvshowSelect  = document.getElementById('ep_tvshow_select');
    const seasonsWrap   = document.getElementById('ep_seasons_wrap');
    const episodesWrap  = document.getElementById('ep_episodes_wrap');
    const episodesSel   = document.getElementById('ep_episodes_select');
    const seasonsSelect = document.getElementById('ep_seasons_select');
    const epLoading     = document.getElementById('ep_loading');

    function initEpisodeSelect2() {
        if (!window.$ || !$.fn.select2) return;
        if (tvshowSelect)  $(tvshowSelect).select2({ placeholder: 'Choisir une série...', allowClear: true, width: '100%' });
        if (seasonsSelect) $(seasonsSelect).select2({ placeholder: 'Sélectionner une ou plusieurs saisons...', allowClear: true, width: '100%' });
        if (episodesSel)   $(episodesSel).select2({ placeholder: 'Sélectionner les épisodes...', allowClear: true, width: '100%' });
    }
    initEpisodeSelect2();

    // Tvshow change → charger les saisons
    if (tvshowSelect) {
        $(tvshowSelect).on('change', function() {
            const tvshowId = $(this).val();
            if (!tvshowId) {
                seasonsWrap.style.display  = 'none';
                episodesWrap.style.display = 'none';
                return;
            }
            epLoading && (epLoading.style.display = '');
            seasonsWrap.style.display = '';
            fetch(HB_URLS.tvshowSeasons + '?tvshow_id=' + tvshowId)
            .then(r => r.json())
            .then(seasons => {
                epLoading && (epLoading.style.display = 'none');
                if (!seasons.length) {
                    seasonsWrap.style.display = 'none';
                    return;
                }
                // Reconstruire le select saisons
                $(seasonsSelect).empty();
                seasons.forEach(s => {
                    $(seasonsSelect).append(new Option(s.name, s.id));
                });
                $(seasonsSelect).trigger('change');
            })
            .catch(() => epLoading && (epLoading.style.display = 'none'));
        });
    }

    // Saisons change → charger les épisodes
    if (seasonsSelect) {
        $(seasonsSelect).on('change', function() {
            const seasonIds = $(this).val();
            if (!seasonIds || !seasonIds.length) {
                episodesWrap.style.display = 'none';
                return;
            }
            epLoading && (epLoading.style.display = '');
            const qs = seasonIds.map(id => 'season_ids[]=' + id).join('&');
            fetch(HB_URLS.seasonEpisodes + '?' + qs)
            .then(r => r.json())
            .then(episodes => {
                epLoading && (epLoading.style.display = 'none');
                if (!episodes.length) {
                    episodesWrap.style.display = 'none';
                    return;
                }
                const currentVals = $(episodesSel).val() || [];
                $(episodesSel).empty();
                episodes.forEach(ep => {
                    const isSelected = currentVals.includes(String(ep.id));
                    $(episodesSel).append(new Option(ep.name, ep.id, isSelected, isSelected));
                });
                $(episodesSel).trigger('change');
                episodesWrap.style.display = '';
                updateEpCount();
            })
            .catch(() => epLoading && (epLoading.style.display = 'none'));
        });
    }

    // Compteur épisodes sélectionnés
    function updateEpCount() {
        const label = document.getElementById('ep_count_label');
        if (!label || !episodesSel) return;
        const count = $(episodesSel).val()?.length || 0;
        label.textContent = count + ' épisode(s) sélectionné(s)';
        const parent = label.closest('.mt-2');
        if (parent) parent.style.display = count > 0 ? '' : 'none';
    }
    if (episodesSel) {
        $(episodesSel).on('change', updateEpCount);
    }

    // Init : si tvshow déjà sélectionné au chargement → déclencher le chargement des saisons
    @if($episodePickerData && isset($episodePickerData['tvshows'][0]))
    // Les saisons et épisodes sont déjà injectés côté serveur
    // Juste mettre à jour le compteur
    setTimeout(function() {
        updateEpCount();
        if (seasonsWrap) seasonsWrap.style.display = '';
        if (episodesWrap) episodesWrap.style.display = '';
    }, 300);
    @endif
});

function setOrientation(value) {
    document.getElementById('card_orientation').value = value;
    document.querySelectorAll('.orientation-card').forEach(el => {
        el.classList.toggle('active', el.dataset.value === value);
    });
}

function setPresentation(field, value) {
    document.getElementById('setting_' + field).value = value;
    document.querySelectorAll('.presentation-option[data-field="' + field + '"]').forEach(el => {
        el.classList.toggle('active', el.dataset.value === value);
    });
}
</script>

<style>
.orientation-card {
    cursor: pointer;
    border: 2px solid var(--bs-border-color);
    border-radius: var(--bs-border-radius);
    padding: 12px 20px;
    text-align: center;
    transition: all .2s ease;
    min-width: 110px;
    font-size: .875rem;
    color: var(--bs-body-color);
}
.orientation-card:hover { border-color: var(--bs-primary); }
.orientation-card.active { border-color: var(--bs-primary); background: rgba(var(--bs-primary-rgb),.08); color: var(--bs-primary); }
.orientation-preview { display: flex; gap: 4px; justify-content: center; margin-bottom: 8px; }
.orientation-vertical .orientation-thumb { width: 20px; height: 30px; background: var(--bs-border-color); border-radius: 3px; }
.orientation-horizontal .orientation-thumb { width: 36px; height: 20px; background: var(--bs-border-color); border-radius: 3px; }
.orientation-card.active .orientation-thumb { background: var(--bs-primary); opacity: .6; }

#episode-picker-card { border-left: 3px solid var(--bs-primary); }
#episode-picker-card .card-title { font-size: .95rem; }

/* ── Présentation des cartes ──────────────────────────────────────────── */
.presentation-option {
    cursor: pointer;
    border: 2px solid var(--bs-border-color);
    border-radius: var(--bs-border-radius);
    padding: 8px 14px;
    text-align: center;
    transition: all .2s ease;
    min-width: 90px;
    font-size: .8rem;
    color: var(--bs-body-color);
}
.presentation-option:hover { border-color: var(--bs-primary); }
.presentation-option.active { border-color: var(--bs-primary); background: rgba(var(--bs-primary-rgb),.08); color: var(--bs-primary); }
.presentation-icon { display: flex; gap: 3px; justify-content: center; align-items: center; margin-bottom: 6px; min-height: 28px; }
.presentation-size-small .p-thumb { width: 14px; height: 20px; background: var(--bs-border-color); border-radius: 2px; }
.presentation-size-medium .p-thumb { width: 18px; height: 26px; background: var(--bs-border-color); border-radius: 2px; }
.presentation-size-large .p-thumb { width: 22px; height: 32px; background: var(--bs-border-color); border-radius: 2px; }
.presentation-option.active .p-thumb { background: var(--bs-primary); opacity: .6; }
.badge-preview { display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: var(--bs-warning); color: #fff; }
.badge-preview-small { width: 18px; height: 18px; font-size: .6rem; }
.badge-preview-medium { width: 24px; height: 24px; font-size: .75rem; }
.badge-preview-large { width: 32px; height: 32px; font-size: 1rem; }
.presentation-option.active .badge-preview { background: var(--bs-primary); }
.hover-icon-none .p-thumb { width: 24px; height: 30px; background: var(--bs-border-color); border-radius: 2px; }
.hover-icon-subtle .p-thumb { width: 24px; height: 30px; background: var(--bs-border-color); border-radius: 2px; transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,.2); }
.hover-icon-zoom .p-thumb { width: 28px; height: 34px; background: var(--bs-border-color); border-radius: 2px; transform: translateY(-4px); box-shadow: 0 6px 12px rgba(0,0,0,.3); }

/* ── Select2 : container ────────────────────────────────────────────────── */
.select2-container--default .select2-selection--multiple {
    background-color: transparent;
    border: 1px solid var(--bs-border-color);
    border-radius: 8px;
    min-height: 42px;
    padding: 4px 6px;
    transition: border-color .2s;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), .12);
}

/* ── Tags : taille dynamique selon le texte ─────────────────────────────── */
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    /* taille dynamique — pas de max-width fixe */
    max-width: min(260px, 90%) !important;
    width: fit-content !important;
    /* couleurs douces par défaut */
    background: rgba(100, 116, 139, .15) !important;
    border: 1px solid rgba(100, 116, 139, .3) !important;
    color: var(--bs-body-color) !important;
    border-radius: 20px !important;
    font-size: 0.72rem !important;
    font-weight: 500 !important;
    letter-spacing: .01em;
    padding: 2px 8px 2px 10px !important;
    margin: 3px 4px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    position: relative;
    transition: background .18s, border-color .18s;
}

/* Couleur différente selon le select (contenu, saisons, épisodes) */
#content_ids + .select2-container .select2-selection__choice,
.select2-container:has(+ input[name="content_ids[]"]) .select2-selection__choice {
    background: rgba(99, 102, 241, .18) !important;
    border-color: rgba(99, 102, 241, .4) !important;
}
.select2-container:has(#select2-ep_seasons_select-container) .select2-selection__choice {
    background: rgba(16, 185, 129, .15) !important;
    border-color: rgba(16, 185, 129, .35) !important;
}
.select2-container:has(#select2-ep_episodes_select-container) .select2-selection__choice {
    background: rgba(245, 158, 11, .15) !important;
    border-color: rgba(245, 158, 11, .35) !important;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
    background: rgba(var(--bs-primary-rgb), .22) !important;
    border-color: rgba(var(--bs-primary-rgb), .5) !important;
}

/* ── Bouton × ────────────────────────────────────────────────────────────── */
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 14px !important;
    height: 14px !important;
    font-size: 12px !important;
    line-height: 1 !important;
    border: none !important;
    border-radius: 50% !important;
    background: rgba(var(--bs-body-color-rgb, 255,255,255), .15) !important;
    color: var(--bs-body-color) !important;
    opacity: .7;
    padding: 0 !important;
    margin-left: 2px !important;
    flex-shrink: 0;
    order: 2;
    position: static !important;
    transform: none !important;
    transition: background .15s, opacity .15s;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    background: rgba(239, 68, 68, .5) !important;
    opacity: 1;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__display {
    order: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 220px;
}

/* ── Dropdown ────────────────────────────────────────────────────────────── */
.select2-dropdown {
    border-color: var(--bs-border-color);
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,.25);
}
.select2-container--default .select2-results__option--highlighted {
    background-color: rgba(var(--bs-primary-rgb), .2) !important;
    color: var(--bs-body-color) !important;
}
.select2-container--default .select2-results__option--selected {
    background-color: rgba(var(--bs-primary-rgb), .1) !important;
}

</style>
@endpush
