<?php

namespace Modules\Partner\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Constant\Models\Constant;
use Modules\Entertainment\Models\Entertainment;
use Modules\Episode\Models\Episode;
use Modules\Partner\Models\Partner;
use Modules\Season\Models\Season;
use Modules\Subscriptions\Models\Plan;

class PartnerSeasonEpisodeController extends Controller
{
    protected function currentPartner(): ?Partner
    {
        return Partner::where('user_id', Auth::id())->first();
    }

    protected function commonData(): array
    {
        $constants       = Constant::whereIn('type', ['upload_type', 'video_quality'])->where('status', 1)->get()->groupBy('type');
        $upload_url_type = $constants->get('upload_type', collect());
        $plan            = Plan::where('status', 1)->get();
        $mediaUrls       = getMediaUrls();
        $assets          = ['textarea'];

        return compact('upload_url_type', 'plan', 'mediaUrls', 'assets');
    }

    // ─────────────────────────────────────────────────────────────
    // SAISONS
    // ─────────────────────────────────────────────────────────────

    public function seasonCreate(int $tvshowId)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $tvshow = Entertainment::where('id', $tvshowId)
            ->where('partner_id', $partner->id)->where('type', 'tvshow')->firstOrFail();

        $page_type     = 'partners/' . $partner->id; // uploads go to partners/{id}/image/
        $partnerFolder = 'partners/' . $partner->id;

