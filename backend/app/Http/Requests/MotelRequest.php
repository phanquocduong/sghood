<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MotelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);
        $motelId = $this->route('id') ?? $this->route('motel');

        // Helper: required or sometimes|required
        $req = fn($rule) => $isUpdate ? "sometimes|required|$rule" : "required|$rule";

        return [
            'name' => array_merge(
                $this->isMethod('put') ? ['sometimes', 'required'] : ['required'],
                [
                    'string',
                    'max:255',
                    Rule::unique('motels', 'name')->ignore($motelId),
                ]
            ),
            'address' => $req('string|max:100'),
            'district_id' => $req('integer|exists:districts,id'),
            'map_embed_url' => $req('url|max:1000'),
            'electricity_fee' => $req('numeric|min:0'),
            'water_fee' => $req('numeric|min:0'),
            'parking_fee' => $req('numeric|min:0'),
            'junk_fee' => $req('numeric|min:0'),
            'internet_fee' => $req('numeric|min:0'),
            'service_fee' => $req('numeric|min:0'),
            'status' => $req('in:Hoạt động,Không hoạt động'),

            'images' => $isUpdate ? 'nullable|array|max:20' : 'required|array|max:20',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            'amenities' => 'nullable|array',
            'amenities.*' => 'integer|exists:amenities,id',

            'main_image_index' => 'nullable|integer|min:0',
            'is_main' => 'nullable|string',
            'new_main_image_index' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên nhà trọ.',
            'name.string' => 'Tên nhà trọ phải là một chuỗi ký tự.',
            'name.max' => 'Tên nhà trọ không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên nhà trọ đã tồn tại.',

            'address.required' => 'Vui lòng nhập địa chỉ.',
            'address.string' => 'Địa chỉ phải là chuỗi.',
            'address.max' => 'Địa chỉ không được vượt quá 100 ký tự.',

            'district_id.required' => 'Vui lòng chọn quận/huyện.',
            'district_id.integer' => 'Quận/huyện không hợp lệ.',
            'district_id.exists' => 'Quận/huyện được chọn không tồn tại.',

            'map_embed_url.required' => 'Vui lòng nhập đường dẫn bản đồ.',
            'map_embed_url.url' => 'Đường dẫn bản đồ không hợp lệ.',
            'map_embed_url.max' => 'Đường dẫn bản đồ quá dài (tối đa 1000 ký tự).',

            'electricity_fee.required' => 'Vui lòng nhập phí điện.',
            'electricity_fee.numeric' => 'Phí điện phải là số.',
            'electricity_fee.min' => 'Phí điện không được âm.',

            'water_fee.required' => 'Vui lòng nhập phí nước.',
            'water_fee.numeric' => 'Phí nước phải là số.',
            'water_fee.min' => 'Phí nước không được âm.',

            'parking_fee.required' => 'Vui lòng nhập phí giữ xe.',
            'parking_fee.numeric' => 'Phí giữ xe phải là số.',
            'parking_fee.min' => 'Phí giữ xe không được âm.',

            'junk_fee.required' => 'Vui lòng nhập phí rác.',
            'junk_fee.numeric' => 'Phí rác phải là số.',
            'junk_fee.min' => 'Phí rác không được âm.',

            'internet_fee.required' => 'Vui lòng nhập phí internet.',
            'internet_fee.numeric' => 'Phí internet phải là số.',
            'internet_fee.min' => 'Phí internet không được âm.',

            'service_fee.required' => 'Vui lòng nhập phí dịch vụ.',
            'service_fee.numeric' => 'Phí dịch vụ phải là số.',
            'service_fee.min' => 'Phí dịch vụ không được âm.',

            'status.required' => 'Vui lòng chọn trạng thái hoạt động.',
            'status.in' => 'Trạng thái không hợp lệ.',

            'images.required' => 'Vui lòng tải lên ít nhất một hình ảnh.',
            'images.array' => 'Hình ảnh phải là một mảng.',
            'images.max' => 'Chỉ được tải tối đa 20 hình ảnh.',
            'images.*.image' => 'Mỗi tệp phải là một hình ảnh.',
            'images.*.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif, webp.',
            'images.*.max' => 'Dung lượng mỗi ảnh không được vượt quá 2MB.',

            'amenities.array' => 'Tiện ích phải là dạng danh sách.',
            'amenities.*.integer' => 'Tiện ích không hợp lệ.',
            'amenities.*.exists' => 'Tiện ích không tồn tại.',

            'main_image_index.integer' => 'Chỉ số ảnh chính phải là số nguyên.',
            'main_image_index.min' => 'Chỉ số ảnh chính không hợp lệ.',

            'new_main_image_index.integer' => 'Chỉ số ảnh chính mới phải là số nguyên.',
            'new_main_image_index.min' => 'Chỉ số ảnh chính mới không hợp lệ.',

            'description.string' => 'Mô tả phải là chuỗi ký tự.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Log::error('Validation failed: ', $validator->errors()->toArray());
        parent::failedValidation($validator);
    }
}
