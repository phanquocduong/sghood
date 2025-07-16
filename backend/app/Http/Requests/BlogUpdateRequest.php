<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogUpdateRequest extends FormRequest
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
            'status' => 'nullable|string|in:draft,published',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'author_id' => 'required|exists:users,id', // Assuming you want to validate the author_id against the users table
        ];
    }
    public function messages()
    {
        return [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'content.required' => 'Nội dung là bắt buộc.',
            'thumbnail.image' => 'Hình ảnh phải là một tệp hình ảnh.',
            'thumbnail.mimes' => 'Hình ảnh phải có định dạng: jpg, jpeg, png.',
            'thumbnail.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'author_id.required' => 'ID tác giả là bắt buộc.',
            'author_id.exists' => 'ID tác giả không hợp lệ.',
        ];
    }
}
