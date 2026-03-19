<?php

namespace Modules\Partner\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ModuleTrait;
use Modules\Entertainment\Models\Entertainment;
use Modules\Video\Models\Video;
use Modules\LiveTV\Models\LiveTvChannel;
use Illuminate\Support\Facades\Schema;

class PartnerValidationController extends Controller
{
    use ModuleTrait {
        initializeModuleTrait as private traitInitializeModuleTrait;
    }

    public function __construct()
    {
        $this->traitInitializeModuleTrait(
            'partner::partner.validation_title',
            'partner-validation',
            'ph ph-seal-check'
        );
    }

    public function index(Request $request)
    {
        $type   = $request->get('type', 'all');
        $status = $request->get('approval_status', 'pending');

        $movies    = collect();
        $tvshows   = collect();
        $videos    = collect();
        $livetvs   = collect();
        $pendingCount = 0;
        $migrationNeeded = false;

        // Guard: check that approval_status column exists (migration may not have run yet)
        if (!Schema::hasColumn('entertainments', 'approval_status')
            || !Schema::hasColumn('videos', 'approval_status')) {
            $migrationNeeded = true;
            $module_action = 'List';
            return view('partner::backend.validation.index', compact(
                'movies', 'tvshows', 'videos', 'livetvs',
                'type', 'status', 'pendingCount', 'module_action', 'migrationNeeded'
            ));
        }

        if (in_array($type, ['all', 'movie'])) {
            $movies = Entertainment::with('partner')
                ->where('type', 'movie')
                ->where('approval_status', $status)
                ->whereNotNull('partner_id')
                ->latest()
                ->get();
        }

        if (in_array($type, ['all', 'tvshow'])) {
            $tvshows = Entertainment::with('partner')
                ->where('type', 'tv_show')
                ->where('approval_status', $status)
                ->whereNotNull('partner_id')
                ->latest()
                ->get();
        }

        if (in_array($type, ['all', 'video'])) {
            $videos = Video::with('partner')
                ->where('approval_status', $status)
                ->whereNotNull('partner_id')
                ->latest()
                ->get();
        }

        if (in_array($type, ['all', 'livetv'])) {
            if (Schema::hasColumn('live_tv_channel', 'approval_status')) {
                $livetvs = LiveTvChannel::with('partner')
                    ->where('approval_status', $status)
                    ->whereNotNull('partner_id')
                    ->latest()
                    ->get();
            }
        }

        $pendingCount = Entertainment::where('approval_status', 'pending')->whereNotNull('partner_id')->count()
            + Video::where('approval_status', 'pending')->whereNotNull('partner_id')->count();

        if (Schema::hasColumn('live_tv_channel', 'approval_status')) {
            $pendingCount += LiveTvChannel::where('approval_status', 'pending')->whereNotNull('partner_id')->count();
        }

        $module_action = 'List';

        return view('partner::backend.validation.index', compact(
            'movies', 'tvshows', 'videos', 'livetvs',
            'type', 'status', 'pendingCount', 'module_action', 'migrationNeeded'
        ));
    }

    public function approve(Request $request, string $contentType, int $id)
    {
        $model = $this->resolveModel($contentType, $id);

        if (!$model) {
            return response()->json(['status' => false, 'message' => __('messages.not_found')], 404);
        }

        $updateData = ['approval_status' => 'approved', 'status' => 1];

        // Si admin fixe un prix final (PPV), on l'utilise. Sinon on valide le prix propose par le partenaire
        if ($request->filled('final_price') && is_numeric($request->input('final_price'))) {
            $updateData['price'] = (float) $request->input('final_price');
        } elseif (!empty($model->partner_proposed_price) && empty($model->price)) {
            $updateData['price'] = $model->partner_proposed_price;
        }

        $model->update($updateData);

        return response()->json(['status' => true, 'message' => __('partner::partner.content_approved')]);
    }

    public function reject(Request $request, string $contentType, int $id)
    {
        $model = $this->resolveModel($contentType, $id);

        if (!$model) {
            return response()->json(['status' => false, 'message' => __('messages.not_found')], 404);
        }

        $updateData = ['approval_status' => 'rejected', 'status' => 0];

        // Motif de rejet si fourni
        if ($request->filled('rejection_reason')) {
            $updateData['rejection_reason'] = $request->input('rejection_reason');
        }

        $model->update($updateData);

        $message = $request->filled('rejection_reason')
            ? __('partner::partner.content_rejected_reason')
            : __('partner::partner.content_rejected');

        return response()->json(['status' => true, 'message' => $message]);
    }

    private function resolveModel(string $contentType, int $id)
    {
        return match ($contentType) {
            'movie', 'tvshow' => Entertainment::find($id),
            'video'           => Video::find($id),
            'livetv'          => LiveTvChannel::find($id),
            default           => null,
        };
    }
}
