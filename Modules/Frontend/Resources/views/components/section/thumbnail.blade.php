<div class="detail-page-banner">
    <div class="video-player-wrapper">
        <!-- Video.js core -->
        <link rel="stylesheet" href="{{ asset('css/video-js.css') }}">
        <link rel="stylesheet" href="{{ asset('css/videojs.ima.css') }}">
        <script src="{{ asset('js/videojs/video.min.js') }}"></script>
        <script src="{{ asset('js/videojs/videojs-youtube.min.js') }}"></script>
        {{-- IMA chargé seulement pour le contenu non-livetv --}}
        @if(!isset($content_type) || $content_type !== 'livetv')
        <script src="{{ asset('js/videojs/videojs-contrib-ads.min.js') }}"></script>
        <script src="{{ asset('js/videojs/videojs.ima.min.js') }}"></script>
        {{-- ima3.js (SDK Google IMA) chargé lazily via JS uniquement si VAST ads existent --}}
        @endif

        <div class="video-player">


            {{-- Bouton Unmute — permanent, visible quand son est muté --}}
            <button id="vp-unmute-btn"
                    title="Activer le son"
                    style="display:none;position:absolute;bottom:70px;right:16px;z-index:100;
                           background:rgba(0,0,0,.78);backdrop-filter:blur(6px);
                           color:#fff;border:2px solid rgba(255,255,255,.55);border-radius:50px;
                           padding:10px 20px 10px 14px;font-size:.9rem;font-weight:700;
                           cursor:pointer;align-items:center;gap:8px;
                           transition:background .2s,transform .15s;animation:vp-badge-in .3s ease;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="22" height="22" style="flex-shrink:0">
                    <path d="M16.5 12A4.5 4.5 0 0 0 14 7.97V9.5l2.45 2.45c.03-.3.05-.61.05-.95zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3 3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4 9.91 6.09 12 8.18V4z"/>
                </svg>
                <span>Son désactivé — Cliquez ici</span>
            </button>
            @php
                $vp_autoplay         = setting('player_autoplay', 0);
                $vp_muted            = setting('player_muted_on_load', 0) || $vp_autoplay;
                $vp_continue         = setting('player_continue_watching', 1);
                $vp_skip_intro       = setting('player_skip_intro', 1);
                $vp_skip_intro_delay = (int) setting('player_skip_intro_delay', 5);
                $vp_quality          = setting('player_default_quality', 'auto');
                $vp_speed            = setting('player_speed_control', 1);
                $vp_download         = setting('player_download_enabled', 0);
                $vp_subtitles        = setting('player_subtitles_default', 0);
                $vp_watermark        = setting('player_watermark_position', 'top-right');
                $vp_forward          = (int) setting('player_forward_seconds', 10);
                $vp_continue_watch   = $vp_continue && isset($continue_watch) && $continue_watch;
            @endphp
            <video id="videoPlayer" class="video-js vjs-default-skin vjs-ima"
                   controls width="560" height="315"
                   {{ $vp_muted ? 'muted' : '' }}
                   {{ $vp_autoplay ? 'autoplay' : '' }}
                   poster="{{ $thumbnail_image }}"
                   data-setup='{"muted": {{ $vp_muted ? "true" : "false" }}, "autoplay": {{ $vp_autoplay ? "true" : "false" }}}'
                   data-type="{{ $type }}"
                   content-video-type="{{ $content_video_type }}"
                   data-continue-watch="{{ $vp_continue_watch ? 'true' : 'false' }}"
                   data-movie-access="{{ $dataAccess ?? '' }}"
                   data-plan-id="{{ $plan_id ?? '' }}"
                   data-watch-time="{{ $watched_time ?? 0 }}"
                   @if ($type != 'Local') data-encrypted="{{ $data }}" @endif
                   @if (isset($content_type) && isset($content_id))
                       data-contentType="{{ $content_type }}"
                       data-contentId="{{ $content_id }}"
                   @endif
                   data-forward-seconds="{{ $vp_forward }}"
                   data-backward-seconds="{{ $vp_forward }}"
                   data-skip-intro="{{ $vp_skip_intro ? 'true' : 'false' }}"
                   data-skip-intro-delay="{{ $vp_skip_intro_delay }}"
                   data-default-quality="{{ $vp_quality }}"
                   data-speed-control="{{ $vp_speed ? 'true' : 'false' }}"
                   data-download-enabled="{{ $vp_download ? 'true' : 'false' }}"
                   data-subtitles-default="{{ $vp_subtitles ? 'true' : 'false' }}"
                   data-watermark-position="{{ $vp_watermark }}"
                   playsinline webkit-playsinline
                   x-webkit-airplay="allow"
                   preload="metadata">
                @if ($type == 'Local')
                    <source src="{{ $data }}" type="video/mp4" id="videoSource">
                @endif
            </video>

            <!-- Vimeo iframe -->
            <div id="vimeoContainer">
                <iframe id="vimeoIframe" frameborder="0"
                        allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
            </div>

            <!-- Custom Ad Modal -->
            <div id="customAdModal">
                <div id="customAdContent">
                    <button id="customAdCloseBtn">&times;</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ mix('js/videoplayer.min.js') }}"></script>