        return view('partner::frontend.season_create',
            array_merge($this->commonData(), compact('partner', 'tvshow', 'page_type', 'partnerFolder'))
        );
    }

    public function seasonStore(Request $request, int $tvshowId)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $tvshow = Entertainment::where('id', $tvshowId)
            ->where('partner_id', $partner->id)->where('type', 'tvshow')->firstOrFail();

        $request->validate([
            'name'          => 'required|string|max:255',
            'season_number' => 'required|integer|min:1',
        ]);

        $data = $request->except(['_token']);
        $data['entertainment_id'] = $tvshow->id;
        $data['partner_id']       = $partner->id;
        $data['approval_status']  = 'pending';
        $data['status']           = 0;
        $data['slug']             = Str::slug($request->name) . '-s' . $request->season_number . '-' . time();

        if ($request->access === 'pay-per-view' && $request->price) {
            $data['partner_proposed_price'] = $request->price;
        } else {
            $data['access'] = 'free';
            $data['price']  = null;
        }

        foreach (['poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'season');
            }
        }

        Season::create($data);

        return redirect()->route('partner.tvshow.seasons', $tvshowId)
            ->with('success', __('partner::partner.season_created'));
    }

    public function seasonEdit(int $tvshowId, int $seasonId)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $tvshow = Entertainment::where('id', $tvshowId)
            ->where('partner_id', $partner->id)->where('type', 'tvshow')->firstOrFail();
        $season = Season::where('id', $seasonId)->where('partner_id', $partner->id)->firstOrFail();

        $season->poster_url    = $season->poster_url    ? setBaseUrlWithFileName($season->poster_url,    'image', 'season') : null;
        $season->poster_tv_url = $season->poster_tv_url ? setBaseUrlWithFileName($season->poster_tv_url, 'image', 'season') : null;

        $page_type     = 'partners/' . $partner->id; // uploads go to partners/{id}/image/
        $partnerFolder = 'partners/' . $partner->id;

        return view('partner::frontend.season_edit',
            array_merge($this->commonData(), compact('partner', 'tvshow', 'season', 'page_type', 'partnerFolder'))
        );
    }

    public function seasonUpdate(Request $request, int $tvshowId, int $seasonId)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $tvshow = Entertainment::where('id', $tvshowId)
            ->where('partner_id', $partner->id)->where('type', 'tvshow')->firstOrFail();
        $season = Season::where('id', $seasonId)->where('partner_id', $partner->id)->firstOrFail();

        $request->validate([
            'name'          => 'required|string|max:255',
            'season_number' => 'required|integer|min:1',
        ]);

        $data = $request->except(['_token', '_method']);
        $data['approval_status'] = 'pending';
        $data['status']          = 0;

        if ($request->access === 'pay-per-view' && $request->price) {
            $data['partner_proposed_price'] = $request->price;
        } else {
            $data['access'] = 'free';
            $data['price']  = null;
            $data['partner_proposed_price'] = null;
        }

        foreach (['poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'season');
            }
        }

        $season->update($data);

        return redirect()->route('partner.tvshow.seasons', $tvshowId)
            ->with('success', __('partner::partner.season_updated'));
    }

    // Liste des saisons d'une série
    public function seasonList(int $tvshowId)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $tvshow  = Entertainment::where('id', $tvshowId)
            ->where('partner_id', $partner->id)->where('type', 'tvshow')->firstOrFail();
        $seasons = Season::where('entertainment_id', $tvshowId)
            ->where('partner_id', $partner->id)->latest()->paginate(20);

        return view('partner::frontend.season_list',
            compact('partner', 'tvshow', 'seasons')
        );
    }

    // ─────────────────────────────────────────────────────────────
    // ÉPISODES
    // ─────────────────────────────────────────────────────────────

    public function episodeCreate(int $tvshowId, int $seasonId)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $tvshow = Entertainment::where('id', $tvshowId)
            ->where('partner_id', $partner->id)->where('type', 'tvshow')->firstOrFail();
        $season = Season::where('id', $seasonId)->where('partner_id', $partner->id)->firstOrFail();

        $page_type     = 'partners/' . $partner->id; // uploads go to partners/{id}/image/
        $partnerFolder = 'partners/' . $partner->id;

        return view('partner::frontend.episode_create',
            array_merge($this->commonData(), compact('partner', 'tvshow', 'season', 'page_type', 'partnerFolder'))
        );
    }

    public function episodeStore(Request $request, int $tvshowId, int $seasonId)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $tvshow = Entertainment::where('id', $tvshowId)
            ->where('partner_id', $partner->id)->where('type', 'tvshow')->firstOrFail();
        $season = Season::where('id', $seasonId)->where('partner_id', $partner->id)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = $request->except(['_token']);
        $data['entertainment_id'] = $tvshow->id;
        $data['season_id']        = $season->id;
        $data['partner_id']       = $partner->id;
        $data['approval_status']  = 'pending';
        $data['status']           = 0;
        $data['slug']             = Str::slug($request->name) . '-' . time();

        if ($request->access === 'pay-per-view' && $request->price) {
            $data['partner_proposed_price'] = $request->price;
        } else {
            $data['access'] = $request->access ?? 'free';
            $data['price']  = null;
        }

        // Traitement vidéo — même logique que l'admin
        $videoType = $data['video_upload_type'] ?? null;
        if ($videoType === 'Embedded') {
            $data['video_url_input'] = $data['embed_code'] ?? '';
        } elseif ($videoType === 'Local') {
            $data['video_url_input'] = basename($data['video_file'] ?? '');
        }
        // Sinon (YouTube, HLS, etc.) : video_url_input vient directement du champ texte

        // Traitement bande annonce
        $trailerType = $data['trailer_url_type'] ?? null;
        if ($trailerType === 'Embedded') {
            $data['trailer_url'] = $data['trailer_embedded'] ?? '';
        } elseif ($trailerType === 'Local') {
            $data['trailer_url'] = basename($data['trailer_file'] ?? '');
        }

        // Images — extraire uniquement le nom de fichier
        foreach (['poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'episode');
            }
        }

        $episode = Episode::create($data);

        return redirect()->route('partner.tvshow.season.episodes', [$tvshowId, $seasonId])
            ->with('success', __('partner::partner.episode_created'));
    }

    public function episodeEdit(int $tvshowId, int $seasonId, int $episodeId)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $tvshow  = Entertainment::where('id', $tvshowId)->where('partner_id', $partner->id)->firstOrFail();
        $season  = Season::where('id', $seasonId)->where('partner_id', $partner->id)->firstOrFail();
        $episode = Episode::where('id', $episodeId)->where('partner_id', $partner->id)->firstOrFail();

        $episode->poster_url    = $episode->poster_url    ? setBaseUrlWithFileName($episode->poster_url,    'image', 'episode') : null;
        $episode->poster_tv_url = $episode->poster_tv_url ? setBaseUrlWithFileName($episode->poster_tv_url, 'image', 'episode') : null;

        $page_type     = 'partners/' . $partner->id; // uploads go to partners/{id}/image/
        $partnerFolder = 'partners/' . $partner->id;

        return view('partner::frontend.episode_edit',
            array_merge($this->commonData(), compact('partner', 'tvshow', 'season', 'episode', 'page_type', 'partnerFolder'))
        );
    }

    public function episodeUpdate(Request $request, int $tvshowId, int $seasonId, int $episodeId)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $episode = Episode::where('id', $episodeId)->where('partner_id', $partner->id)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = $request->except(['_token', '_method']);
        $data['approval_status'] = 'pending';
        $data['status']          = 0;

        if ($request->access === 'pay-per-view' && $request->price) {
            $data['partner_proposed_price'] = $request->price;
        } else {
            $data['access'] = $request->access ?? 'free';
            $data['price']  = null;
            $data['partner_proposed_price'] = null;
        }

        // Traitement vidéo — même logique que l'admin
        $videoType = $data['video_upload_type'] ?? null;
        if ($videoType === 'Embedded') {
            $data['video_url_input'] = $data['embed_code'] ?? '';
        } elseif ($videoType === 'Local') {
            $data['video_url_input'] = basename($data['video_file'] ?? '');
        }
        // Sinon (YouTube, HLS, etc.) : video_url_input vient directement du champ texte

        // Traitement bande annonce
        $trailerType = $data['trailer_url_type'] ?? null;
        if ($trailerType === 'Embedded') {
            $data['trailer_url'] = $data['trailer_embedded'] ?? '';
        } elseif ($trailerType === 'Local') {
            $data['trailer_url'] = basename($data['trailer_file'] ?? '');
        }

        foreach (['poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'episode');
            }
        }

        $episode->update($data);

        return redirect()->route('partner.tvshow.season.episodes', [$tvshowId, $seasonId])
            ->with('success', __('partner::partner.episode_updated'));
    }

    // Liste des épisodes d'une saison
    public function episodeList(int $tvshowId, int $seasonId)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $tvshow   = Entertainment::where('id', $tvshowId)->where('partner_id', $partner->id)->firstOrFail();
        $season   = Season::where('id', $seasonId)->where('partner_id', $partner->id)->firstOrFail();
        $episodes = Episode::where('season_id', $seasonId)->where('partner_id', $partner->id)->latest()->paginate(20);

        return view('partner::frontend.episode_list',
            compact('partner', 'tvshow', 'season', 'episodes')
        );
    }
}
