<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\ChangePasswordRequest;
use App\Http\Requests\Apis\UpdateProfileRequest;
use App\Services\Apis\UserService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
    public function getAdmins(){
        $user = User::where('role','Quản trị viên')->get();
        return response()->json($user);
    }
}
