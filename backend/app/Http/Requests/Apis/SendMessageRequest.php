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
            'message' => 'required|string',
        ];
    }
    
    public function messages(): array
    {
        return [
            'message.required' => 'Vui lòng nhập tin nhắn',
            'message.string' => 'Tin nhắn phải là chuỗi ký tự',
        ];
    }
}
