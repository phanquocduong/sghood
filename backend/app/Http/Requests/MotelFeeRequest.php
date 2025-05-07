<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MotelFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motel_id' => 'required|exists:motels,id',
            'fee_type' => 'required|in:Điện,Nước,Giữ xe,Rác,Internet,Dịch vụ',
            'fee_amount' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'motel_id.required' => 'Vui lòng chọn nhà trọ.',
            'motel_id.exists' => 'Nhà trọ không tồn tại.',
            'fee_type.required' => 'Vui lòng chọn loại phí.',
            'fee_type.in' => 'Loại phí không hợp lệ.',
            'fee_amount.required' => 'Vui lòng nhập số tiền.',
            'fee_amount.integer' => 'Số tiền phải là số nguyên.',
            'fee_amount.min' => 'Số tiền phải lớn hơn hoặc bằng 0.',
        ];
    }
}
