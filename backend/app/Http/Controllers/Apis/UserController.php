<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\ChangePasswordRequest;
use App\Http\Requests\Apis\UpdateProfileRequest;
use App\Services\Apis\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $data = $request->only(['name', 'gender', 'birthdate', 'address']);
        $avatar = $request->file('avatar');

        $updatedUser = $this->userService->updateProfile($user, $data, $avatar);

        return response()->json($updatedUser);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();
        $this->userService->changePassword($user, $request->current_password, $request->new_password);

        return response()->json(['message' => 'Đổi mật khẩu thành công']);
    }

    public function saveFcmToken(Request $request)
    {
        $user = Auth::user();
        $this->userService->updateFcmToken($user, $request->fcm_token);
        return response()->json(['message' => 'FCM token saved successfully']);
    }
}
