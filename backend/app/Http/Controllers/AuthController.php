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

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('username', 'password');
        $field = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials[$field] = $credentials['username'];
        unset($credentials['username']);

        $remember = $request->has('remember');

        $result = $this->authService->attemptLogin($credentials, $remember);

        if ($request->expectsJson()) {
            if ($result['success']) {
                $request->session()->regenerate();
                return response()->json(['success' => true, 'message' => $result['message']]);
            }
            return response()->json(['success' => false, 'message' => $result['message']], 401);
        }

        if ($result['success']) {
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', $result['message']);
        }

        return back()->with('error', $result['message'])->withInput();
    }

    public function saveFcmToken(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        $user = Auth::user();
        if ($user) {
            $user->update(['fcm_token' => $request->fcm_token]);
            return response()->json(['success' => true, 'message' => 'FCM token saved successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Người dùng chưa được xác thực'], 401);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
