<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('user_name', 'like', '%' . $request->search . '%')
                  ->orWhere('model_name', 'like', '%' . $request->search . '%')
                  ->orWhere('action', 'like', '%' . $request->search . '%');
            });
        }

        $logs    = $query->paginate(25)->withQueryString();
        $actions = AuditLog::distinct()->pluck('action');
        $module_action = 'List';

        return view('backend.audit-log.index', compact('logs', 'actions', 'module_action'));
    }
}
