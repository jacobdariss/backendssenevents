{{--
  Composant : uploader Cloudflare Stream (Direct Creator Upload)
  Usage : @include('entertainment::backend.components.cf_stream_uploader', ['fieldPrefix' => 'video'])
--}}
@php
    $cfEnabled = config('cloudflare.stream.enabled') && !empty(config('cloudflare.stream.account_id'));
    // Lire aussi depuis settings DB
    if (!$cfEnabled) {
        $dbEnabled  = \Illuminate\Support\Facades\DB::table('settings')->whereNull('deleted_at')->where('name','cf_stream_enabled')->value('val');
        $dbAccount  = \Illuminate\Support\Facades\DB::table('settings')->whereNull('deleted_at')->where('name','cf_stream_account_id')->value('val');
        $cfEnabled  = ($dbEnabled == '1') && !empty($dbAccount);
    }
    $prefix = $fieldPrefix ?? 'video';
@endphp

{{-- Toujours afficher le panel pour le switch JS --}}
<div id="cf-stream-panel-inner-{{ $prefix }}" class="cf-stream-uploader mt-2">

@if(!$cfEnabled)
<div class="alert py-2 mb-3 d-flex align-items-center gap-2" style="background:rgba(239,159,39,0.1);border:1px solid rgba(239,159,39,0.3);border-radius:8px;font-size:.82rem;color:#EF9F27">
    <i class="ph ph-warning"></i>
    Cloudflare Stream non configuré —
    <a href="{{ route('backend.settings.storage-settings') }}" class="ms-1" style="color:#EF9F27;font-weight:600">Configurer maintenant</a>
</div>
@endif

    {{-- Zone de drop / sélection fichier --}}
    <div id="cf-drop-zone-{{ $prefix }}"
         style="border:2px dashed rgba(55,138,221,0.4);border-radius:12px;padding:32px 20px;text-align:center;cursor:pointer;transition:.2s;background:rgba(55,138,221,0.04)"
         onmouseover="this.style.background='rgba(55,138,221,0.08)'"
         onmouseout="this.style.background='rgba(55,138,221,0.04)'">
        <i class="ph ph-cloud-arrow-up" style="font-size:2.5rem;color:#85B7EB;display:block;margin-bottom:8px"></i>
        <div style="font-size:.9rem;color:#85B7EB;font-weight:500">Glisser-déposer une vidéo ici</div>
        <div style="font-size:.75rem;color:rgba(255,255,255,0.4);margin-top:4px">ou</div>
        <label class="btn btn-sm mt-2" style="background:rgba(55,138,221,0.15);color:#85B7EB;border:1px solid rgba(55,138,221,0.3);cursor:pointer">
            <i class="ph ph-folder-open me-1"></i> Sélectionner un fichier
            <input type="file" id="cf-file-input-{{ $prefix }}" accept="video/*" style="display:none">
        </label>
        <div style="font-size:.7rem;color:rgba(255,255,255,0.3);margin-top:8px">MP4, MOV, MKV · Max 20 Go · Upload direct vers Cloudflare Stream</div>
    </div>

    {{-- Fichier sélectionné --}}
    <div id="cf-file-info-{{ $prefix }}" class="d-none mt-2 p-2 rounded" style="background:rgba(255,255,255,0.05);font-size:.8rem">
        <i class="ph ph-file-video me-1" style="color:#85B7EB"></i>
        <span id="cf-file-name-{{ $prefix }}" class="text-muted"></span>
        <span id="cf-file-size-{{ $prefix }}" class="text-muted ms-2"></span>
    </div>

    {{-- Barre de progression --}}
    <div id="cf-progress-wrap-{{ $prefix }}" class="d-none mt-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span style="font-size:.75rem;color:#85B7EB">
                <i class="ph ph-cloud-arrow-up me-1"></i>
                <span id="cf-progress-label-{{ $prefix }}">Préparation…</span>
            </span>
            <span id="cf-progress-pct-{{ $prefix }}" style="font-size:.75rem;color:#85B7EB;font-weight:600">0%</span>
        </div>
        <div style="background:rgba(255,255,255,0.1);border-radius:6px;height:6px;overflow:hidden">
            <div id="cf-progress-bar-{{ $prefix }}" style="height:100%;width:0%;background:#378ADD;border-radius:6px;transition:width .3s"></div>
        </div>
    </div>

    {{-- Statut --}}
    <div id="cf-status-{{ $prefix }}" class="d-none mt-2 p-2 rounded d-flex align-items-center gap-2" style="font-size:.8rem">
        <i id="cf-status-icon-{{ $prefix }}" class="ph ph-spinner"></i>
        <span id="cf-status-text-{{ $prefix }}"></span>
    </div>

    {{-- Boutons --}}
    <div class="mt-3 d-flex gap-2">
        <button type="button" id="cf-upload-btn-{{ $prefix }}" class="btn btn-sm btn-primary d-none"
                onclick="cfStreamStart('{{ $prefix }}')">
            <i class="ph ph-cloud-arrow-up me-1"></i>Uploader vers Cloudflare Stream
        </button>
        <button type="button" id="cf-cancel-btn-{{ $prefix }}" class="btn btn-sm btn-outline-secondary d-none"
                onclick="cfStreamCancel('{{ $prefix }}')">
            <i class="ph ph-x me-1"></i>Annuler
        </button>
    </div>

    {{-- Champs cachés --}}
    <input type="hidden" name="cf_stream_uid" id="cf-uid-{{ $prefix }}" value="{{ old('cf_stream_uid') }}">

