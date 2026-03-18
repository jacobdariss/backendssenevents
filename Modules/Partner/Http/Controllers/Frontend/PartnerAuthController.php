<?php

namespace Modules\Partner\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Modules\Partner\Models\Partner;
use Illuminate\Support\Str;

class PartnerAuthController extends Controller
{
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('backend.dashboard');
        }

        return view('partner::frontend.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'company_name'   => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'website'        => 'nullable|url|max:255',
            'description'    => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user account
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'user_type'  => 'partner',
        ]);

        $user->assignRole('partner');
        $user->save();

        // Create linked partner record
        Partner::create([
            'user_id'     => $user->id,
            'name'        => $request->company_name,
            'slug'        => Str::slug($request->company_name),
            'email'       => $request->email,
            'phone'       => $request->phone,
            'website'     => $request->website,
            'description' => $request->description,
            'status'      => 1,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('backend.dashboard')
            ->with('success', __('partner.register_success'));
    }
}
