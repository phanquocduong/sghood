<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm()
    {
        return view('login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('username', 'password');
        $field = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials[$field] = $credentials['username'];
        unset($credentials['username']);

        // Kiểm tra xem checkbox "remember" có được chọn hay không
        $remember = $request->has('remember');

        $result = $this->authService->attemptLogin($credentials, $remember);

        if ($result['success']) {
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', $result['message']);
        }

        return back()->with('error', $result['message'])->withInput();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Đăng xuất thành công');
    }
}