</div>

@once
@push('after-scripts')
<script src="https://cdn.jsdelivr.net/npm/tus-js-client@3.1.0/dist/tus.min.js"></script>
<script>
window._cfUploads = {};

function cfStreamFmt(b) {
    if (b < 1048576) return (b/1024).toFixed(1)+' KB';
    if (b < 1073741824) return (b/1048576).toFixed(1)+' MB';
    return (b/1073741824).toFixed(2)+' Go';
}

function cfStreamStatus(p, type, text) {
    const el   = document.getElementById('cf-status-'+p);
    const icon = document.getElementById('cf-status-icon-'+p);
    const txt  = document.getElementById('cf-status-text-'+p);
    if (!el) return;
    el.classList.remove('d-none');
    const s = {
        processing: { bg:'rgba(55,138,221,0.1)', color:'#85B7EB', i:'ph-spinner' },
        success:    { bg:'rgba(29,158,117,0.1)', color:'#5DCAA5', i:'ph-check-circle' },
        error:      { bg:'rgba(239,68,68,0.1)',  color:'#F0997B', i:'ph-warning-circle' },
    }[type] || { bg:'rgba(55,138,221,0.1)', color:'#85B7EB', i:'ph-spinner' };
    el.style.background = s.bg;
    el.style.color = s.color;
    if (icon) icon.className = 'ph '+s.i;
    if (txt)  txt.textContent = text;
}

function cfStreamSelectFile(p, file) {
    window._cfUploads[p] = { file };
    const n = document.getElementById('cf-file-name-'+p);
    const s = document.getElementById('cf-file-size-'+p);
    const i = document.getElementById('cf-file-info-'+p);
    const b = document.getElementById('cf-upload-btn-'+p);
    if (n) n.textContent = file.name;
    if (s) s.textContent = '('+cfStreamFmt(file.size)+')';
    if (i) i.classList.remove('d-none');
    if (b) b.classList.remove('d-none');
}

function cfStreamStart(p) {
    const up = window._cfUploads[p];
    if (!up?.file) return;
    const file = up.file;
    const pw = document.getElementById('cf-progress-wrap-'+p);
    const pb = document.getElementById('cf-progress-bar-'+p);
    const pp = document.getElementById('cf-progress-pct-'+p);
    const bb = document.getElementById('cf-upload-btn-'+p);
    const cb = document.getElementById('cf-cancel-btn-'+p);
    if (pw) pw.classList.remove('d-none');
    if (bb) bb.classList.add('d-none');
    if (cb) cb.classList.remove('d-none');
    cfStreamStatus(p, 'processing', 'Obtention URL upload…');

    const useTus = file.size > 200*1024*1024;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const endpoint = useTus ? '/api/cf-stream/tus-url' : '/api/cf-stream/upload-url';
    const body = useTus
        ? JSON.stringify({ file_size: file.size, name: file.name })
        : JSON.stringify({ name: file.name });

    fetch(endpoint, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Erreur API');
        cfStreamStatus(p, 'processing', 'Upload en cours…');

        if (useTus) {
            const tusUp = new tus.Upload(file, {
                endpoint: data.tusEndpoint,
                retryDelays: [0, 3000, 5000, 10000],
                chunkSize: 50*1024*1024,
                metadata: { filename: file.name, filetype: file.type },
                onProgress(u, t) {
                    const pct = Math.round(u/t*100);
                    if (pb) pb.style.width = pct+'%';
                    if (pp) pp.textContent = pct+'%';
                },
                onSuccess() { cfStreamDone(p, data.uid); },
                onError(e) { cfStreamStatus(p, 'error', 'Erreur : '+e.message); if (cb) cb.classList.remove('d-none'); }
            });
            window._cfUploads[p].tus = tusUp;
            tusUp.start();
        } else {
            const fd = new FormData(); fd.append('file', file);
            const xhr = new XMLHttpRequest();
            window._cfUploads[p].xhr = xhr;
            xhr.upload.onprogress = e => {
                if (!e.lengthComputable) return;
                const pct = Math.round(e.loaded/e.total*100);
                if (pb) pb.style.width = pct+'%';
                if (pp) pp.textContent = pct+'%';
            };
            xhr.onload  = () => xhr.status < 300 ? cfStreamDone(p, data.uid) : cfStreamStatus(p, 'error', 'HTTP '+xhr.status);
            xhr.onerror = () => cfStreamStatus(p, 'error', 'Erreur réseau');
            xhr.open('POST', data.uploadURL);
            xhr.send(fd);
        }
    })
    .catch(e => cfStreamStatus(p, 'error', e.message));
}

