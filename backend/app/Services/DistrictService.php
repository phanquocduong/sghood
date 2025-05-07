<?php

namespace App\Services;

use App\Models\District;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DistrictService
{
    public function getAllDistricts()
    {
        return District::all();
    }

    public function getDistrictById($id)
    {
        $district = District::find($id);
        if (!$district) {
            throw new ModelNotFoundException('Quận/Huyện không tồn tại!');
        }
        return $district;
    }

    public function createDistrict(array $data)
    {
        return District::create($data);
    }

    public function updateDistrict($id, array $data)
    {
        $district = $this->getDistrictById($id);
        $district->update($data);
        return $district;
    }

    public function deleteDistrict($id)
    {
        $district = $this->getDistrictById($id);
        $district->delete();
    }

    public function handleDistrictImage($imageFile)
    {
        if ($imageFile && $imageFile->isValid()) {
            $filename = time() . '_' . $imageFile->getClientOriginalName();

            $path = $imageFile->storeAs('district_images', $filename, 'public');

            
            return 'storage/' . $path; // php artisan storage:link
        }

        return null;
    }


    public function deleteDistrictImage($imagePath)
    {
        if ($imagePath) {
            $fullPath = public_path($imagePath);
            if (file_exists($fullPath)) {
                unlink($fullPath); // Xoá file ảnh
            }
        }
        return null;
    }


}