<script>
    var isAuthenticated  = {{ auth()->check() ? 'true' : 'false' }};
    var loginUrl         = "{{ route('login') }}";
    var skipTrailerText      = "{{ __('messages.skip_trailer') }}";
    var skipIntroText        = "{{ __('messages.skip_intro') }}";
    var previousEpisodeText  = "{{ __('messages.previous_episode') }}";
    var nextEpisodeText      = "{{ __('messages.next_episode') }}";
    var backwardButtonText   = "{{ __('messages.backward_button') }}";
    var forwardButtonText    = "{{ __('messages.forward_button') }}";
    var defaultText          = "{{ __('messages.default') }}";
    var errorLoadingAdText   = "{{ __('messages.error_loading_ad') }}";
    var nextText             = "{{ __('messages.next') }}";

    // ── Paramètres du lecteur (depuis Paramètres > Lecteur Vidéo) ────────────
    window.playerSettings = {
        autoplay:          {{ $vp_autoplay ? 'true' : 'false' }},
        mutedOnLoad:       {{ $vp_muted ? 'true' : 'false' }},
        continueWatching:  {{ $vp_continue ? 'true' : 'false' }},
        skipIntro:         {{ $vp_skip_intro ? 'true' : 'false' }},
        skipIntroDelay:    {{ $vp_skip_intro_delay }},
        defaultQuality:    "{{ $vp_quality }}",
        speedControl:      {{ $vp_speed ? 'true' : 'false' }},
        downloadEnabled:   {{ $vp_download ? 'true' : 'false' }},
        subtitlesDefault:  {{ $vp_subtitles ? 'true' : 'false' }},
        watermarkPosition: "{{ $vp_watermark }}",
        forwardSeconds:    {{ $vp_forward }},
    };
</script>

