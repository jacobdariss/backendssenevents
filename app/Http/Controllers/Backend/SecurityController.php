<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function index()
    {
        // Variables requises par le layout setting::backend.setting.index
        $module_title = 'messages.security';

        return view('backend.security.index', compact('module_title'));
    }

    public function toggle2FA(Request $request)
    {
        $key     = in_array($request->input('setting_key'), ['admin_2fa_enabled', 'partner_2fa_enabled'])
            ? $request->input('setting_key')
            : 'admin_2fa_enabled';
        $enabled = $request->has('admin_2fa_enabled') ? 1 : 0;

        Setting::add($key, $enabled, 'string', null);

        $message = $enabled
            ? __('messages.2fa_enabled_success')
            : __('messages.2fa_disabled_success');

        return redirect()->back()->with('success', $message);
    }
}
