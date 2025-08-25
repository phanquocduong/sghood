<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\ChangePasswordRequest;
use App\Http\Requests\Apis\UpdateProfileRequest;
use App\Services\Apis\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * Controller xử lý các yêu cầu API liên quan đến thông tin và hành động của người dùng.
 */
class UserController extends Controller
{
    /**
     * @var UserService Dịch vụ xử lý logic người dùng
     */
    protected $userService;

    /**
     * Khởi tạo controller với dịch vụ quản lý người dùng.
     *
     * @param UserService $userService Dịch vụ xử lý logic người dùng
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Cập nhật thông tin hồ sơ người dùng.
     *
     * @param UpdateProfileRequest $request Yêu cầu chứa dữ liệu đã xác thực
     * @return \Illuminate\Http\JsonResponse Phản hồi JSON chứa thông tin người dùng đã cập nhật
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        // Lấy thông tin người dùng đang đăng nhập
        $user = Auth::user();
        // Lấy dữ liệu từ yêu cầu (tên, giới tính, ngày sinh, địa chỉ)
        $data = $request->only(['name', 'gender', 'birthdate', 'address']);
        // Lấy file ảnh đại diện nếu có
        $avatar = $request->file('avatar');

        // Gọi dịch vụ để cập nhật hồ sơ người dùng
        $updatedUser = $this->userService->updateProfile($user, $data, $avatar);

        // Trả về phản hồi JSON với thông tin người dùng đã cập nhật
        return response()->json($updatedUser);
    }

    /**
     * Đổi mật khẩu người dùng.
     *
     * @param ChangePasswordRequest $request Yêu cầu chứa mật khẩu hiện tại và mới
     * @return \Illuminate\Http\JsonResponse Phản hồi JSON với thông báo thành công
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        // Lấy thông tin người dùng đang đăng nhập
        $user = Auth::user();
        // Gọi dịch vụ để đổi mật khẩu
        $this->userService->changePassword($user, $request->current_password, $request->new_password);

        // Trả về phản hồi JSON với thông báo thành công
        return response()->json(['message' => 'Đổi mật khẩu thành công']);
    }

    /**
     * Lưu FCM token để gửi thông báo đẩy.
     *
     * @param Request $request Yêu cầu chứa FCM token
     * @return \Illuminate\Http\JsonResponse Phản hồi JSON với thông báo thành công
     */
    public function saveFcmToken(Request $request)
    {
        // Lấy thông tin người dùng từ yêu cầu
        $user = $request->user();
        // Gọi dịch vụ để cập nhật FCM token
        $this->userService->updateFcmToken($user, $request->fcm_token);
        // Trả về phản hồi JSON với thông báo thành công
        return response()->json(['message' => 'FCM token saved successfully']);
    }

    /**
     * Lấy danh sách quản trị viên.
     *
     * @return \Illuminate\Http\JsonResponse Phản hồi JSON chứa danh sách quản trị viên
     */
    public function getAdmins()
    {
        // Truy vấn danh sách người dùng có vai trò Quản trị viên
        $user = User::where('role', 'Quản trị viên')->get();
        // Trả về phản hồi JSON với danh sách quản trị viên
        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }
}