<style>
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
        background: rgba(0,0,0,.9); backdrop-filter: blur(8px);
        align-items: center; justify-content: center;
        opacity: 0; transition: opacity .4s;
    }
    #customAdModal.show { opacity: 1; }
    #customAdContent {
        max-width: 900px; width: 90%; max-height: 85vh;
        position: relative; background: #000; border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,.7);
        display: flex; align-items: center; justify-content: center;
        overflow: hidden; border: 1px solid rgba(255,255,255,.1);
    }
    #customAdContent img, #customAdContent video, #customAdContent iframe {
        width: 100%; height: auto; max-height: 85vh;
        object-fit: contain; display: block;
    }
    #customAdCloseBtn {
        position: absolute; top: 15px; right: 15px;
        background: rgba(255,0,0,.85); color: #fff;
        border: 2px solid #fff; border-radius: 50%;
        width: 36px; height: 36px; font-size: 22px;
        line-height: 1; display: flex; align-items: center;
        justify-content: center; cursor: pointer; z-index: 10001;
        transition: all .2s; padding: 0;
    }
    #customAdCloseBtn:hover { background: #f00; transform: scale(1.1); }

    /* IMA skip masqués */
    .ima-skip-container, .ima-skip-button, div[class*="ima-ad-skip"],
    div[class*="ima_skip"], div[class*="ima-skip"], button[class*="ima-skip"],
    button[class*="ima_skip"], div[class*="skip-button"], .ima-skip-button-container,
    [class*="skip-container"], [data-skip-button], [data-skip-container] {
        opacity: 0 !important; visibility: hidden !important; display: none !important;
        pointer-events: none !important; width: 0 !important; height: 0 !important;
        position: absolute !important; top: -9999px !important; left: -9999px !important;
    }
    .ima-controls-div, .ima-progress-div, .ima-countdown-div,
    .ima-seek-bar-div, .ima-progress-bar-div {
        display: block !important; visibility: visible !important;
        opacity: 1 !important; pointer-events: auto !important;
    }
    .vjs-texttrack-settings { display: none !important; }
    .video-js .vjs-text-track-cue,
    .video-js .vjs-text-track-cue div { font-size: 1.4em !important; line-height: normal !important; }
    @supports (-webkit-touch-callout: none) {
        .video-js .vjs-text-track-display,
        .video-js .vjs-text-track-cue,
        .video-js .vjs-text-track-cue div {
            font-size: 1.4em !important; line-height: normal !important;
            transform: none !important; -webkit-transform: none !important;
        }
        .video-js .vjs-big-play-button { pointer-events: auto !important; opacity: 1 !important; }
        .video-js .vjs-big-play-button.vjs-hidden { display: block !important; opacity: 1 !important; }
    }
    @media (max-width: 768px) {
        .video-js .vjs-text-track-cue,
        .video-js .vjs-text-track-cue div { font-size: 16px !important; }
    }
    @media (max-width: 480px) {
        .video-js .vjs-text-track-cue,
        .video-js .vjs-text-track-cue div,
        video::-webkit-media-text-track-display { font-size: 13px !important; }
    }
    .video-js.vjs-ima { overflow: visible; }
    .video-js.vjs-ima .vjs-ima-ad-container {
        position: absolute; top: 0; right: 0; bottom: 0; left: 0; pointer-events: none;
    }
    .video-js.vjs-ima .vjs-ima-ad-container > div { pointer-events: auto; }
    .vjs-skip-ad-button {
        position: absolute; bottom: 80px; right: 20px;
        background: rgba(15,15,15,.85); backdrop-filter: blur(10px);
        color: #fff; padding: 12px 24px;
        border: 1px solid rgba(255,255,255,.2); border-radius: 4px;
        cursor: pointer; z-index: 10001 !important;
        font-size: 16px; font-weight: 600; transition: all .2s;
        display: flex; align-items: center; gap: 8px;
    }
    .vjs-skip-ad-button:hover { background: rgba(255,255,255,.1); border-color: #fff; transform: scale(1.05); }
    .overlay-ad {
        position: absolute; bottom: 80px; left: 20px; z-index: 1000;
        background: rgba(15,15,15,.9); backdrop-filter: blur(8px);
        padding: 8px; border-radius: 8px; border: 1px solid rgba(255,255,255,.2);
    }
    .video-player { position: relative; z-index: 0; }
    @keyframes vp-badge-in {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 768px) {
        .vjs-skip-ad-button { bottom: 70px; padding: 8px 16px; font-size: 14px; }
        .overlay-ad { bottom: 80px; left: 10px; right: 10px; text-align: center; }
        #customAdContent { width: 95%; border-radius: 12px; }
        #customAdCloseBtn { width: 32px; height: 32px; top: 10px; right: 10px; font-size: 18px; }
    }
    @media (max-width: 480px) {
        .vjs-skip-ad-button { bottom: 110px; padding: 6px 12px; font-size: 12px; }
        .overlay-ad { bottom: 110px; }
        #customAdContent { width: 98%; max-height: 70vh; }
    }
</style>

<script>
(function () {
    var btn = document.getElementById('vp-unmute-btn');
    var vid = document.getElementById('videoPlayer');
    if (!btn || !vid) return;

    function syncBtn() {
        var isMuted = vid.muted || vid.volume === 0;
        btn.style.display = isMuted ? 'flex' : 'none';
    }

    // Clic sur le bouton → unmute + hide
    btn.addEventListener('click', function () {
        vid.muted  = false;
        vid.volume = vid.volume > 0 ? vid.volume : 1;
        // Essayer via l'instance Video.js si disponible
        if (window.videojs && videojs.getPlayer && videojs.getPlayer('videoPlayer')) {
            var p = videojs.getPlayer('videoPlayer');
            p.muted(false);
            if (p.volume() === 0) p.volume(1);
        }
        syncBtn();
    });

    btn.addEventListener('mouseenter', function () {
        btn.style.background = 'rgba(229,9,20,.9)';
        btn.style.borderColor = '#fff';
        btn.style.transform = 'scale(1.04)';
    });
    btn.addEventListener('mouseleave', function () {
        btn.style.background = 'rgba(0,0,0,.78)';
        btn.style.borderColor = 'rgba(255,255,255,.55)';
        btn.style.transform = 'scale(1)';
    });

    // Surveiller les changements de volume/mute
    vid.addEventListener('volumechange', syncBtn);

    // Afficher dès que la vidéo commence à jouer si elle est mutée
    vid.addEventListener('play', syncBtn);
    vid.addEventListener('playing', syncBtn);

    // Synchronisation initiale après chargement Video.js
    var attempts = 0;
    var checkInterval = setInterval(function () {
        attempts++;
        syncBtn();
        // Aussi vérifier via l'instance Video.js
        if (window.videojs && typeof videojs.getPlayer === 'function') {
            var p = videojs.getPlayer('videoPlayer');
            if (p) {
                p.on('volumechange', syncBtn);
                p.on('play', syncBtn);
                p.on('playing', syncBtn);
                clearInterval(checkInterval);
                syncBtn();
            }
        }
        if (attempts > 20) clearInterval(checkInterval);
    }, 300);
})();
</script>
