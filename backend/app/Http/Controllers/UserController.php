<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function index(Request $request)
    {
        $users = $this->userService->getFilteredUsers($request);
        return view('users.user', compact('users'));
    }
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.editUser', compact('user'));
    }
    public function update(Request $request, $id) {
        if (Auth::id() == $id) {
        return redirect()->back()->with('error', 'Bạn không thể chỉnh sửa chính mình.');
        }
        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->status = $request->status;
        $user->save();

        return redirect()->route('users.user')->with('success', 'Cập nhật thành công');
    }
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.user')->with('success', 'User deleted successfully.');
    }

}
