<?php

namespace Modules\HomepageBuilder\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HomepageBuilder\Models\HomepageSection;
use Modules\Entertainment\Models\Entertainment;
use Modules\Video\Models\Video;
use Modules\LiveTV\Models\LiveTvChannel;
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
        return view('homepage-builder::backend.homepage-builder.edit', compact(
            'section', 'types', 'contentTypes', 'sortOptions', 'platforms', 'contentOptions'
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
            'content_limit' => 'required|integer|min:1|max:100',
            'sort_by'       => 'required|string',
            'content_ids'   => 'nullable|array',
        ]);
        $data['content_ids'] = !empty($data['content_ids']) ? $data['content_ids'] : null;
        $data['is_active']   = $request->boolean('is_active', $section->is_active);
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
        return view('homepage-builder::backend.homepage-builder.edit', compact(
            'section', 'types', 'contentTypes', 'sortOptions', 'platforms', 'contentOptions'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'type'          => 'required|string',
            'content_type'  => 'nullable|string',
            'platform'      => 'required|in:web,mobile,both',
            'content_limit' => 'required|integer|min:1|max:100',
            'sort_by'       => 'required|string',
            'content_ids'   => 'nullable|array',
        ]);
        $data['slug']        = Str::slug($data['name']) . '-' . uniqid();
        $data['position']    = HomepageSection::max('position') + 1;
        $data['is_active']   = $request->boolean('is_active', true);
        $data['content_ids'] = !empty($data['content_ids']) ? $data['content_ids'] : null;
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
}
