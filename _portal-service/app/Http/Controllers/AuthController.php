<?php

namespace App\Http\Controllers;

use App\Constants\UserConst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('_admin.auth.login');
    }

    public function doLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return $this->redirectByRole(Auth::user());
        }

        return back()->withErrors([
            'login_error' => 'Email atau Password tidak sesuai, periksa kembali',
        ])->onlyInput('email');
    }

    private function redirectByRole($user)
    {
        switch ($user->access_type) {
            case UserConst::SUPERADMIN:
                return redirect()->route('admin.dashboard');
            case UserConst::PARTNER:
                return redirect()->route('partner.dashboard');
            default:
                return redirect()->intended(route('admin.dashboard'));
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
