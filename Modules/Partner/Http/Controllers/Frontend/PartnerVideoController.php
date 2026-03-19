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
        $page_type       = 'video';
        $partnerFolder   = 'partners/' . $partner->id;

        return view('partner::frontend.video_create', compact(
            'partner', 'upload_url_type', 'video_quality', 'plan', 'mediaUrls', 'page_type', 'partnerFolder'
        ));
    }

    public function store(Request $request)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $request->validate([
            'name'              => 'required|string|max:255|unique:videos,name',
            'description'       => 'required|string',
            'duration'          => 'required',
            'release_date'      => 'required',
            'access'            => 'required',
            'video_upload_type' => 'required',
        ]);

        $data = $request->except(['_token']);
        $data['partner_id']      = $partner->id;
        $data['approval_status'] = 'pending';
        $data['slug']            = Str::slug($request->name) . '-' . time();
        $data['status']          = 0; // inactif jusqu'à validation

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
        $page_type       = 'video';
        $partnerFolder   = 'partners/' . $partner->id;

        // Reconstituer les URLs complètes pour l'affichage
        $video->thumbnail_url = $video->thumbnail_url ? setBaseUrlWithFileName($video->thumbnail_url, 'image', 'video') : null;
        $video->poster_url    = $video->poster_url    ? setBaseUrlWithFileName($video->poster_url,    'image', 'video') : null;
        $video->poster_tv_url = $video->poster_tv_url ? setBaseUrlWithFileName($video->poster_tv_url, 'image', 'video') : null;

        return view('partner::frontend.video_edit', compact(
            'partner', 'video', 'upload_url_type', 'video_quality', 'plan', 'mediaUrls', 'page_type', 'partnerFolder'
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
            'access'            => 'required',
            'video_upload_type' => 'required',
        ]);

        $data = $request->except(['_token', '_method']);
        $data['approval_status'] = 'pending'; // Re-soumettre à validation
        $data['status']          = 0;

        foreach (['thumbnail_url', 'poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'video');
            }
        }

        $video->update($data);

        return redirect()->route('partner.videos')
            ->with('success', __('partner::partner.video_updated'));
    }
}
