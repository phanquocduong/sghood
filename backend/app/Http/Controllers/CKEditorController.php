<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CKEditorController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');

            // Lưu file vào thư mục images/blogs trong disk public
            $path = $file->store('images/blogs', 'public');

            // Tạo URL public
            $url = Storage::url($path);

            return response()->json([
                'url' => $url,
            ]);
        }

        return response()->json([
            'error' => 'No file uploaded.'
        ], 400);
    }
}
