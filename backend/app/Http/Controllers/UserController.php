<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;


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
        return view('users.user', compact('users'));
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
    public function update(UpdateUserRequest $request, $id)
{
    $user = User::findOrFail($id);

<<<<<<< HEAD:backend/app/Http/Controllers/UserController.php
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
=======
    $data = $request->validated();

    // Handle avatar upload if exists
    if ($request->hasFile('avatar')) {
        $path = $request->file('avatar')->store('avatars', 'public');
        $data['avatar'] = $path;
    }

    // Cập nhật user
    $user->update($data);

    return redirect()->route('admin.users')->with('success', 'Cập nhật thành công');
}


>>>>>>> e1a49cd6a81e6441a78825af6b913d36f815dff4:backend/app/Http/Controllers/Admin/UserController.php

}
