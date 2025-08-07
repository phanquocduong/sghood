<?php

namespace App\Http\Requests\Apis;

use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ReturnRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được phép gửi request này không.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Quy tắc xác thực cho request.
     *
     * @return array
     */
    public function rules(): array
    {
        $contractId = $this->route('id');

        return [
            'is_cash_refunded' => 'required|boolean',
            'bank_name' => 'nullable|required_if:is_cash_refunded,false|string|max:255',
            'account_number' => 'nullable|required_if:is_cash_refunded,false|string|max:50',
            'account_holder' => 'nullable|required_if:is_cash_refunded,false|string|max:255',
            'check_out_date' => [
                'required',
                'date_format:d/m/Y',
                'after:today',
                function ($attribute, $value, $fail) use ($contractId) {
                    $contract = Contract::findOrFail($contractId);
                    $endDate = Carbon::parse($contract->end_date);
                    if ($endDate->isValid()) {
                        $maxDate = $endDate->copy()->addDays(30);
                        $checkOutDate = Carbon::createFromFormat('d/m/Y', $value);
                        if ($checkOutDate->gt($maxDate)) {
                            $fail('Ngày dự kiến trả phòng không được vượt quá 30 ngày sau ngày kết thúc hợp đồng (' . $maxDate->format('d/m/Y') . ').');
                        }
                    } else {
                        $fail('Ngày kết thúc hợp đồng không hợp lệ.');
                    }
                },
            ],
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh bằng tiếng Việt.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'is_cash_refunded.required' => 'Vui lòng chọn phương thức hoàn tiền.',
            'is_cash_refunded.boolean' => 'Phương thức hoàn tiền không hợp lệ.',
            'bank_name.required_if' => 'Vui lòng chọn ngân hàng thụ hưởng khi chọn chuyển khoản.',
            'bank_name.string' => 'Tên ngân hàng phải là chuỗi ký tự.',
            'bank_name.max' => 'Tên ngân hàng không được vượt quá 255 ký tự.',
            'account_number.required_if' => 'Vui lòng nhập số tài khoản khi chọn chuyển khoản.',
            'account_number.string' => 'Số tài khoản phải là chuỗi ký tự.',
            'account_number.max' => 'Số tài khoản không được vượt quá 50 ký tự.',
            'account_holder.required_if' => 'Vui lòng nhập tên chủ tài khoản khi chọn chuyển khoản.',
            'account_holder.string' => 'Tên chủ tài khoản phải là chuỗi ký tự.',
            'account_holder.max' => 'Tên chủ tài khoản không được vượt quá 255 ký tự.',
            'check_out_date.required' => 'Vui lòng chọn ngày dự kiến trả phòng.',
            'check_out_date.date_format' => 'Ngày dự kiến trả phòng phải có định dạng DD/MM/YYYY (ví dụ: 31/12/2025).',
            'check_out_date.after' => 'Ngày dự kiến trả phòng phải từ ngày mai trở đi.',
        ];
    }
}
