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
        //
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
    public function update(Request $request, $id) {
        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->status = $request->status;
        $user->save();

        return redirect()->route('users.user')->with('success', 'Cập nhật thành công');
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:Người đăng ký,Người thuê,Quản trị viên',
        ]);

        $auth = auth()->user();
        $target = User::findOrFail($id);

        // Không cho tự sửa chính mình
        if ($auth->id === $target->id) {
            return back()->with('error', 'Bạn không thể sửa chính mình.');
        }

        // Admin thường không được sửa Admin hoặc Super Admin
        if (!$auth->is_super_admin) {
            if ($target->role === 'Quản trị viên') {
                return back()->with('error', 'Bạn không có quyền sửa quản trị viên.');
            }
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

        if ($auth->id === $target->id) {
            return back()->with('error', 'Bạn không thể khoá chính mình.');
        }

        // Admin thường không được sửa Admin hoặc Super Admin
        if (!$auth->is_super_admin) {
            if ($target->role === 'Quản trị viên') {
                return back()->with('error', 'Bạn không có quyền khoá quản trị viên.');
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

}
