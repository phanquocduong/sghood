<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = $this->userService->getFilteredUsers($request);
        $message = $users->isEmpty() ? 'Không tìm thấy người dùng phù hợp.' : null;
        return view('users.user', compact('users', 'message'))
            ->with('keyword', $request->keyword)
            ->with('role', $request->role)
            ->with('status', $request->status)
            ->with('sort', $request->sort);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.partials.modal-info', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.editUser', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->status = $request->status;
        $user->save();

        return redirect()->route('users.user')->with('success', 'Cập nhật thành công');
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:Người đăng ký,Người thuê,Quản trị viên,Super admin',
        ]);

        $auth = auth()->user();
        $target = User::findOrFail($id);

        // Không cho tự sửa chính mình
        if ($auth->id === $target->id) {
            return back()->with('error', 'Bạn không thể sửa vai trò của chính mình.');
        }

        // Nếu là Admin thường
        if ($auth->role !== 'Super admin') {
            // Admin thường không thể sửa vai trò của Quản trị viên hoặc Super admin
            if (in_array($target->role, ['Quản trị viên', 'Super admin'])) {
                return back()->with('error', 'Bạn không có quyền sửa vai trò này.');
            }

            // Admin thường không thể tạo Super admin
            if ($request->role === 'Super admin') {
                return back()->with('error', 'Bạn không có quyền gán vai trò Super admin.');
            }
        }

        // Logic chuyển vai trò đặc biệt
        if ($target->role === 'Người thuê' && $request->role !== 'Quản trị viên') {
            return back()->with('error', 'Người thuê chỉ có thể chuyển thành Quản trị viên.');
        }

        if ($target->role === 'Quản trị viên' && $request->role !== 'Người đăng ký') {
            return back()->with('error', 'Quản trị viên chỉ có thể chuyển thành Người đăng ký.');
        }

        $target->role = $request->role;
        $target->save();

        return back()->with('success', 'Cập nhật vai trò thành công.');
    }



    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Hoạt động,Khoá',
        ]);

        $auth = auth()->user();
        $target = User::findOrFail($id);

        // Không cho tự khoá chính mình
        if ($auth->id === $target->id) {
            return back()->with('error', 'Bạn không thể khoá chính mình.');
        }

        // Nếu là Super admin
        if ($auth->role === 'Super admin') {
            $target->status = $request->status;
            $target->save();
            return back()->with('success', 'Cập nhật trạng thái thành công.');
        }
        // Nếu là Admin thường
        else {
            // Admin thường không được khoá Quản trị viên hoặc Super admin
            if (in_array($target->role, ['Quản trị viên', 'Super admin'])) {
                return back()->with('error', 'Bạn không có quyền khoá người dùng này.');
            }
        }

        $target->status = $request->status;
        $target->save();

        return back()->with('success', 'Cập nhật trạng thái thành công.');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.user')->with('success', 'User deleted successfully.');
    }
    // phan lay danh sach user theo id
    public function getByIds(Request $request)
    {
        $ids = $request->input('ids', []);
        $users = User::whereIn('id', $ids)->get();
        return response()->json($users);
    }
}
