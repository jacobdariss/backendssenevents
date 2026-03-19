<?php

namespace Modules\Partner\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Constant\Models\Constant;
use Modules\Entertainment\Models\Entertainment;
use Modules\Partner\Models\Partner;
use Modules\Subscriptions\Models\Plan;

class PartnerContentController extends Controller
{
    protected function currentPartner(): ?Partner
    {
        return Partner::where('user_id', Auth::id())->first();
    }

    protected function commonData(string $type): array
    {
        $constants       = Constant::whereIn('type', ['upload_type', 'video_quality'])->where('status', 1)->get()->groupBy('type');
        $upload_url_type = $constants->get('upload_type', collect());
        $plan            = Plan::where('status', 1)->get();
        $mediaUrls       = getMediaUrls();
        $assets          = ['textarea'];

        return compact('upload_url_type', 'plan', 'mediaUrls', 'assets');
    }

    // ── FILMS ─────────────────────────────────────────────────────

    public function moviesCreate()
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $partnerFolder = 'partners/' . $partner->id;
        $page_type     = 'movie';
        $content_type  = 'movie';

        return view('partner::frontend.movie_create',
            array_merge($this->commonData('movie'), compact('partner', 'partnerFolder', 'page_type', 'content_type'))
        );
    }

    public function moviesStore(Request $request)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'required|string',
            'release_date'      => 'required',
            'movie_access'      => 'required|in:free,pay-per-view',
            'video_upload_type' => 'required',
            'price'             => 'required_if:movie_access,pay-per-view|nullable|numeric|min:0',
            'purchase_type'     => 'required_if:movie_access,pay-per-view',
        ]);

        $data                    = $request->except(['_token']);
        $data['type']            = 'movie';
        $data['partner_id']      = $partner->id;
        $data['approval_status'] = 'pending';
        $data['status']          = 0;
        $data['slug']            = Str::slug($request->name) . '-' . time();

        if ($request->movie_access === 'pay-per-view' && $request->price) {
            $data['partner_proposed_price'] = $request->price;
        } else {
            $data['movie_access'] = 'free';
            $data['price'] = null;
        }

        foreach (['thumbnail_url', 'poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'movie');
            }
        }

        Entertainment::create($data);

        return redirect()->route('partner.movies')
            ->with('success', __('partner::partner.video_submitted'));
    }

    public function moviesEdit(int $id)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $item = Entertainment::where('id', $id)->where('partner_id', $partner->id)
            ->where('type', 'movie')->firstOrFail();

        $item->thumbnail_url = $item->thumbnail_url ? setBaseUrlWithFileName($item->thumbnail_url, 'image', 'movie') : null;
        $item->poster_url    = $item->poster_url    ? setBaseUrlWithFileName($item->poster_url,    'image', 'movie') : null;
        $item->poster_tv_url = $item->poster_tv_url ? setBaseUrlWithFileName($item->poster_tv_url, 'image', 'movie') : null;

        $partnerFolder = 'partners/' . $partner->id;
        $page_type     = 'movie';
        $content_type  = 'movie';

        return view('partner::frontend.movie_edit',
            array_merge($this->commonData('movie'), compact('partner', 'item', 'partnerFolder', 'page_type', 'content_type'))
        );
    }

    public function moviesUpdate(Request $request, int $id)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $item = Entertainment::where('id', $id)->where('partner_id', $partner->id)
            ->where('type', 'movie')->firstOrFail();

        $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'required|string',
            'release_date'      => 'required',
            'movie_access'      => 'required|in:free,pay-per-view',
            'video_upload_type' => 'required',
            'price'             => 'required_if:movie_access,pay-per-view|nullable|numeric|min:0',
        ]);

        $data                    = $request->except(['_token', '_method']);
        $data['approval_status'] = 'pending';
        $data['status']          = 0;

        if ($request->movie_access === 'pay-per-view' && $request->price) {
            $data['partner_proposed_price'] = $request->price;
        } else {
            $data['movie_access'] = 'free';
            $data['price'] = null;
            $data['partner_proposed_price'] = null;
        }

        foreach (['thumbnail_url', 'poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'movie');
            }
        }

        $item->update($data);

        return redirect()->route('partner.movies')
            ->with('success', __('partner::partner.video_updated'));
    }

    // ── SÉRIES TV ─────────────────────────────────────────────────

    public function tvshowsCreate()
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $partnerFolder = 'partners/' . $partner->id;
        $page_type     = 'movie'; // même stockage que films
        $content_type  = 'tvshow';

        return view('partner::frontend.movie_create',
            array_merge($this->commonData('tvshow'), compact('partner', 'partnerFolder', 'page_type', 'content_type'))
        );
    }

    public function tvshowsStore(Request $request)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'required|string',
            'release_date' => 'required',
            'movie_access' => 'required|in:free,pay-per-view',
        ]);

        $data                    = $request->except(['_token']);
        $data['type']            = 'tv_show';
        $data['partner_id']      = $partner->id;
        $data['approval_status'] = 'pending';
        $data['status']          = 0;
        $data['slug']            = Str::slug($request->name) . '-' . time();

        if ($request->movie_access === 'pay-per-view' && $request->price) {
            $data['partner_proposed_price'] = $request->price;
        } else {
            $data['movie_access'] = 'free';
            $data['price'] = null;
        }

        foreach (['thumbnail_url', 'poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'movie');
            }
        }

        Entertainment::create($data);

        return redirect()->route('partner.tvshows')
            ->with('success', __('partner::partner.video_submitted'));
    }

    public function tvshowsEdit(int $id)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $item = Entertainment::where('id', $id)->where('partner_id', $partner->id)
            ->where('type', 'tv_show')->firstOrFail();

        $item->thumbnail_url = $item->thumbnail_url ? setBaseUrlWithFileName($item->thumbnail_url, 'image', 'movie') : null;
        $item->poster_url    = $item->poster_url    ? setBaseUrlWithFileName($item->poster_url,    'image', 'movie') : null;
        $item->poster_tv_url = $item->poster_tv_url ? setBaseUrlWithFileName($item->poster_tv_url, 'image', 'movie') : null;

        $partnerFolder = 'partners/' . $partner->id;
        $page_type     = 'movie'; // même stockage que films
        $content_type  = 'tvshow';

        return view('partner::frontend.movie_edit',
            array_merge($this->commonData('tvshow'), compact('partner', 'item', 'partnerFolder', 'page_type', 'content_type'))
        );
    }

    public function tvshowsUpdate(Request $request, int $id)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $item = Entertainment::where('id', $id)->where('partner_id', $partner->id)
            ->where('type', 'tv_show')->firstOrFail();

        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'required|string',
            'release_date' => 'required',
            'movie_access' => 'required|in:free,pay-per-view',
        ]);

        $data                    = $request->except(['_token', '_method']);
        $data['approval_status'] = 'pending';
        $data['status']          = 0;

        if ($request->movie_access === 'pay-per-view' && $request->price) {
            $data['partner_proposed_price'] = $request->price;
        } else {
            $data['movie_access'] = 'free';
            $data['price'] = null;
            $data['partner_proposed_price'] = null;
        }

        foreach (['thumbnail_url', 'poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'movie');
            }
        }

        $item->update($data);

        return redirect()->route('partner.tvshows')
            ->with('success', __('partner::partner.video_updated'));
    }
}
