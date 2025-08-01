<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
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
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ];
    }
    public function messages(): array
    {
        return [
            'receiver_id.required' => 'Vui lòng chọn người nhận tin nhắn.',
            'receiver_id.exists' => 'Người nhận không tồn tại.',
            'message.required' => 'Vui lòng nhập nội dung tin nhắn.',
            'message.string' => 'Nội dung tin nhắn phải là một chuỗi.',
            'message.max' => 'Nội dung tin nhắn không được vượt quá 1000 ký tự.',
        ];
    }
}
