@extends('setting::backend.setting.index')

@section('title') Lecteur Vidéo @endsection

@section('settings-content')

<form method="POST" action="{{ route('backend.settings.video-player.save') }}" id="form-submit">
    @csrf

    {{-- En-tête ──────────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">
            <i class="fas fa-play-circle me-2 text-primary"></i>
            Lecteur Vidéo
        </h3>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Enregistrer
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Lecture ──────────────────────────────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-play me-2"></i>Lecture</h5>
        </div>
        <div class="card-body">

            {{-- Lecture automatique --}}
            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                <div>
                    <strong>Lecture automatique</strong>
                    <p class="text-muted small mb-0">Démarrer automatiquement la lecture au chargement d'un contenu.</p>
                </div>
                <div class="form-check form-switch ms-3">
                    <input class="form-check-input" type="checkbox" name="player_autoplay" value="1"
                           style="width:3rem;height:1.5rem;"
                           {{ ($data['player_autoplay'] ?? 0) == 1 ? 'checked' : '' }}>
                </div>
            </div>

            {{-- Muet au chargement --}}
            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                <div>
                    <strong>Muet au chargement</strong>
                    <p class="text-muted small mb-0">Démarrer en mode muet (requis pour l'autoplay sur certains navigateurs).</p>
                </div>
                <div class="form-check form-switch ms-3">
                    <input class="form-check-input" type="checkbox" name="player_muted_on_load" value="1"
                           style="width:3rem;height:1.5rem;"
                           {{ ($data['player_muted_on_load'] ?? 0) == 1 ? 'checked' : '' }}>
                </div>
            </div>

            {{-- Reprise automatique --}}
            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                <div>
                    <strong>Reprise automatique</strong>
                    <p class="text-muted small mb-0">Reprendre là où l'utilisateur s'est arrêté lors de la prochaine lecture.</p>
                </div>
                <div class="form-check form-switch ms-3">
                    <input class="form-check-input" type="checkbox" name="player_continue_watching" value="1"
                           style="width:3rem;height:1.5rem;"
                           {{ ($data['player_continue_watching'] ?? 1) == 1 ? 'checked' : '' }}>
                </div>
            </div>

            {{-- Qualité par défaut --}}
            <div class="p-3 border rounded mb-0">
                <label class="form-label fw-semibold mb-1">Qualité par défaut</label>
                <p class="text-muted small mb-2">Qualité de lecture sélectionnée automatiquement au démarrage.</p>
                <select name="player_default_quality" class="form-select" style="max-width:200px;">
                    @foreach(['auto' => 'Automatique', '360p' => '360p', '480p' => '480p', '720p' => '720p HD', '1080p' => '1080p Full HD'] as $val => $label)
                        <option value="{{ $val }}"
                            {{ ($data['player_default_quality'] ?? 'auto') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    {{-- Navigation & Contrôles ───────────────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-sliders-h me-2"></i>Navigation & Contrôles</h5>
        </div>
        <div class="card-body">

            {{-- Contrôle de vitesse --}}
            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                <div>
                    <strong>Contrôle de vitesse</strong>
                    <p class="text-muted small mb-0">Permettre à l'utilisateur de modifier la vitesse de lecture (0.5x, 1x, 1.5x, 2x).</p>
                </div>
                <div class="form-check form-switch ms-3">
                    <input class="form-check-input" type="checkbox" name="player_speed_control" value="1"
                           style="width:3rem;height:1.5rem;"
                           {{ ($data['player_speed_control'] ?? 1) == 1 ? 'checked' : '' }}>
                </div>
            </div>

            {{-- Avance rapide --}}
            <div class="p-3 border rounded mb-0">
                <label class="form-label fw-semibold mb-1">Avance / retour rapide (secondes)</label>
                <p class="text-muted small mb-2">Durée du saut lors d'un double tap ou clic sur les boutons avance/retour.</p>
                <div class="d-flex align-items-center gap-2">
                    <input type="number" name="player_forward_seconds"
                           class="form-control" style="max-width:120px;"
                           value="{{ $data['player_forward_seconds'] ?? 10 }}"
                           min="5" max="120" step="5">
                    <span class="text-muted">secondes</span>
                </div>
            </div>

        </div>
    </div>

    {{-- Skip Intro ────────────────────────────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-forward me-2"></i>Skip Intro</h5>
        </div>
        <div class="card-body">

            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                <div>
                    <strong>Bouton "Passer l'intro"</strong>
                    <p class="text-muted small mb-0">Afficher le bouton Skip Intro sur les épisodes de séries TV.</p>
                </div>
                <div class="form-check form-switch ms-3">
                    <input class="form-check-input" type="checkbox" name="player_skip_intro" value="1"
                           id="skipIntroToggle"
                           style="width:3rem;height:1.5rem;"
                           {{ ($data['player_skip_intro'] ?? 1) == 1 ? 'checked' : '' }}>
                </div>
            </div>

            <div class="p-3 border rounded mb-0" id="skipIntroDelay">
                <label class="form-label fw-semibold mb-1">Délai d'apparition (secondes)</label>
                <p class="text-muted small mb-2">Le bouton Skip Intro s'affiche après ce délai depuis le début de la lecture.</p>
                <div class="d-flex align-items-center gap-2">
                    <input type="number" name="player_skip_intro_delay"
                           class="form-control" style="max-width:120px;"
                           value="{{ $data['player_skip_intro_delay'] ?? 5 }}"
                           min="0" max="300">
                    <span class="text-muted">secondes</span>
                </div>
            </div>

        </div>
    </div>

    {{-- Sous-titres & Téléchargement ─────────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-closed-captioning me-2"></i>Sous-titres & Téléchargement</h5>
        </div>
        <div class="card-body">

            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                <div>
                    <strong>Sous-titres activés par défaut</strong>
                    <p class="text-muted small mb-0">Activer automatiquement les sous-titres s'ils sont disponibles.</p>
                </div>
                <div class="form-check form-switch ms-3">
                    <input class="form-check-input" type="checkbox" name="player_subtitles_default" value="1"
                           style="width:3rem;height:1.5rem;"
                           {{ ($data['player_subtitles_default'] ?? 0) == 1 ? 'checked' : '' }}>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-0">
                <div>
                    <strong>Téléchargement activé</strong>
                    <p class="text-muted small mb-0">Autoriser les abonnés à télécharger les contenus pour un visionnage hors-ligne.</p>
                </div>
                <div class="form-check form-switch ms-3">
                    <input class="form-check-input" type="checkbox" name="player_download_enabled" value="1"
                           style="width:3rem;height:1.5rem;"
                           {{ ($data['player_download_enabled'] ?? 0) == 1 ? 'checked' : '' }}>
                </div>
            </div>

        </div>
    </div>

    {{-- Filigrane ────────────────────────────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-copyright me-2"></i>Filigrane</h5>
        </div>
        <div class="card-body">
            <div class="p-3 border rounded">
                <label class="form-label fw-semibold mb-1">Position du filigrane</label>
                <p class="text-muted small mb-3">Position du logo de la plateforme affiché en superposition sur le lecteur.</p>
                <div class="row g-3" style="max-width:400px;">
                    @foreach([
                        'top-left'     => ['icon' => '↖', 'label' => 'Haut gauche'],
                        'top-right'    => ['icon' => '↗', 'label' => 'Haut droite'],
                        'bottom-left'  => ['icon' => '↙', 'label' => 'Bas gauche'],
                        'bottom-right' => ['icon' => '↘', 'label' => 'Bas droite'],
                    ] as $val => $opt)
                        <div class="col-6">
                            <label class="d-flex align-items-center gap-2 p-2 border rounded cursor-pointer
                                {{ ($data['player_watermark_position'] ?? 'top-right') === $val ? 'border-primary bg-primary bg-opacity-10' : '' }}"
                                   style="cursor:pointer;">
                                <input type="radio" name="player_watermark_position" value="{{ $val }}"
                                       class="form-check-input mt-0"
                                       {{ ($data['player_watermark_position'] ?? 'top-right') === $val ? 'checked' : '' }}>
                                <span class="fs-5">{{ $opt['icon'] }}</span>
                                <span class="small">{{ $opt['label'] }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Bouton enregistrer bas --}}
    <div class="d-flex justify-content-end mb-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save me-2"></i>Enregistrer les paramètres
        </button>
    </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Afficher/masquer délai skip intro selon l'état du toggle
    const toggle = document.getElementById('skipIntroToggle');
    const delayBox = document.getElementById('skipIntroDelay');
    if (toggle && delayBox) {
        delayBox.style.opacity = toggle.checked ? '1' : '0.4';
        toggle.addEventListener('change', () => {
            delayBox.style.opacity = toggle.checked ? '1' : '0.4';
        });
    }
    // Highlight radio watermark au clic
    document.querySelectorAll('input[name="player_watermark_position"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('input[name="player_watermark_position"]').forEach(r => {
                r.closest('label').classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            });
            this.closest('label').classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        });
    });
});
</script>

@endsection
