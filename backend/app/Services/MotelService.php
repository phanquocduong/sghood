<?php
// app/Services/MotelService.php
namespace App\Services;

use App\Models\Motel;
use Illuminate\Http\Request;

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
        return Motel::create($data);
    }

    public function update($id, array $data)
    {
        $motel = Motel::find($id);
        if (!$motel)
            return null;

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
}
