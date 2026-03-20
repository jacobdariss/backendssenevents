<div class="detail-page-banner">
    <div class="video-player-wrapper" id="vp-wrapper">

        {{-- ── Poster placeholder (affiché au chargement, avant tout JS player) ── --}}
        <div id="vp-poster" class="vp-poster-overlay"
             style="position:relative;width:100%;background:#000;cursor:pointer;aspect-ratio:16/9;overflow:hidden;">
            <img src="{{ $thumbnail_image }}"
                 alt="poster"
                 style="width:100%;height:100%;object-fit:cover;display:block;opacity:.85;">

            {{-- Bouton Play central --}}
            <div id="vp-play-btn"
                 style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
                        width:72px;height:72px;border-radius:50%;
                        background:rgba(255,255,255,.15);backdrop-filter:blur(6px);
                        border:2px solid rgba(255,255,255,.5);
                        display:flex;align-items:center;justify-content:center;
                        transition:transform .2s,background .2s;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                     fill="white" width="32" height="32">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </div>

            {{-- Spinner (caché, affiché pendant le chargement) --}}
            <div id="vp-spinner"
                 style="display:none;position:absolute;top:50%;left:50%;
                        transform:translate(-50%,-50%);
                        width:48px;height:48px;border-radius:50%;
                        border:3px solid rgba(255,255,255,.2);
                        border-top-color:#fff;
                        animation:vp-spin .7s linear infinite;">
            </div>
        </div>

        {{-- ── Player container (caché jusqu'au clic) ── --}}
        <div id="vp-player-container" style="display:none;">
            <div class="video-player" style="position:relative;">
                <video id="videoPlayer"
                       class="video-js vjs-default-skin vjs-ima"
                       controls width="560" height="315" muted
                       poster="{{ $thumbnail_image }}"
                       data-setup='{"muted": true}'
                       data-type="{{ $type }}"
                       content-video-type="{{ $content_video_type }}"
                       data-continue-watch="{{ isset($continue_watch) && $continue_watch ? 'true' : 'false' }}"
                       data-movie-access="{{ $dataAccess ?? '' }}"
                       data-plan-id="{{ $plan_id ?? '' }}"
                       data-watch-time="{{ $watched_time ?? 0 }}"
                       @if ($type != 'Local') data-encrypted="{{ $data }}" @endif
                       @if (isset($content_type) && isset($content_id))
                           data-contentType="{{ $content_type }}"
                           data-contentId="{{ $content_id }}"
                       @endif
                       data-forward-seconds="{{ setting('forward_seconds', 30) }}"
                       data-backward-seconds="{{ setting('backward_seconds', 30) }}"
                       playsinline webkit-playsinline
                       x-webkit-airplay="allow"
                       preload="none">
                    @if ($type == 'Local')
                        <source src="{{ $data }}" type="video/mp4" id="videoSource">
                    @endif
                </video>

                {{-- Vimeo iframe --}}
                <div id="vimeoContainer">
                    <iframe id="vimeoIframe" frameborder="0"
                            allow="autoplay; fullscreen; picture-in-picture" allowfullscreen>
                    </iframe>
                </div>

                {{-- Custom Ad Modal --}}
                <div id="customAdModal">
                    <div id="customAdContent">
                        <button id="customAdCloseBtn">&times;</button>
                    </div>
                </div>

                {{-- Badge son muté --}}
                <div id="vp-muted-badge"
                     style="display:none;position:absolute;top:14px;right:14px;z-index:999;
                            background:rgba(0,0,0,.65);color:#fff;font-size:.75rem;font-weight:600;
                            padding:5px 10px;border-radius:20px;backdrop-filter:blur(4px);
                            display:flex;align-items:center;gap:6px;
                            animation:vp-fadein .3s ease;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="14" height="14">
                        <path d="M16.5 12A4.5 4.5 0 0 0 14 7.97V9.5l2.45 2.45c.03-.3.05-.61.05-.95zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3 3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4 9.91 6.09 12 8.18V4z"/>
                    </svg>
                    Cliquez pour activer le son
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Scripts player — injectés uniquement au clic, PAS au chargement de la page --}}
<script>
(function() {
    var loaded   = false;
    var poster   = document.getElementById('vp-poster');
    var playBtn  = document.getElementById('vp-play-btn');
    var spinner  = document.getElementById('vp-spinner');
    var container = document.getElementById('vp-player-container');
    var mutedBadge = document.getElementById('vp-muted-badge');

    // Hover effect sur le bouton play
    if (playBtn) {
        playBtn.addEventListener('mouseenter', function() {
            playBtn.style.background = 'rgba(255,255,255,.25)';
            playBtn.style.transform  = 'translate(-50%,-50%) scale(1.1)';
        });
        playBtn.addEventListener('mouseleave', function() {
            playBtn.style.background = 'rgba(255,255,255,.15)';
            playBtn.style.transform  = 'translate(-50%,-50%) scale(1)';
        });
    }

    function loadPlayer() {
        if (loaded) return;
        loaded = true;

        // Afficher spinner, masquer bouton play
        if (playBtn)  playBtn.style.display  = 'none';
        if (spinner)  spinner.style.display  = 'block';

        // Injection dynamique des CSS
        var cssFiles = [
            '{{ asset("css/video-js.css") }}',
            '{{ asset("css/videojs.ima.css") }}',
        ];
        cssFiles.forEach(function(href) {
            var l = document.createElement('link');
            l.rel  = 'stylesheet';
            l.href = href;
            document.head.appendChild(l);
        });

        // Injection dynamique des JS en séquence
        var scripts = [
            '{{ asset("js/videojs/video.min.js") }}',
            '{{ asset("js/videojs/videojs-youtube.min.js") }}',
            '{{ asset("js/videojs/ima3.js") }}',
            '{{ asset("js/videojs/videojs-contrib-ads.min.js") }}',
            '{{ asset("js/videojs/videojs.ima.min.js") }}',
            '{{ mix("js/videoplayer.min.js") }}',
        ];

        function loadScript(index) {
            if (index >= scripts.length) {
                // Tous les scripts chargés — afficher le player
                if (poster)    poster.style.display    = 'none';
                if (container) container.style.display = '';
                // Afficher badge son muté 3 secondes
                showMutedBadge();
                return;
            }
            var s = document.createElement('script');
            s.src = scripts[index];
            s.onload = function() { loadScript(index + 1); };
            s.onerror = function() { loadScript(index + 1); }; // continuer même si un script échoue
            document.head.appendChild(s);
        }

        loadScript(0);
    }

    function showMutedBadge() {
        if (!mutedBadge) return;
        mutedBadge.style.display = 'flex';
        // Masquer après 3 secondes
        setTimeout(function() {
            mutedBadge.style.opacity = '0';
            mutedBadge.style.transition = 'opacity .5s ease';
            setTimeout(function() { mutedBadge.style.display = 'none'; }, 500);
        }, 3000);

        // Masquer aussi si l'utilisateur clique sur le player (il a activé le son)
        var video = document.getElementById('videoPlayer');
        if (video) {
            video.addEventListener('volumechange', function() {
                if (!video.muted) {
                    mutedBadge.style.opacity = '0';
                    setTimeout(function() { mutedBadge.style.display = 'none'; }, 300);
                }
            }, { once: true });
        }
    }

    // Déclencher au clic sur le poster
    if (poster) {
        poster.addEventListener('click', loadPlayer);
    }

    // Déclencher aussi si watchNowButton est cliqué (bouton "Regarder maintenant" en bas)
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.id === 'watchNowButton' || e.target.closest('#watchNowButton'))) {
            loadPlayer();
        }
    });

    // Si continue_watch = true → charger le player directement (utilisateur revient sur sa progression)
    @if(isset($continue_watch) && $continue_watch === true)
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(loadPlayer, 100);
        });
    @endif
})();
</script>

