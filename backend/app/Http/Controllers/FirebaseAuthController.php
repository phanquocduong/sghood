<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;

class FirebaseAuthController extends Controller
{
    protected $authService;

    public function __construct(FirebaseAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function auth(AuthRequest $request)
    {
        return $this->authService->authenticate($request->id_token);
    }

    public function register(RegisterRequest $request)
    {
        return $this->authService->register($request->id_token, $request->validated());
    }

    public function logout(Request $request)
    {
        return $this->authService->logout();
    }
}
