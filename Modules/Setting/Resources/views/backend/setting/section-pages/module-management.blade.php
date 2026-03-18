@extends('setting::backend.setting.index')

@section('title')
    Gestion des modules
@endsection

@section('settings-content')
    <div class="col-md-12 mb-3 d-flex justify-content-between align-items-center">
        <h3 class="mb-0"><i class="ph ph-puzzle-piece icon"></i> Gestion des modules</h3>
    </div>

    <p class="text-muted mb-4">
        Activez ou désactivez les modules de l'application. Les modules <strong>core</strong> ne peuvent pas être modifiés.
    </p>

    <div id="module-toggle-feedback" class="alert d-none mb-3" role="alert"></div>

    @foreach ($modules as $module)
        <div class="form-group border-bottom pb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-semibold">{{ $module['name'] }}</span>
                    @if ($module['core'])
                        <span class="badge bg-secondary ms-2">Core</span>
                    @endif
                </div>
                @if ($module['core'])
                    <span class="badge bg-success-subtle text-success">Actif (protégé)</span>
                @else
                    <div class="form-check form-switch m-0">
                        <input
                            class="form-check-input module-toggle"
                            type="checkbox"
                            id="module-{{ $module['name'] }}"
                            data-module="{{ $module['name'] }}"
                            {{ $module['enabled'] ? 'checked' : '' }}
                        />
                    </div>
                @endif
            </div>
        </div>
    @endforeach
@endsection

@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const feedback  = document.getElementById('module-toggle-feedback');

    document.querySelectorAll('.module-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const moduleName = this.dataset.module;
            const enabled    = this.checked;
            const self       = this;
            self.disabled    = true;

            fetch('{{ route('backend.settings.toggle-module') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ module: moduleName, enabled: enabled ? 1 : 0 })
            })
            .then(r => r.json())
            .then(function (data) {
                self.disabled = false;
                feedback.classList.remove('d-none', 'alert-success', 'alert-danger');
                if (data.success) {
                    feedback.classList.add('alert-success');
                    feedback.textContent = 'Module "' + moduleName + '" ' + (enabled ? 'activé' : 'désactivé') + '. Redémarrez le cache si nécessaire.';
                } else {
                    self.checked = !enabled;
                    feedback.classList.add('alert-danger');
                    feedback.textContent = data.message || 'Erreur lors de la mise à jour.';
                }
                setTimeout(function () { feedback.classList.add('d-none'); }, 4000);
            })
            .catch(function () {
                self.disabled = false;
                self.checked  = !enabled;
                feedback.classList.remove('d-none', 'alert-success');
                feedback.classList.add('alert-danger');
                feedback.textContent = 'Erreur réseau. Veuillez réessayer.';
                setTimeout(function () { feedback.classList.add('d-none'); }, 4000);
            });
        });
    });
});
</script>
@endpush