<script>
    // Variables globales pour le player (inchangées)
    var isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
    var loginUrl = "{{ route('login') }}";
    var skipTrailerText    = "{{ __('messages.skip_trailer') }}";
    var skipIntroText      = "{{ __('messages.skip_intro') }}";
    var previousEpisodeText = "{{ __('messages.previous_episode') }}";
    var nextEpisodeText    = "{{ __('messages.next_episode') }}";
    var backwardButtonText = "{{ __('messages.backward_button') }}";
    var forwardButtonText  = "{{ __('messages.forward_button') }}";
    var defaultText        = "{{ __('messages.default') }}";
    var errorLoadingAdText = "{{ __('messages.error_loading_ad') }}";
    var nextText           = "{{ __('messages.next') }}";
</script>

<style>
@keyframes vp-spin {
    to { transform: translate(-50%,-50%) rotate(360deg); }
}
@keyframes vp-fadein {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
}

.video-player-wrapper { position: relative; }

#vimeoContainer {
    position: relative; padding-bottom: 56.25%;
    height: 0; overflow: hidden; display: none;
}
#vimeoIframe {
    position: absolute; top: 0; left: 0;
    width: 100%; height: 100%; display: none;
}
#customAdModal {
    display: none; position: absolute; z-index: 9999;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,.85);
    align-items: center; justify-content: center;
}
#customAdContent {
    position: relative; background: transparent;
    display: flex; align-items: center;
    justify-content: center; flex-direction: column;
}
#customAdCloseBtn {
    position: absolute; top: -20px; right: -20px;
    background: #f00; color: #fff; border: none;
    border-radius: 50%; width: 32px; height: 32px;
    font-size: 20px; cursor: pointer; z-index: 2;
}
/* IMA skip buttons masqués */
.ima-skip-container,.ima-skip-button,div[class*="ima-ad-skip"],
div[class*="ima_skip"],div[class*="ima-skip"],button[class*="ima-skip"],
button[class*="ima_skip"],div[class*="skip-button"],.ima-skip-button-container,
[class*="skip-container"],[data-skip-button],[data-skip-container] {
    opacity:0!important;visibility:hidden!important;display:none!important;
    pointer-events:none!important;width:0!important;height:0!important;
    position:absolute!important;top:-9999px!important;left:-9999px!important;
}
.ima-controls-div,.ima-progress-div,.ima-countdown-div,
.ima-seek-bar-div,.ima-progress-bar-div {
    display:block!important;visibility:visible!important;
    opacity:1!important;pointer-events:auto!important;
}
.vjs-texttrack-settings { display:none!important; }
.video-js .vjs-text-track-cue,
.video-js .vjs-text-track-cue div { font-size:1.4em!important;line-height:normal!important; }
@supports (-webkit-touch-callout:none) {
    .video-js .vjs-text-track-display,
    .video-js .vjs-text-track-cue,
    .video-js .vjs-text-track-cue div {
        font-size:1.4em!important;line-height:normal!important;
        transform:none!important;-webkit-transform:none!important;
    }
    .video-js .vjs-big-play-button { pointer-events:auto!important;opacity:1!important; }
    .video-js .vjs-big-play-button.vjs-hidden { display:block!important;opacity:1!important; }
}
@media (max-width:768px) {
    .video-js .vjs-text-track-cue,
    .video-js .vjs-text-track-cue div { font-size:16px!important; }
}
@media (max-width:480px) {
    .video-js .vjs-text-track-cue,
    .video-js .vjs-text-track-cue div,
    video::-webkit-media-text-track-display { font-size:13px!important; }
}
.video-js.vjs-ima { overflow:visible; }
.video-js.vjs-ima .vjs-ima-ad-container {
    position:absolute;top:0;right:0;bottom:0;left:0;pointer-events:none;
}
.video-js.vjs-ima .vjs-ima-ad-container>div { pointer-events:auto; }
.vjs-skip-ad-button {
    position:absolute;bottom:80px;right:20px;
    background:rgba(15,15,15,.85);backdrop-filter:blur(10px);
    color:#fff;padding:12px 24px;
    border:1px solid rgba(255,255,255,.2);border-radius:4px;
    cursor:pointer;z-index:10001!important;font-size:16px;
    font-weight:600;transition:all .2s;
    display:flex;align-items:center;gap:8px;
}
.vjs-skip-ad-button:hover { background:rgba(255,255,255,.1);border-color:#fff;transform:scale(1.05); }
.overlay-ad {
    position:absolute;bottom:80px;left:20px;z-index:1000;
    background:rgba(15,15,15,.9);backdrop-filter:blur(8px);
    padding:8px;border-radius:8px;
    border:1px solid rgba(255,255,255,.2);
}
.video-player { position:relative;z-index:0; }
#customAdModal {
    position:absolute;z-index:10000;top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,.9);backdrop-filter:blur(8px);
    align-items:center;justify-content:center;opacity:0;transition:opacity .4s;
}
#customAdModal.show { opacity:1; }
#customAdContent {
    max-width:900px;width:90%;max-height:85vh;position:relative;
    background:#000;border-radius:16px;
    box-shadow:0 25px 50px -12px rgba(0,0,0,.7);
    display:flex;align-items:center;justify-content:center;
    overflow:hidden;border:1px solid rgba(255,255,255,.1);
}
#customAdContent img,#customAdContent video,#customAdContent iframe {
    width:100%;height:auto;max-height:85vh;object-fit:contain;display:block;
}
#customAdCloseBtn {
    position:absolute;top:15px;right:15px;
    background:rgba(255,0,0,.85);color:#fff;
    border:2px solid #fff;border-radius:50%;
    width:36px;height:36px;font-size:22px;line-height:1;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;z-index:10001;transition:all .2s;
    box-shadow:0 4px 12px rgba(0,0,0,.3);padding:0;
}
#customAdCloseBtn:hover { background:#f00;transform:scale(1.1); }
@media (max-width:768px) {
    #customAdContent { width:95%;border-radius:12px; }
    #customAdCloseBtn { width:32px;height:32px;top:10px;right:10px;font-size:18px; }
    .vjs-skip-ad-button { bottom:70px;padding:8px 16px;font-size:14px; }
    .overlay-ad { bottom:80px;left:10px;right:10px;text-align:center; }
}
@media (max-width:480px) {
    #customAdContent { width:98%;max-height:70vh; }
    .vjs-skip-ad-button { bottom:110px;padding:6px 12px;font-size:12px; }
    .overlay-ad { bottom:110px; }
}
</style>

