<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Rooms;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'nullable',
                'sometimes',
                'string',
                'max:255',
            ],
            'price' => 'nullable|sometimes|numeric|min:0',
            'area' => 'nullable|sometimes|numeric|min:0',
            'status' => 'nullable|sometimes|in:Còn trống,Đã thuê,Đang sửa,Ẩn',
            'motel_id' => 'nullable|sometimes|exists:motels,id',
            'device_token' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Tên phòng phải là chuỗi ký tự.',
            'name.max' => 'Tên phòng không được vượt quá 255 ký tự.',
            'price.numeric' => 'Giá phòng phải là số.',
            'price.min' => 'Giá phòng không được âm.',
            'area.numeric' => 'Diện tích phòng phải là số.',
            'area.min' => 'Diện tích phòng không được âm.',
            'status.in' => 'Trạng thái phải là "Còn trống", "Đã thuê", "Đang sửa" hoặc "Ẩn".',
            'motel_id.exists' => 'Nhà trọ không tồn tại.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Dữ liệu không hợp lệ.',
            'errors' => $validator->errors(),
        ], 422));
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $room = $this->route('room');

            if (!$room) {
                $validator->errors()->add('room', 'Phòng không tồn tại.');
                return;
            }

            $roomId = $room->id;
            $motelId = $this->input('motel_id', $room->motel_id);
            $name = $this->input('name');

            if ($name) {
                $exists = Rooms::where('name', $name)
                    ->where('motel_id', $motelId)
                    ->where('id', '!=', $roomId)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('name', 'Tên phòng đã tồn tại trong nhà trọ này. Vui lòng chọn tên khác.');
                }
            }
        });
    }
}
