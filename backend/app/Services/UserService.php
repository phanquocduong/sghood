<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function fetchUsers(bool $onlyTrashed, string $querySearch, string $sortOption, int $perPage): array {
        try {
            $query = $onlyTrashed ? User::onlyTrashed() : User::query();

            $this->applyFilters($query, $querySearch);
            $this->applySorting($query, $sortOption);

            $users = $query->paginate($perPage);

            return ['data' => $users];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách người dùng', 'status' => 500];
        }
    }

    private function applyFilters($query, string $querySearch): void {
        if ($querySearch !== '') {
            $query->where('name', 'like', '%' . $querySearch . '%')
                  ->orWhere('email', 'like', '%' . $querySearch . '%');
        }
    }

    private function applySorting($query, string $sortOption): void {
        $sort = $this->handleSortOption($sortOption);
        $query->orderBy($sort['field'], $sort['order']);
    }

    public function handleSortOption(string $sortOption): array {
        switch ($sortOption) {
            case 'name_asc':
                return ['field' => 'name', 'order' => 'asc'];
            case 'name_desc':
                return ['field' => 'name', 'order' => 'desc'];
            case 'email_asc':
                return ['field' => 'email', 'order' => 'asc'];
            case 'email_desc':
                return ['field' => 'email', 'order' => 'desc'];
            default:
                return ['field' => 'created_at', 'order' => 'desc'];
        }
    }

    public function getAllUsers(string $querySearch, string $sortOption, int $perPage): array {
        return $this->fetchUsers(false, $querySearch, $sortOption, $perPage);
    }

    public function getUser(string $id): array {
        try {
            $user = User::find($id);
            if (!$user) {
                return ['error' => 'Người dùng không tìm thấy', 'status' => 404];
            }

            return ['data' => $user];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy người dùng', 'status' => 500];
        }
    }

    public function update(string $id, array $data, ?array $imageFiles = []): array
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $failedUploads = $this->processUserImages($user, $data, $imageFiles);

            $user->update($data);

            DB::commit();

            $result = ['data' => $user];
            if (!empty($failedUploads)) {
                $result['warnings'] = ['failed_images' => $failedUploads];
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Cập nhật người dùng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function destroy(string $id): array
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $user->delete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Xóa người dùng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function restore(string $id): array
    {
        DB::beginTransaction();
        try {
            $user = User::onlyTrashed()->findOrFail($id);
            $user->restore();

            DB::commit();
            return ['data' => $user];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Khôi phục người dùng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    public function forceDelete(string $id): array
    {
        DB::beginTransaction();
        try {
            $user = User::withTrashed()->findOrFail($id);

            $this->deleteUserImage($user->avatar);
            $this->deleteUserImage($user->front_id_card_image);
            $this->deleteUserImage($user->back_id_card_image);

            $user->forceDelete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Xóa vĩnh viễn người dùng thất bại: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    private function processUserImages(User $user, array &$data, ?array $imageFiles = []): array
    {
        $failedUploads = [];
        $fields = [
            'avatar' => 'avatars',
            'front_id_card_image' => 'id_cards',
            'back_id_card_image' => 'id_cards',
        ];

        foreach ($fields as $field => $folder) {
            if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
                if ($user->$field) {
                    $this->deleteUserImage($user->$field);
                }
                $imagePath = $this->uploadUserImage($data[$field], $folder, $user->name);
                if ($imagePath) {
                    $data[$field] = $imagePath;
                } else {
                    $failedUploads[] = $data[$field]->getClientOriginalName();
                    unset($data[$field]);
                }
            }
        }

        return $failedUploads;
    }

    private function uploadUserImage(UploadedFile $imageFile, string $folder): string|false
    {
        try {
            $imageName = 'user-' . time() . '-' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = $imageFile->storeAs('images/' . $folder, $imageName, 'public');
            return Storage::url($imagePath);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    private function deleteUserImage(?string $imagePath): void
    {
        try {
            if ($imagePath) {
                $filePath = str_replace('/storage/', '', $imagePath);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}
