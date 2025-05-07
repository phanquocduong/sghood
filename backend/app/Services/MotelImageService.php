<?php

namespace App\Services;

use App\Models\MotelImage;

class MotelImageService
{
    public function getAll()
    {
        return MotelImage::all();
    }

    public function getById($id)
    {
        return MotelImage::find($id);
    }

    public function create(array $data)
    {
        return MotelImage::create($data);
    }

    public function update($id, array $data)
    {
        $image = MotelImage::find($id);
        if (!$image) return null;

        $image->update($data);
        return $image;
    }

    public function handleImage($imageFile)
    {
        if ($imageFile && $imageFile->isValid()) {
            $filename = time() . '_' . $imageFile->getClientOriginalName();
            $path = $imageFile->storeAs('motel', $filename, 'public');
            return 'storage/' . $path; // php artisan storage:link
        }
        return null;
    }

    public function delete($id)
    {
        $image = MotelImage::find($id);
        if (!$image) return false;

        return $image->delete();
    }
}
