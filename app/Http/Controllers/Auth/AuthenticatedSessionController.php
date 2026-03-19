<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Trait\AuthTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\sendOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthenticatedSessionController extends Controller
{
    use AuthTrait;

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {

        $isLogin = $this->loginTrait($request);
        if ($isLogin) {

            if (isset($isLogin['error'])) {

                return back()->withErrors([
                    'email' => $isLogin['error']
                ])->onlyInput('email');
            } elseif ($isLogin['status'] == 406) {

                return back()->withErrors([
                    'email' => $isLogin['message']
                ])->onlyInput('email');
            } elseif ($isLogin['status'] == 403) {

                return back()->withErrors([
                    'email' => $isLogin['message']
                ])->onlyInput('email');

            } else {

                $user = Auth::user();

                // Si le 2FA admin est désactivé → connexion directe
                if (!setting('admin_2fa_enabled', true)) {
                    // Partenaire → dashboard partenaire
                    if ($user->hasRole('partner')) {
                        return redirect()->route('partner.dashboard');
                    }
                    return redirect()->intended('/app/dashboard');
                }

                // 2FA : store user in session, send OTP, logout temporarily
                $otp  = (string) random_int(100000, 999999);

                $request->session()->put('2fa_pending_user_id', $user->id);
                $request->session()->put('2fa_otp', $otp);
                $request->session()->put('2fa_otp_expires_at', now()->addMinutes(10)->timestamp);
                $request->session()->put('2fa_remember', (bool) $request->remember_me);

                Auth::logout();

                $emailSent = false;
                try {
                    Mail::to($user->email)->send(new sendOtp(['body' => $otp]));
                    $emailSent = true;
                } catch (\Exception $e) {
                    Log::error('2FA OTP send failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                    // Log OTP in case email is not configured (dev/debug)
                    Log::info('2FA OTP (email failed)', ['user_id' => $user->id, 'otp' => $otp]);
                }

                return redirect()->route('admin.2fa')
                    ->with('email_sent', $emailSent)
                    ->with('email', $user->email);
            }
        }

        return back()->withErrors([
            'email' => __('messages.not_matched'),
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
