<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function index()
    {
        return view('backend.security.index');
    }

    public function toggle2FA(Request $request)
    {
        $enabled = $request->has('admin_2fa_enabled') ? 1 : 0;

        Setting::add('admin_2fa_enabled', $enabled, 'string', null);

        $message = $enabled
            ? __('messages.2fa_enabled_success')
            : __('messages.2fa_disabled_success');

        return redirect()->back()->with('success', $message);
    }
}
