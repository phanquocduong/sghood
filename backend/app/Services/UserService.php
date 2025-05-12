<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function fetchUsers(string $querySearch, string $sortOption, string $perPage)
    {
        try {
            $query = User::query();

            if ($querySearch !== '') {
                $query->where('name', 'like', '%' . $querySearch . '%')
                      ->orWhere('email', 'like', '%' . $querySearch . '%');
            }

            $sort = $this->handleSortOption($sortOption);
            $query->orderBy($sort['field'], $sort['order']);

            $users = $query->paginate($perPage);

            return ['data' => $users];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function handleSortOption(string $sortOption)
    {
        $sortOptions = [
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'email_asc' => ['email', 'asc'],
            'email_desc' => ['email', 'desc'],
            '' => ['created_at', 'desc'], // Giá trị mặc định
        ];

        $sort = $sortOptions[$sortOption] ?? ['created_at', 'desc'];
        return [
            'field' => $sort[0],
            'order' => $sort[1]
        ];
    }

    public function getAllUsers(string $querySearch, string $sortOption, string $perPage)
    {
        return $this->fetchUsers($querySearch, $sortOption, $perPage);
    }

    public function getUser(string $id)
    {
        try {
            $user = User::findOrFail($id);
            return ['data' => $user];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function update(string $id, array $data)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $this->handleImages($user, $data, [
                'avatar' => 'avatars',
                'front_id_card_image' => 'id_cards',
                'back_id_card_image' => 'id_cards',
            ]);

            $user->update($data);

            DB::commit();
            return ['data' => $user];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Cập nhật người dùng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    private function handleImages($user, &$data, array $fields)
    {
        foreach ($fields as $field => $folder) {
            if (isset($data[$field]) && $data[$field] instanceof \Illuminate\Http\UploadedFile) {
                if ($user->$field) {
                    $path = str_replace(Storage::url(''), '', $user->$field);
                    Storage::disk('public')->delete($path);
                }
                $data[$field] = $this->handleImage($data[$field], $folder, $user->name);
            }
        }
    }

    public function handleImage($image, string $folder, string $userName = 'name')
    {
        try {
            $sanitizedUserName = str_replace(' ', '_', $userName);

            $imageName = $sanitizedUserName . '-' . time() . '-' . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs($folder, $imageName, 'public');
            return Storage::url($imagePath);
        } catch (\Throwable $e) {
            Log::error('Lỗi khi tải hình ảnh: ' . $e->getMessage());
            return false;
        }
    }
}
