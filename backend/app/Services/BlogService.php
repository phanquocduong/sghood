<?php

namespace App\Services;

use App\Models\Blog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;



class BlogService
{
    public function getAllBlogs($querySearch = null, $status = null, $sortOption = 'created_at_desc', $perPage = 10)
    {
        $query = Blog::with('author');
          if ($querySearch) {
            $query->where(function ($q) use ($querySearch) {
                $q->where('title', 'like', "%{$querySearch}%")
                    ->orWhere('author_id', 'like', "%{$querySearch}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($sortOption) {
            // Ví dụ: created_at_desc hoặc created_at_asc
            [$column, $direction] = explode('_', $sortOption) + [null, 'desc'];
            if (in_array($column, ['created_at', 'updated_at', 'title']) && in_array($direction, ['asc', 'desc'])) {
                $query->orderBy($column, $direction);
            }
        }

        return $query->paginate($perPage);
    }
    public function getDeleteBlogs($querySearch = null, $status = null, $sortOption = 'created_at_desc', $perPage = 10)
    {
        $query = Blog::onlyTrashed();
        // $query = Blog::with('author');
          if ($querySearch) {
            $query->where(function ($q) use ($querySearch) {
                $q->where('title', 'like', "%{$querySearch}%")
                    ->orWhere('author_id', 'like', "%{$querySearch}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($sortOption) {
            // Ví dụ: created_at_desc hoặc created_at_asc
            [$column, $direction] = explode('_', $sortOption) + [null, 'desc'];
            if (in_array($column, ['created_at', 'updated_at', 'title']) && in_array($direction, ['asc', 'desc'])) {
                $query->orderBy($column, $direction);
            }
        }

        return $query->paginate($perPage);
    }
    public function getBlogById(int $id)
    {
        return Blog::FindorFail($id);
    }
    public function createBlog(array $data)
    {
        try {
            $blog = new Blog();
            $blog->title = $data['title'];
            $blog->slug = Str::slug($data['title']);
            // $blog->excerpt = isset($data['excerpt']) ? $data['excerpt'] : '';
            $blog->content = $data['content'];
            // $blog->thumbnail = isset($data['thumbnail']) ? $data['thumbnail'] : null;
            $blog->thumbnail = $this->uploadBlogImage($data['thumbnail']);
            $blog->status = isset($data['status']) ? $data['status'] : 'draft';
            $blog->author_id = $data['author_id'];
            $blog->save();

            return $blog;
        } catch (\Exception $e) {
            Log::error('Error creating blog: ' . $e->getMessage());
            return null;
        }
    }

    public function updateBlog(int $id, array $data)
    {
        try {
            $blog = Blog::findOrFail($id);
            $blog->title = $data['title'];
            $blog->slug = Str::slug($data['title']);
            // $blog->excerpt = isset($data['excerpt']) ? $data['excerpt'] : '';
            $blog->content = $data['content'];
            // $blog->thumbnail = isset($data['thumbnail']) ? $data['thumbnail'] : null;
            if (!empty($data['thumbnail'])) {
                $blog->thumbnail = $this->uploadBlogImage($data['thumbnail']);
            }
            $blog->status = isset($data['status']) ? $data['status'] : 'draft';
            $blog->author_id = $data['author_id'];
            $blog->save();

            return $blog;
        } catch (\Exception $e) {
            Log::error('Error creating blog: ' . $e->getMessage());
            return null;
        }
    }

    public function deleteBlog(int $id)
    {
        try {
            $blog = Blog::findorFail($id);
            if (!$blog) {
                return ['error' => 'Không tìm thấy bài viết', 'status' => 400];
            }
            $blog->delete();
            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa bài viết'.$e->getMessage(), 'status' => 500];
        }
    }

    private function uploadBlogImage(UploadedFile $imageFile): string|false
    {
        try {
            $manager = new ImageManager(new Driver());
            $filename = 'images/blogs/blog-' . time() . '.' . 'webp';
            $image = $manager->read($imageFile)->toWebp(quality: 85)->toString();

            Storage::disk('public')->put($filename, $image);
            return '/storage/' . $filename;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public function restoreBlog(int $id)
    {
        try {
            $blog = Blog::onlyTrashed()->find($id);
            if (!$blog) {
                return ['error' => 'Không tìm thấy bài viết', 'status' => 400];
            }
            $blog->restore();
            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi khôi phục bài viết'.$e->getMessage(), 'status' => 500];
        }
    }

    public function forcedeleteBlog(int $id)
    {
        try {
            $blog = Blog::onlyTrashed()->find($id);
            if (!$blog) {
                return ['error' => 'Không tìm thấy bài viết', 'status' => 400];
            }
            $blog->forceDelete();
            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa bài viết vĩnh viễn'.$e->getMessage(), 'status' => 500];
        }
    }

    public function detailBlog($id)
    {
        return Blog::find($id);
    }
}
