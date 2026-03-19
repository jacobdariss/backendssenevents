<?php

namespace Modules\Partner\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Mail\sendOtp;
use App\Models\User;
use Modules\Partner\Models\Partner;
use Illuminate\Support\Str;

class PartnerAuthController extends Controller
{
    // REGISTER
    public function showRegisterForm()
    {
        if (Auth::check() && Auth::user()->hasRole('partner')) {
            return redirect()->route('partner.dashboard');
        }
        return view('partner::frontend.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
            'company_name' => 'required|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'website'      => 'nullable|url|max:255',
            'description'  => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'user_type'  => 'partner',
            'status'     => 1,
        ]);

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'partner', 'guard_name' => 'web'], ['title' => 'Partner', 'is_fixed' => true]);
            $user->assignRole('partner');

        Partner::create([
            'user_id'     => $user->id,
            'name'        => $request->company_name,
            'slug'        => Str::slug($request->company_name) . '-' . $user->id,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'website'     => $request->website,
            'description' => $request->description,
            'status'      => 1,
        ]);

        return redirect()->route('partner.login')
            ->with('success', __('partner::partner.register_success'));
    }

    // LOGIN
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->hasRole('partner')) {
            return redirect()->route('partner.dashboard');
        }
        return view('partner::frontend.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember_me'))) {
            return back()->withErrors(['email' => __('auth.failed')])->withInput($request->only('email'));
        }

        $user = Auth::user();

        if (!$user->hasRole('partner')) {
            Auth::logout();
            return back()->withErrors(['email' => __('partner::partner.not_a_partner')]);
        }

        if (setting('partner_2fa_enabled', true)) {
            $otp = (string) random_int(100000, 999999);
            $request->session()->put('partner_2fa_pending_user_id', $user->id);
            $request->session()->put('partner_2fa_otp', $otp);
            $request->session()->put('partner_2fa_expires_at', now()->addMinutes(10)->timestamp);
            $request->session()->put('partner_2fa_remember', $request->boolean('remember_me'));
            Auth::logout();

            $emailSent = false;
            try {
                Mail::to($user->email)->send(new sendOtp(['body' => $otp]));
                $emailSent = true;
            } catch (\Exception $e) {
                Log::error('Partner 2FA failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                Log::info('Partner 2FA OTP', ['user_id' => $user->id, 'otp' => $otp]);
            }

            return redirect()->route('partner.2fa')
                ->with('email_sent', $emailSent)
                ->with('email', $user->email);
        }

        $request->session()->regenerate();
        return redirect()->route('partner.dashboard');
    }

    // 2FA
    public function show2FA(Request $request)
    {
        if (!$request->session()->has('partner_2fa_pending_user_id')) {
            return redirect()->route('partner.login');
        }
        return view('partner::frontend.two_factor');
    }

    public function verify2FA(Request $request)
    {
        $request->validate(['otp' => 'required|string|digits:6']);

        $pendingUserId = $request->session()->get('partner_2fa_pending_user_id');
        $storedOtp     = $request->session()->get('partner_2fa_otp');
        $expiresAt     = $request->session()->get('partner_2fa_expires_at');
        $remember      = $request->session()->get('partner_2fa_remember', false);

        if (!$pendingUserId || !$storedOtp) {
            return redirect()->route('partner.login');
        }

        if (now()->timestamp > $expiresAt) {
            $request->session()->forget(['partner_2fa_pending_user_id', 'partner_2fa_otp', 'partner_2fa_expires_at', 'partner_2fa_remember']);
            return back()->withErrors(['otp' => __('messages.otp_expired')]);
        }

        if ($request->otp !== $storedOtp) {
            return back()->withErrors(['otp' => __('messages.invalid_otp')]);
        }

        $user = User::findOrFail($pendingUserId);
        Auth::login($user, $remember);
        $request->session()->forget(['partner_2fa_pending_user_id', 'partner_2fa_otp', 'partner_2fa_expires_at', 'partner_2fa_remember']);
        $request->session()->regenerate();

        return redirect()->route('partner.dashboard');
    }

    public function resend2FA(Request $request)
    {
        $pendingUserId = $request->session()->get('partner_2fa_pending_user_id');
        if (!$pendingUserId) return redirect()->route('partner.login');

        $user = User::findOrFail($pendingUserId);
        $otp  = (string) random_int(100000, 999999);
        $request->session()->put('partner_2fa_otp', $otp);
        $request->session()->put('partner_2fa_expires_at', now()->addMinutes(10)->timestamp);

        try {
            Mail::to($user->email)->send(new sendOtp(['body' => $otp]));
        } catch (\Exception $e) {
            Log::info('Partner 2FA OTP (resend)', ['user_id' => $user->id, 'otp' => $otp]);
        }

        return back()->with('status', __('messages.otp_sent_to_email'));
    }

    // LOGOUT
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('partner.login');
    }
}
