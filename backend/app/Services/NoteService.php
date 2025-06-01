<?php
namespace App\Services;

use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;

class NoteService
{
    // Tiêu chuẩn hóa phản hồi thành công
    protected function successResponse($data)
    {
        return ['data' => $data];
    }

    // Tiêu chuẩn hóa phản hồi lỗi
    protected function errorResponse($message, $status = 400)
    {
        return ['error' => $message, 'status' => $status];
    }

    // Lấy danh sách ghi chú theo các tiêu chí (method chính cho controller)
    public function fetchNotes(string $querySearch, string $userId, string $type, string $sortOption, string $perPage): array
    {
        try {
            $query = Note::with('user');

            // Áp dụng bộ lọc
            $this->applyFilters($query, $querySearch, $userId, $type);

            // Áp dụng sắp xếp
            $this->applySorting($query, $sortOption);

            // Phân trang
            $notes = $query->paginate($perPage);

            return $this->successResponse($notes);
        } catch (Exception $e) {
            \Log::error('Error fetching notes: ' . $e->getMessage());
            return $this->errorResponse('Không thể tải danh sách ghi chú: ' . $e->getMessage(), 500);
        }
    }

    // Áp dụng bộ lọc cho truy vấn
    private function applyFilters($query, string $querySearch, string $userId, string $type): void
    {
        // Tìm kiếm theo content
        if ($querySearch !== '') {
            $query->where('content', 'LIKE', '%' . $querySearch . '%');
        }

        // Lọc theo user_id
        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        // Lọc theo type
        if (!empty($type)) {
            $query->where('type', $type);
        }
    }

    // Áp dụng sắp xếp cho truy vấn
    private function applySorting($query, string $sortOption): void
    {
        $sort = $this->handleSortOption($sortOption);
        $query->orderBy($sort['field'], $sort['order']);
    }

    // Xử lý tùy chọn sắp xếp
    private function handleSortOption(string $sortOption): array
    {
        switch ($sortOption) {
            case 'content_asc':
                return ['field' => 'content', 'order' => 'asc'];
            case 'content_desc':
                return ['field' => 'content', 'order' => 'desc'];
            case 'type_asc':
                return ['field' => 'type', 'order' => 'asc'];
            case 'type_desc':
                return ['field' => 'type', 'order' => 'desc'];
            case 'user_name_asc':
                return ['field' => 'users.name', 'order' => 'asc'];
            case 'user_name_desc':
                return ['field' => 'users.name', 'order' => 'desc'];
            case 'created_at_asc':
                return ['field' => 'created_at', 'order' => 'asc'];
            case 'created_at_desc':
            default:
                return ['field' => 'created_at', 'order' => 'desc'];
        }
    }

    // Lấy danh sách tất cả ghi chú (cho dashboard)
    public function getAllNotes()
    {
        try {
            $notes = Note::with('user')->latest()->get();
            return $this->successResponse($notes);
        } catch (Exception $e) {
            \Log::error('Error fetching all notes: ' . $e->getMessage());
            return $this->errorResponse('Không thể tải danh sách ghi chú: ' . $e->getMessage(), 500);
        }
    }

    // Lấy thông tin chi tiết của một ghi chú
    public function getNote(string $id): array
    {
        try {
            $note = Note::with('user')->find($id);
            if (!$note) {
                return $this->errorResponse('Ghi chú không tìm thấy.', 404);
            }

            // Kiểm tra quyền truy cập (chỉ cho phép xem ghi chú của mình)
            if ($note->user_id !== Auth::id()) {
                return $this->errorResponse('Bạn không có quyền xem ghi chú này.', 403);
            }

            return $this->successResponse($note);
        } catch (Exception $e) {
            \Log::error('Error fetching note: ' . $e->getMessage());
            return $this->errorResponse('Không thể lấy thông tin ghi chú: ' . $e->getMessage(), 500);
        }
    }

    // Tạo ghi chú mới
    public function createNote(array $data): array
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return $this->errorResponse('Bạn cần đăng nhập để tạo ghi chú.', 401);
            }

            // Chuẩn bị dữ liệu
            $noteData = [
                'user_id' => $userId,
                'content' => $data['content'],
                'type' => $data['type'] ?? 'Ghi chú cá nhân',
            ];

            $note = Note::create($noteData);
            return $this->successResponse($note);
        } catch (Exception $e) {
            \Log::error('Error creating note: ' . $e->getMessage());
            return $this->errorResponse('Không thể tạo ghi chú: ' . $e->getMessage(), 500);
        }
    }

    // Cập nhật ghi chú
    public function updateNote(string $id, array $data): array
    {
        try {
            $note = Note::find($id);
            if (!$note) {
                return $this->errorResponse('Ghi chú không tìm thấy.', 404);
            }

            // Kiểm tra quyền chỉnh sửa
            if ($note->user_id !== Auth::id()) {
                return $this->errorResponse('Bạn không có quyền chỉnh sửa ghi chú này.', 403);
            }

            // Cập nhật dữ liệu
            $updateData = [
                'content' => $data['content'],
                'type' => $data['type'] ?? $note->type,
            ];

            $note->update($updateData);
            return $this->successResponse($note);
        } catch (Exception $e) {
            \Log::error('Error updating note: ' . $e->getMessage());
            return $this->errorResponse('Không thể cập nhật ghi chú: ' . $e->getMessage(), 500);
        }
    }

    // Xóa ghi chú
    public function deleteNote(string $id): array
    {
        try {
            $note = Note::find($id);
            if (!$note) {
                return $this->errorResponse('Ghi chú không tìm thấy.', 404);
            }

            // Kiểm tra quyền xóa
            if ($note->user_id !== Auth::id()) {
                return $this->errorResponse('Bạn không có quyền xóa ghi chú này.', 403);
            }

            $note->delete();
            return $this->successResponse(true);
        } catch (Exception $e) {
            \Log::error('Error deleting note: ' . $e->getMessage());
            return $this->errorResponse('Không thể xóa ghi chú: ' . $e->getMessage(), 500);
        }
    }

    // Lấy danh sách người dùng có ghi chú
    public function getUsersWithNotes(): array
    {
        try {
            $users = User::has('notes')->pluck('name', 'id');
            return $this->successResponse($users);
        } catch (Exception $e) {
            \Log::error('Error retrieving users with notes: ' . $e->getMessage());
            return $this->errorResponse('Không thể lấy danh sách người dùng: ' . $e->getMessage(), 500);
        }
    }
}
