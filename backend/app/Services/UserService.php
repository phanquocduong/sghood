<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function getAllUsers(?string $querySearch = null, ?string $sortOption = null, int $perPage = 10)
    {
        $query = User::query();

        if ($querySearch) {
            $query->where('name', 'like', '%' . $querySearch . '%')
                  ->orWhere('email', 'like', '%' . $querySearch . '%');
        }

        $sortOptions = [
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'email_asc' => ['email', 'asc'],
            'email_desc' => ['email', 'desc'],
        ];

        if (isset($sortOptions[$sortOption])) {
            [$field, $direction] = $sortOptions[$sortOption];
            $query->orderBy($field, $direction);
        }

        return $query->paginate($perPage);
    }

    public function getUser(string $id, bool $withTrashed = false)
    {
        $query = User::query();
        if ($withTrashed) {
            $query->withTrashed();
        }
        return $query->findOrFail($id);
    }

    public function update(string $id, array $validatedRequest)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $this->handleImages($user, $validatedRequest, [
                'avatar' => 'avatars',
                'front_id_card_image' => 'id_cards',
                'back_id_card_image' => 'id_cards',
            ]);

            $user->update($validatedRequest);

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cập nhật người dùng thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    private function handleImages($user, &$validatedRequest, array $fields)
    {
        foreach ($fields as $field => $folder) {
            if (isset($validatedRequest[$field]) && $validatedRequest[$field] instanceof \Illuminate\Http\UploadedFile) {
                if ($user->$field) {
                    $path = str_replace(Storage::url(''), '', $user->$field);
                    Storage::disk('public')->delete($path);
                }
                $validatedRequest[$field] = $this->handleImage($validatedRequest[$field], $folder, $user->name);
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
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải hình ảnh: ' . $e->getMessage());
            throw $e;
        }
    }
}
