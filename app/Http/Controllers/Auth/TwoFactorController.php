<?php

namespace App\Http\Controllers\Auth;

use App\Events\Auth\UserLoginSuccess;
use App\Http\Controllers\Controller;
use App\Mail\sendOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TwoFactorController extends Controller
{
    public function create(Request $request)
    {
        if (!$request->session()->has('2fa_pending_user_id')) {
            return redirect()->route('admin-login');
        }

        return view('auth.two_factor');
    }

    public function store(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        $pendingUserId = $request->session()->get('2fa_pending_user_id');
        $storedOtp     = $request->session()->get('2fa_otp');
        $expiresAt     = $request->session()->get('2fa_otp_expires_at');
        $remember      = $request->session()->get('2fa_remember', false);

        if (!$pendingUserId || !$storedOtp || !$expiresAt) {
            return redirect()->route('admin-login')
                ->withErrors(['email' => __('messages.session_expired_please_login')]);
        }

        if (now()->timestamp > $expiresAt) {
            $request->session()->forget(['2fa_pending_user_id', '2fa_otp', '2fa_otp_expires_at', '2fa_remember']);

            return redirect()->route('admin-login')
                ->withErrors(['email' => __('messages.otp_expired')]);
        }

        if ($request->otp !== $storedOtp) {
            return back()->withErrors(['otp' => __('messages.invalid_otp')]);
        }

        $user = User::find($pendingUserId);

        if (!$user || !$user->status) {
            $request->session()->forget(['2fa_pending_user_id', '2fa_otp', '2fa_otp_expires_at', '2fa_remember']);

            return redirect()->route('admin-login')
                ->withErrors(['email' => __('messages.not_matched')]);
        }

        // OTP valid — complete authentication
        Auth::login($user, $remember);

        $request->session()->forget(['2fa_pending_user_id', '2fa_otp', '2fa_otp_expires_at', '2fa_remember']);
        $request->session()->regenerate();

        Log::info('2FA login success (web)', ['user_id' => $user->id, 'ip' => $request->ip()]);
        event(new UserLoginSuccess($request, $user));

        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('config:cache');
        Artisan::call('route:clear');

        // Partenaire → dashboard partenaire
        if (auth()->user()->hasRole('partner')) {
            return redirect()->route('partner.dashboard');
        }
        return redirect('app/dashboard');
    }

    public function resend(Request $request)
    {
        $pendingUserId = $request->session()->get('2fa_pending_user_id');

        if (!$pendingUserId) {
            return redirect()->route('admin-login');
        }

        $user = User::find($pendingUserId);

        if (!$user) {
            return redirect()->route('admin-login');
        }

        $otp = random_int(100000, 999999);
        $request->session()->put('2fa_otp', (string) $otp);
        $request->session()->put('2fa_otp_expires_at', now()->addMinutes(10)->timestamp);

        try {
            Mail::to($user->email)->send(new sendOtp(['body' => (string) $otp]));
        } catch (\Exception $e) {
            Log::error('2FA OTP resend failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return back()->with('status', __('messages.otp_sent_to_email'));
    }
}
