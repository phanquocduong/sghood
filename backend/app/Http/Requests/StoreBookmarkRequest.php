<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookmarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motel_id' => 'required|exists:motels,id',
            'user_id'  => 'required|exists:users,id', // Thêm dòng này
        ];
    }

    public function messages(): array
    {
        return [
            'motel_id.required' => 'Vui lòng cung cấp ID của motel.',
            'motel_id.exists'   => 'Motel không tồn tại.',
            'user_id.required'  => 'Vui lòng cung cấp ID của người dùng.',
            'user_id.exists'    => 'Người dùng không tồn tại.',
        ];
    }
}
