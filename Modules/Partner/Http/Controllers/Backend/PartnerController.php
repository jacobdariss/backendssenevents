<?php

namespace Modules\Partner\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Partner\Http\Requests\PartnerRequest;
use Modules\Partner\Services\PartnerService;
use Modules\Partner\Models\Partner;
use App\Models\User;
use Yajra\DataTables\DataTables;
use App\Traits\ModuleTrait;

class PartnerController extends Controller
{
    protected $partnerService;

    use ModuleTrait {
        initializeModuleTrait as private traitInitializeModuleTrait;
    }

    public function __construct(PartnerService $partnerService)
    {
        $this->partnerService = $partnerService;
        $this->traitInitializeModuleTrait(
            'partner.title',
            'partners',
            'ph ph-handshake'
        );
    }

    public function index(Request $request)
    {
        $module_action = 'List';

        $filter = [
            'status' => $request->status,
        ];

        return view('partner::backend.partner.index', compact('module_action', 'filter'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        $filter = $request->filter ?? [];
        return $this->partnerService->getDataTable($datatable, $filter);
    }

    public function update_status(Request $request, int $id)
    {
        $this->partnerService->updatePartner($id, ['status' => $request->status]);
        return response()->json(['status' => true, 'message' => __('messages.status_updated')]);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;
        $moduleName = __('partner.title');
        return $this->performBulkAction(Partner::class, $ids, $actionType, $moduleName);
    }

    public function create(Request $request)
    {
        $module_title = __('partner.add_title');

        return view('partner::backend.partner.create', compact('module_title'));
    }

    public function store(PartnerRequest $request)
    {
        $data = $request->all();

        if (!empty($data['logo_url'])) {
            $data['logo_url'] = extractFileNameFromUrl($data['logo_url'], 'partners');
        }

        $data['allowed_content_types'] = $request->input('content_types', []);

        // Create linked user account if requested
        if ($request->boolean('create_account')) {
            $user = User::create([
                'first_name' => $request->account_first_name,
                'last_name'  => $request->account_last_name,
                'email'      => $request->account_email,
                'password'   => Hash::make($request->account_password),
                'user_type'  => 'partner',
            ]);
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'partner', 'guard_name' => 'web'], ['title' => 'Partner', 'is_fixed' => true]);
            $user->assignRole('partner');
            $data['user_id'] = $user->id;
        }

        $this->partnerService->createPartner($data);

        $message = __('messages.create_form', ['form' => __('partner.title')]);
        return redirect()->route('backend.partners.index')->with('success', $message);
    }

    public function show(int $id)
    {
        $partner     = $this->partnerService->getPartnerById($id);
        $stats       = $this->partnerService->getVideoStats($id);
        $module_title = __('partner::partner.lbl_partner');
        $module_action = 'Show';

        return view('partner::backend.partner.show', compact('partner', 'stats', 'module_title', 'module_action'));
    }

    public function edit(int $id)
    {
        $partner = $this->partnerService->getPartnerById($id);
        $module_title = __('partner.edit_title');

        return view('partner::backend.partner.edit', compact('partner', 'module_title'));
    }

    public function update(PartnerRequest $request, int $id)
    {
        $data = $request->all();

        if (!empty($data['logo_url'])) {
            $data['logo_url'] = extractFileNameFromUrl($data['logo_url'], 'partners');
        }

        $data['allowed_content_types'] = $request->input('content_types', []);

        // Gestion upload contrat
        if ($request->hasFile('contract_file') && $request->file('contract_file')->isValid()) {
            $file      = $request->file('contract_file');
            $filename  = 'contract_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/partners/contracts', $filename);
            $data['contract_url'] = 'partners/contracts/' . $filename;
        } else {
            unset($data['contract_file']);
        }

        $partner = $this->partnerService->getPartnerById($id);

        // Create user account if partner doesn't have one and admin requests it
        if ($request->boolean('create_account') && !$partner->user_id) {
            $user = User::create([
                'first_name' => $request->account_first_name,
                'last_name'  => $request->account_last_name,
                'email'      => $request->account_email,
                'password'   => Hash::make($request->account_password),
                'user_type'  => 'partner',
            ]);
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'partner', 'guard_name' => 'web'], ['title' => 'Partner', 'is_fixed' => true]);
            $user->assignRole('partner');
            $data['user_id'] = $user->id;
        }

        $this->partnerService->updatePartner($id, $data);

        $message = __('messages.update_form', ['form' => __('partner.title')]);
        return redirect()->route('backend.partners.index')->with('success', $message);
    }

    public function deleteContract(int $id)
    {
        $partner = $this->partnerService->getPartnerById($id);
        if ($partner->contract_url) {
            Storage::delete('public/' . $partner->contract_url);
            $partner->update(['contract_url' => null, 'contract_status' => 'none', 'contract_signed_at' => null]);
        }
        return redirect()->back()->with('success', __('partner::partner.contract_deleted'));
    }

    public function destroy(int $id)
    {
        $this->partnerService->deletePartner($id);
        $message = __('messages.delete_form', ['form' => __('partner.title')]);
        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function restore(int $id)
    {
        $this->partnerService->restorePartner($id);
        $message = __('messages.restore_form', ['form' => __('partner.title')]);
        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function forceDelete(int $id)
    {
        $this->partnerService->forceDeletePartner($id);
        $message = __('messages.permanent_delete_form', ['form' => __('partner.title')]);
        return response()->json(['message' => $message, 'status' => true], 200);
    }
}
