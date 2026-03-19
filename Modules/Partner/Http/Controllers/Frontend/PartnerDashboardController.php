<?php

namespace Modules\Partner\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Partner\Models\Partner;
use Modules\Video\Models\Video;
use Modules\Entertainment\Models\Entertainment;

class PartnerDashboardController extends Controller
{
    /**
     * Retourne le partenaire lié à l'utilisateur connecté.
     */
    protected function currentPartner(): ?Partner
    {
        return Partner::where('user_id', Auth::id())->first();
    }

    /**
     * Dashboard principal du partenaire.
     */
    public function index()
    {
        $partner = $this->currentPartner();

        if (!$partner) {
            return view('partner::frontend.no_partner');
        }

        // Stats
        // Quota usage
        $totalContent = \Modules\Video\Models\Video::where('partner_id', $partner->id)->count()
            + \Modules\Entertainment\Models\Entertainment::where('partner_id', $partner->id)->count()
            + \Modules\LiveTV\Models\LiveTvChannel::where('partner_id', $partner->id)->count();

        $stats = [
            'videos_active'   => $this->countVideos($partner->id, 1),
            'videos_inactive' => $this->countVideos($partner->id, 0),
            'videos_pending'  => $this->countVideosByApproval($partner->id, 'pending'),
            'videos_rejected' => $this->countVideosByApproval($partner->id, 'rejected'),
            'movies_total'    => $this->countMovies($partner->id),
            'quota_used'      => $totalContent,
            'quota_max'       => $partner->video_quota,
        ];

        return view('partner::frontend.dashboard', compact('partner', 'stats'));
    }

    /**
     * Liste des vidéos du partenaire.
     */
    public function videos(Request $request)
    {
        $partner = $this->currentPartner();

        if (!$partner) {
            return redirect()->route('partner.dashboard');
        }

        $status          = $request->get('status', '');
        $approvalStatus  = $request->get('approval_status', '');

        $query = Video::where('partner_id', $partner->id)->latest();

        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($approvalStatus !== '') {
            $query->where('approval_status', $approvalStatus);
        }

        $videos = $query->paginate(20);

        return view('partner::frontend.videos', compact('partner', 'videos', 'status', 'approvalStatus'));
    }

    public function movies(Request $request)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $query = \Modules\Entertainment\Models\Entertainment::where('partner_id', $partner->id)
            ->where('type', 'movie')->latest();
        $items = $query->paginate(20);

        return view('partner::frontend.content_list', compact('partner', 'items') + [
            'content_type' => 'movie',
            'title'        => __('movie.movies'),
        ]);
    }

    public function tvshows(Request $request)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $query = \Modules\Entertainment\Models\Entertainment::where('partner_id', $partner->id)
            ->where('type', 'tv_show')->latest();
        $items = $query->paginate(20);

        return view('partner::frontend.content_list', compact('partner', 'items') + [
            'content_type' => 'tvshow',
            'title'        => __('movie.tvshows'),
        ]);
    }

    public function livetv(Request $request)
    {
        $partner = $this->currentPartner();
        if (!$partner) return redirect()->route('partner.dashboard');

        $query = \Modules\LiveTV\Models\LiveTvChannel::where('partner_id', $partner->id)->latest();
        $items = $query->paginate(20);

        return view('partner::frontend.content_list', compact('partner', 'items') + [
            'content_type' => 'livetv',
            'title'        => __('frontend.livetv'),
        ]);
    }

    // ── helpers ──────────────────────────────────────────────────

    private function countVideos(int $partnerId, int $status): int
    {
        try {
            return Video::where('partner_id', $partnerId)->where('status', $status)->count();
        } catch (\Exception $e) { return 0; }
    }

    private function countVideosByApproval(int $partnerId, string $approvalStatus): int
    {
        try {
            return Video::where('partner_id', $partnerId)->where('approval_status', $approvalStatus)->count();
        } catch (\Exception $e) { return 0; }
    }

    private function countMovies(int $partnerId): int
    {
        try {
            return Entertainment::where('partner_id', $partnerId)->count();
        } catch (\Exception $e) { return 0; }
    }
    public function notifications(Request $request)
    {
        $partner = $this->currentPartner();
        if (!$partner || !$partner->user_id) return redirect()->route('partner.dashboard');

        $user          = \Auth::user();
        $notifications = $user->notifications()->latest()->paginate(20);
        $unreadCount   = $user->unreadNotifications()->count();

        return view('partner::frontend.notifications', compact('partner', 'notifications', 'unreadCount'));
    }

    public function markNotificationsRead(Request $request)
    {
        \Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('success', __('partner::partner.mark_all_read'));
    }

}
