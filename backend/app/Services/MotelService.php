<?php
// app/Services/MotelService.php
namespace App\Services;

use App\Models\Motel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MotelService
{
    public function getAll()
    {
        return Motel::all();
    }

    public function getById($id)
    {
        return Motel::with('district')->find($id);
    }

    public function create(array $data)
    {
        if (isset($data['address'])) {
            $data['slug'] = Str::slug($data['address']);
        }
        return Motel::create($data);
    }

    public function update($id, array $data)
    {
        $motel = Motel::find($id);
        if (!$motel)
            return null;

        // tạo slug mới nếu địa chỉ đã thay đổi
        if (isset($data['address']) && $data['address'] !== $motel->address) {
            $data['slug'] = Str::slug($data['address']);
        }
        $motel->update($data);
        return $motel;
    }

    public function delete($id)
    {
        $motel = Motel::find($id);
        if (!$motel)
            return false;

        $motel->delete();
        return true;
    }

    public function restore($id)
    {
        $motel = Motel::withTrashed()->find($id);

        // Nếu không tìm thấy hoặc không bị xóa mềm
        if (!$motel || !$motel->trashed()) {
            return false;
        }

        $motel->restore();
        return true;
    }

    public function search($filters = [])
    {
        $query = Motel::query();

        if (!empty($filters['address'])) {
            $query->where('address', 'like', '%' . $filters['address'] . '%'); // Dùng cho form tìm kiếm với name = address
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']); // Dùng cho bộ lọc trạng thái với name = status
        }

        return $query->paginate(10);
    }
}
