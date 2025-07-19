<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'nullable|string|in:Nháp,Đã xuất bản', // Validate status
            'thumbnail' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'author_id' => 'required|exists:users,id', // Assuming you want to validate the author_id against the users table
            'category' => 'nullable|string|in:Tin tức,Hướng dẫn,Khuyến mãi,Pháp luật,Kinh nghiệm', // Validate category
        ];
    }
    public function messages()
    {
        return [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'content.required' => 'Nội dung là bắt buộc.',
            'thumbnail.image' => 'Hình ảnh phải là một tệp hình ảnh.',
            'thumbnail.required' => 'Hình ảnh là bắt buộc.',
            'thumbnail.mimes' => 'Hình ảnh phải có định dạng: jpg, jpeg, png.',
            'thumbnail.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'author_id.required' => 'ID tác giả là bắt buộc.',
            'author_id.exists' => 'ID tác giả không hợp lệ.',
        ];
    }
}
