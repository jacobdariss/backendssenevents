<?php

namespace Modules\Partner\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Constant\Models\Constant;
use Modules\Partner\Models\Partner;
use Modules\Subscriptions\Models\Plan;
use Modules\Video\Models\Video;

class PartnerVideoController extends Controller
{
    protected function currentPartner(): ?Partner
    {
        return Partner::where('user_id', Auth::id())->first();
    }

    // ── CREATE ────────────────────────────────────────────────────

    public function create()
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $constants       = Constant::whereIn('type', ['upload_type', 'video_quality'])->where('status', 1)->get()->groupBy('type');
        $upload_url_type = $constants->get('upload_type', collect());
        $video_quality   = $constants->get('video_quality', collect());
        $plan            = Plan::where('status', 1)->get();
        $mediaUrls       = getMediaUrls();
        $page_type       = 'partners/' . $partner->id; // uploads go to partners/{id}/image/
        $partnerFolder   = 'partners/' . $partner->id;

        $assets = ['textarea'];
        return view('partner::frontend.video_create', compact(
            'partner', 'upload_url_type', 'video_quality', 'plan', 'mediaUrls', 'page_type', 'partnerFolder', 'assets'
        ));
    }

    public function store(Request $request)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        if ($redirect = $this->checkQuota($partner)) return $redirect;

        $request->validate([
            'name'              => 'required|string|max:255',
            'video_upload_type' => 'required',
        ]);
        $data = $request->except(['_token']);
        $data['partner_id']      = $partner->id;
        $data['approval_status'] = 'pending';
        $data['slug']            = Str::slug($request->name) . '-' . time();
        $data['status']          = 0; // inactif jusqu'à validation

        // Si PPV : conserver le prix proposé par le partenaire
        if ($request->access === 'pay-per-view' && $request->price) {
            $data['partner_proposed_price'] = $request->price;
        } else {
            // Forcer free si pas PPV
            $data['access'] = $request->access === 'free' ? 'free' : 'free';
            $data['price']  = null;
            $data['partner_proposed_price'] = null;
        }

        // Si Local, utiliser le fichier sélectionné comme URL vidéo
        if ($request->video_upload_type === 'Local' && $request->filled('video_file')) {
            $data['video_url_input'] = extractFileNameFromUrl($request->video_file, 'video');
        }

        // Extraire le nom de fichier des URLs
        foreach (['thumbnail_url', 'poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'video');
            }
        }

        Video::create($data);

        return redirect()->route('partner.videos')
            ->with('success', __('partner::partner.video_submitted'));
    }

    // ── EDIT ──────────────────────────────────────────────────────

    public function edit(int $id)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $video = Video::where('id', $id)->where('partner_id', $partner->id)->firstOrFail();

        $constants       = Constant::whereIn('type', ['upload_type', 'video_quality'])->where('status', 1)->get()->groupBy('type');
        $upload_url_type = $constants->get('upload_type', collect());
        $video_quality   = $constants->get('video_quality', collect());
        $plan            = Plan::where('status', 1)->get();
        $mediaUrls       = getMediaUrls();
        $page_type       = 'partners/' . $partner->id; // uploads go to partners/{id}/image/
        $partnerFolder   = 'partners/' . $partner->id;

        // Reconstituer les URLs complètes pour l'affichage
        $video->thumbnail_url = $video->thumbnail_url ? setBaseUrlWithFileName($video->thumbnail_url, 'image', 'video') : null;
        $video->poster_url    = $video->poster_url    ? setBaseUrlWithFileName($video->poster_url,    'image', 'video') : null;
        $video->poster_tv_url = $video->poster_tv_url ? setBaseUrlWithFileName($video->poster_tv_url, 'image', 'video') : null;

        $assets = ['textarea'];
        return view('partner::frontend.video_edit', compact(
            'partner', 'video', 'upload_url_type', 'video_quality', 'plan', 'mediaUrls', 'page_type', 'partnerFolder', 'assets'
        ));
    }

    public function update(Request $request, int $id)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $video = Video::where('id', $id)->where('partner_id', $partner->id)->firstOrFail();

        $request->validate([
            'name'              => 'required|string|max:255|unique:videos,name,' . $id,
            'description'       => 'required|string',
            'duration'          => 'required',
            'release_date'      => 'required',
            'access'            => 'required|in:free,pay-per-view',
            'video_upload_type' => 'required',
            'price'             => 'required_if:access,pay-per-view|numeric|min:0',
            'purchase_type'     => 'required_if:access,pay-per-view',
            'access_duration'   => 'required_if:purchase_type,rental|nullable|integer|min:1',
            'available_for'     => 'required_if:access,pay-per-view|nullable|integer|min:1',
        ]);

        $data = $request->except(['_token', '_method']);
        $data['approval_status'] = 'pending';
        $data['status']          = 0;

        if ($request->access === 'pay-per-view' && $request->price) {
            $data['partner_proposed_price'] = $request->price;
        } else {
            $data['access']                 = 'free';
            $data['price']                  = null;
            $data['partner_proposed_price'] = null;
            $data['purchase_type']          = null;
            $data['access_duration']        = null;
            $data['available_for']          = null;
        }

        foreach (['thumbnail_url', 'poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'video');
            }
        }

        $video->update($data);

        return redirect()->route('partner.videos')
            ->with('success', __('partner::partner.video_updated'));
    }

    protected function checkQuota(Partner $partner): ?\Illuminate\Http\RedirectResponse
    {
        if ($partner->video_quota === null) return null; // illimité

        // Compter tous les contenus du partenaire
        $count = 0;
        $count += \Modules\Video\Models\Video::where('partner_id', $partner->id)->count();
        $count += \Modules\Entertainment\Models\Entertainment::where('partner_id', $partner->id)->count();
        $count += \Modules\LiveTV\Models\LiveTvChannel::where('partner_id', $partner->id)->count();

        if ($count >= $partner->video_quota) {
            return redirect()->back()->with('error',
                __('partner::partner.quota_exceeded', ['max' => $partner->video_quota, 'current' => $count])
            );
        }
        return null;
    }

}
