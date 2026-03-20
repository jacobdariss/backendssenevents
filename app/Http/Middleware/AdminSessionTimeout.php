<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSessionTimeout
{
    // Timeout admin : 60 minutes d'inactivité
    const TIMEOUT_MINUTES = 60;

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->hasRole(['admin', 'super-admin'])) {
            $lastActivity = session('admin_last_activity');

            if ($lastActivity && (time() - $lastActivity) > self::TIMEOUT_MINUTES * 60) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect()->route('login')
                    ->with('error', __('messages.session_expired'));
            }

            session(['admin_last_activity' => time()]);
        }

        return $next($request);
    }
}
