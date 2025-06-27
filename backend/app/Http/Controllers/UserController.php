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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.user')->with('success', 'User deleted successfully.');
    }

}
