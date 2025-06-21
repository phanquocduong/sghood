<?php
namespace App\Http\Requests\Apis;
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
            'message' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.required' => 'Vui lòng chọn người nhận',
            'receiver_id.exists' => 'Người nhận không tồn tại',
            'message.required' => 'Vui lòng nhập tin nhắn',
            'message.string' => 'Tin nhắn phải là chuỗi ký tự',
        ];
    }
}