function cfStreamDone(p, uid) {
    const pb = document.getElementById('cf-progress-bar-'+p);
    const pp = document.getElementById('cf-progress-pct-'+p);
    const ui = document.getElementById('cf-uid-'+p);
    const cb = document.getElementById('cf-cancel-btn-'+p);
    if (pb) pb.style.width = '100%';
    if (pp) pp.textContent = '100%';
    if (ui) ui.value = uid;
    if (cb) cb.classList.add('d-none');
    cfStreamStatus(p, 'processing', 'Vidéo envoyée — traitement Cloudflare…');
    cfStreamPoll(p, uid, 0);
}

function cfStreamPoll(p, uid, n) {
    if (n > 24) { cfStreamStatus(p, 'success', 'Upload terminé — traitement en arrière-plan.'); return; }
    fetch('/api/cf-stream/status/'+uid, { headers:{'Accept':'application/json'} })
    .then(r => r.json())
    .then(d => {
        const st = d.data?.status;
        if (st === 'ready') {
            const vi = document.getElementById('video_url_input');
            if (vi && d.data?.embedUrl) vi.value = d.data.embedUrl;
            cfStreamStatus(p, 'success', '✓ Vidéo prête sur Cloudflare Stream');
        } else if (st === 'error') {
            cfStreamStatus(p, 'error', 'Erreur de traitement Cloudflare Stream.');
        } else {
            cfStreamStatus(p, 'processing', 'Traitement en cours… ('+( n+1)+'/24)');
            setTimeout(() => cfStreamPoll(p, uid, n+1), 5000);
        }
    })
    .catch(() => setTimeout(() => cfStreamPoll(p, uid, n+1), 5000));
}

function cfStreamCancel(p) {
    const up = window._cfUploads[p] || {};
    if (up.tus) up.tus.abort();
    if (up.xhr) up.xhr.abort();
    ['cf-progress-wrap-','cf-status-'].forEach(id => {
        const el = document.getElementById(id+p);
        if (el) el.classList.add('d-none');
    });
    const bb = document.getElementById('cf-upload-btn-'+p);
    const cb = document.getElementById('cf-cancel-btn-'+p);
    if (bb) bb.classList.remove('d-none');
    if (cb) cb.classList.add('d-none');
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[id^="cf-drop-zone-"]').forEach(el => {
        const p = el.id.replace('cf-drop-zone-', '');
        const inp = document.getElementById('cf-file-input-'+p);
        el.addEventListener('dragover', e => { e.preventDefault(); el.style.borderColor='#378ADD'; });
        el.addEventListener('dragleave', () => { el.style.borderColor='rgba(55,138,221,0.4)'; });
        el.addEventListener('drop', e => {
            e.preventDefault();
            el.style.borderColor='rgba(55,138,221,0.4)';
            if (e.dataTransfer.files[0]) cfStreamSelectFile(p, e.dataTransfer.files[0]);
        });
        if (inp) inp.addEventListener('change', () => { if (inp.files[0]) cfStreamSelectFile(p, inp.files[0]); });
    });
});
</script>
@endpush
@endonce
