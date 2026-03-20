<?php

namespace Modules\HomepageBuilder\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HomepageBuilder\Models\HomepageSection;
use Modules\Entertainment\Models\Entertainment;
use Modules\Video\Models\Video;
use Modules\LiveTV\Models\LiveTvChannel;
use Modules\Season\Models\Season;
use Modules\Episode\Models\Episode;
use Illuminate\Support\Str;

class HomepageBuilderController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::orderBy('position')->get();
        $module_action = 'List';
        return view('homepage-builder::backend.homepage-builder.index', compact('sections', 'module_action'));
    }

    public function reorder(Request $request)
    {
        $request->validate(['positions' => 'required|array']);
        foreach ($request->positions as $item) {
            HomepageSection::where('id', $item['id'])->update(['position' => $item['position']]);
        }
        HomepageSection::clearCache();
        return response()->json(['status' => true, 'message' => 'Ordre mis à jour']);
    }

    public function toggleActive(Request $request, int $id)
    {
        $section = HomepageSection::findOrFail($id);
        $section->update(['is_active' => !$section->is_active]);
        HomepageSection::clearCache();
        return response()->json(['status' => true, 'is_active' => $section->is_active]);
    }

    public function edit(int $id)
    {
        $section        = HomepageSection::findOrFail($id);
        $types          = HomepageSection::types();
        $contentTypes   = HomepageSection::contentTypes();
        $sortOptions    = HomepageSection::sortOptions();
        $platforms      = ['both' => 'Web + Mobile', 'web' => 'Web seulement', 'mobile' => 'Mobile seulement'];
        $contentOptions = $this->getContentOptions($section);

        // Pour la section tvshow : pré-charger les données de la sélection saisons/épisodes
        $episodePickerData = $this->buildEpisodePickerData($section);

        $ajaxUrls = [
            'contentOptions' => route('backend.homepage-builder.content-options'),
            'tvshowSeasons'  => route('backend.homepage-builder.tvshow-seasons'),
            'seasonEpisodes' => route('backend.homepage-builder.season-episodes'),
        ];

        return view('homepage-builder::backend.homepage-builder.edit', compact(
            'section', 'types', 'contentTypes', 'sortOptions', 'platforms', 'contentOptions', 'episodePickerData', 'ajaxUrls'
        ));
    }

    public function update(Request $request, int $id)
    {
        $section = HomepageSection::findOrFail($id);
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'type'          => 'required|string',
            'content_type'  => 'nullable|string',
            'platform'      => 'required|in:web,mobile,both',
            'content_limit'    => 'required|integer|min:1|max:100',
            'sort_by'          => 'required|string',
            'card_orientation' => 'nullable|in:vertical,horizontal',
            'content_ids'      => 'nullable|array',
            'episode_ids'      => 'nullable|array',
        ]);
        $data['content_ids']      = !empty($data['content_ids']) ? $data['content_ids'] : null;
        $data['episode_ids']      = !empty($data['episode_ids']) ? $data['episode_ids'] : null;
        $data['card_orientation'] = $request->input('card_orientation', 'vertical');
        $data['is_active']        = $request->boolean('is_active', $section->is_active);
        $section->update($data);
        HomepageSection::clearCache();
        return redirect()->route('backend.homepage-builder.index')
            ->with('success', 'Section mise à jour');
    }

    public function create()
    {
        $types          = HomepageSection::types();
        $contentTypes   = HomepageSection::contentTypes();
        $sortOptions    = HomepageSection::sortOptions();
        $platforms      = ['both' => 'Web + Mobile', 'web' => 'Web seulement', 'mobile' => 'Mobile seulement'];
        $section        = null;
        $contentOptions = [];
        $episodePickerData = null;
        $ajaxUrls = [
            'contentOptions' => route('backend.homepage-builder.content-options'),
            'tvshowSeasons'  => route('backend.homepage-builder.tvshow-seasons'),
            'seasonEpisodes' => route('backend.homepage-builder.season-episodes'),
        ];
        return view('homepage-builder::backend.homepage-builder.edit', compact(
            'section', 'types', 'contentTypes', 'sortOptions', 'platforms', 'contentOptions', 'episodePickerData', 'ajaxUrls'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'type'          => 'required|string',
            'content_type'  => 'nullable|string',
            'platform'      => 'required|in:web,mobile,both',
            'content_limit'    => 'required|integer|min:1|max:100',
            'sort_by'          => 'required|string',
            'card_orientation' => 'nullable|in:vertical,horizontal',
            'content_ids'      => 'nullable|array',
            'episode_ids'      => 'nullable|array',
        ]);
        $data['slug']             = Str::slug($data['name']) . '-' . uniqid();
        $data['position']         = HomepageSection::max('position') + 1;
        $data['is_active']        = $request->boolean('is_active', true);
        $data['card_orientation'] = $request->input('card_orientation', 'vertical');
        $data['content_ids']      = !empty($data['content_ids']) ? $data['content_ids'] : null;
        $data['episode_ids']      = !empty($data['episode_ids']) ? $data['episode_ids'] : null;
        HomepageSection::create($data);
        HomepageSection::clearCache();
        return redirect()->route('backend.homepage-builder.index')->with('success', 'Section créée');
    }

    public function destroy(int $id)
    {
        $section = HomepageSection::findOrFail($id);
        $section->delete();
        HomepageSection::clearCache();
        return response()->json(['status' => true, 'message' => 'Section supprimée']);
    }

    public function getContentOptionsAjax(Request $request)
    {
        $section = new HomepageSection([
            'type'         => $request->type,
            'content_type' => $request->content_type,
        ]);
        return response()->json($this->getContentOptions($section));
    }

    /**
     * AJAX — Retourne les saisons d'une série TV
     */
    public function getTvshowSeasons(Request $request)
    {
        $tvshowId = $request->integer('tvshow_id');
        if (!$tvshowId) {
            return response()->json([]);
        }

        $seasons = Season::where('entertainment_id', $tvshowId)
            ->orderBy('season_number')
            ->get(['id', 'name', 'season_number'])
            ->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->name ?: 'Saison ' . $s->season_number,
            ]);

        return response()->json($seasons);
    }

    /**
     * AJAX — Retourne les épisodes de saisons sélectionnées
     */
    public function getSeasonEpisodes(Request $request)
    {
        $seasonIds = $request->input('season_ids', []);
        if (empty($seasonIds)) {
            return response()->json([]);
        }

        $episodes = Episode::whereIn('season_id', $seasonIds)
            ->where('status', 1)
            ->orderBy('season_id')
            ->orderBy('episode_number')
            ->get(['id', 'name', 'episode_number', 'season_id'])
            ->map(function ($ep) {
                $season = Season::find($ep->season_id);
                $seasonLabel = $season ? ($season->name ?: 'Saison ' . $season->season_number) : '';
                return [
                    'id'   => $ep->id,
                    'name' => ($ep->episode_number ? 'Ep.' . $ep->episode_number . ' — ' : '') . $ep->name
                             . ($seasonLabel ? ' (' . $seasonLabel . ')' : ''),
                ];
            });

        return response()->json($episodes);
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function getContentOptions(HomepageSection $section): array
    {
        return match($section->type) {
            'entertainment' => Entertainment::where('status', 1)->whereNull('deleted_at')
                ->when($section->content_type, fn($q) => $q->where('type', $section->content_type))
                ->where(fn($q) => $q->whereNull('release_date')->orWhereDate('release_date', '<=', now()))
                ->orderBy('name')->get(['id', 'name', 'type'])->toArray(),
            'video' => Video::where('status', 1)->whereNull('deleted_at')
                ->where(fn($q) => $q->whereNull('release_date')->orWhereDate('release_date', '<=', now()))
                ->orderBy('name')->get(['id', 'name'])->toArray(),
            'livetv' => LiveTvChannel::where('status', 1)->orderBy('name')->get(['id', 'name'])->toArray(),
            default  => [],
        };
    }

    /**
     * Pré-charge les données du sélecteur saisons/épisodes pour l'édition
     */
    private function buildEpisodePickerData(HomepageSection $section): ?array
    {
        if ($section->type !== 'entertainment' || $section->content_type !== 'tvshow') {
            return null;
        }

        $episodeIds = $section->episode_ids ?? [];
        if (empty($episodeIds)) {
            return null;
        }

        // Retrouver les épisodes sauvegardés
        $episodes = Episode::whereIn('id', $episodeIds)
            ->with(['season:id,name,season_number,entertainment_id'])
            ->get(['id', 'name', 'episode_number', 'season_id']);

        if ($episodes->isEmpty()) {
            return null;
        }

        // Retrouver les saisons et la série parente
        $seasonIds = $episodes->pluck('season_id')->unique()->toArray();
        $seasons   = Season::whereIn('id', $seasonIds)->get(['id', 'name', 'season_number', 'entertainment_id']);
        $tvshowIds = $seasons->pluck('entertainment_id')->unique()->toArray();
        $tvshows   = Entertainment::whereIn('id', $tvshowIds)->get(['id', 'name']);

        return [
            'tvshows'    => $tvshows->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->toArray(),
            'seasons'    => $seasons->map(fn($s) => ['id' => $s->id, 'name' => $s->name ?: 'Saison ' . $s->season_number])->toArray(),
            'episodes'   => $episodes->map(function ($ep) {
                $sName = $ep->season ? ($ep->season->name ?: 'Saison ' . $ep->season->season_number) : '';
                return [
                    'id'   => $ep->id,
                    'name' => ($ep->episode_number ? 'Ep.' . $ep->episode_number . ' — ' : '') . $ep->name
                             . ($sName ? ' (' . $sName . ')' : ''),
                ];
            })->toArray(),
            'episode_ids' => $episodeIds,
        ];
    }
}
