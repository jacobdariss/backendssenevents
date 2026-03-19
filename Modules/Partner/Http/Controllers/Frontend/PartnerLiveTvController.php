<?php

namespace Modules\Partner\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Constant\Models\Constant;
use Modules\LiveTV\Models\LiveTvCategory;
use Modules\LiveTV\Models\LiveTvChannel;
use Modules\LiveTV\Models\TvChannelStreamContentMapping;
use Modules\Partner\Models\Partner;
use Modules\Subscriptions\Models\Plan;

class PartnerLiveTvController extends Controller
{
    protected function currentPartner(): ?Partner
    {
        return Partner::where('user_id', Auth::id())->first();
    }

    public function create()
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $tvcategory    = LiveTvCategory::where('status', 1)->get();
        $embedded      = Constant::where('type', 'STREAM_TYPE')->where('name', 'Embedded')->get();
        $url           = Constant::where('type', 'STREAM_TYPE')->where('name', '!=', 'Embedded')->get();
        $plan          = Plan::where('status', 1)->get();
        $mediaUrls     = getMediaUrls();
        $assets        = ['textarea'];
        $page_type     = 'livetvchannel';
        $partnerFolder = 'partners/' . $partner->id;

        return view('partner::frontend.livetv_create',
            compact('partner', 'tvcategory', 'embedded', 'url', 'plan', 'mediaUrls', 'assets', 'page_type', 'partnerFolder')
        );
    }

    public function store(Request $request)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required',
            'access'      => 'required|in:free,pay-per-view',
        ]);

        $data                    = $request->except(['_token', 'type', 'stream_type', 'server_url', 'server_url1', 'embedded']);
        $data['partner_id']      = $partner->id;
        $data['approval_status'] = 'pending';
        $data['status']          = 0;

        if ($request->access === 'pay-per-view' && $request->price) {
            $data['partner_proposed_price'] = $request->price;
        } else {
            $data['access'] = 'free';
            $data['price']  = null;
        }

        foreach (['poster_url', 'poster_tv_url'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = extractFileNameFromUrl($data[$field], 'livetv');
            }
        }
        if (!empty($data['thumbnail_url'])) {
            $data['thumb_url'] = extractFileNameFromUrl($data['thumbnail_url'], 'livetv');
            unset($data['thumbnail_url']);
        }

        $channel = LiveTvChannel::create($data);

        // Sauvegarder le stream
        if ($request->filled('server_url') || $request->filled('embedded')) {
            TvChannelStreamContentMapping::create([
                'tv_channel_id' => $channel->id,
                'stream_type'   => $request->stream_type,
                'embedded'      => $request->embedded,
                'server_url'    => $request->server_url,
                'server_url1'   => $request->server_url1,
            ]);
        }

        return redirect()->route('partner.livetv')
            ->with('success', __('partner::partner.video_submitted'));
    }

    public function edit(int $id)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $item = LiveTvChannel::where('id', $id)->where('partner_id', $partner->id)->firstOrFail();
        $streamMapping = $item->TvChannelStreamContentMappings;

        $item->poster_url    = $item->poster_url ? setBaseUrlWithFileName($item->poster_url,    'image', 'livetvchannel') : null;
        $item->poster_tv_url = $item->poster_tv_url ? setBaseUrlWithFileName($item->poster_tv_url, 'image', 'livetvchannel') : null;
        $item->thumb_url     = $item->thumb_url ? setBaseUrlWithFileName($item->thumb_url,      'image', 'livetvchannel') : null;

        $tvcategory    = LiveTvCategory::where('status', 1)->get();
        $embedded      = Constant::where('type', 'STREAM_TYPE')->where('name', 'Embedded')->get();
        $url           = Constant::where('type', 'STREAM_TYPE')->where('name', '!=', 'Embedded')->get();
        $plan          = Plan::where('status', 1)->get();
        $mediaUrls     = getMediaUrls();
        $assets        = ['textarea'];
        $page_type     = 'livetvchannel';
        $partnerFolder = 'partners/' . $partner->id;

        return view('partner::frontend.livetv_edit',
            compact('partner', 'item', 'streamMapping', 'tvcategory', 'embedded', 'url', 'plan', 'mediaUrls', 'assets', 'page_type', 'partnerFolder')
        );
    }

    public function update(Request $request, int $id)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $item = LiveTvChannel::where('id', $id)->where('partner_id', $partner->id)->firstOrFail();

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required',
            'access'      => 'required|in:free,pay-per-view',
        ]);

        $data                    = $request->except(['_token', '_method', 'type', 'stream_type', 'server_url', 'server_url1', 'embedded']);
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
                $data[$field] = extractFileNameFromUrl($data[$field], 'livetv');
            }
        }
        if (!empty($data['thumbnail_url'])) {
            $data['thumb_url'] = extractFileNameFromUrl($data['thumbnail_url'], 'livetv');
            unset($data['thumbnail_url']);
        }

        $item->update($data);

        // Mettre à jour le stream
        if ($request->filled('server_url') || $request->filled('embedded')) {
            TvChannelStreamContentMapping::updateOrCreate(
                ['tv_channel_id' => $item->id],
                [
                    'stream_type' => $request->stream_type,
                    'embedded'    => $request->embedded,
                    'server_url'  => $request->server_url,
                    'server_url1' => $request->server_url1,
                ]
            );
        }

        return redirect()->route('partner.livetv')
            ->with('success', __('partner::partner.video_updated'));
    }
}
