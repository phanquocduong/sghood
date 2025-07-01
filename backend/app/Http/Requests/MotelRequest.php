<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MotelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isUpdate = $this->method() === 'PUT' || $this->method() === 'PATCH';

        return [
            'name' => $isUpdate ? 'sometimes|required|string|max:255|unique:motels,name,'.$this->route('motel') : 'required|string|max:255|unique:motels,name',
            'address' => $isUpdate ? 'sometimes|required|string|max:100' : 'required|string|max:100',
            'district_id' => $isUpdate ? 'sometimes|required|integer|exists:districts,id' : 'required|integer|exists:districts,id',
            'map_embed_url' => $isUpdate ? 'sometimes|required|url|max:1000' : 'required|url|max:1000',
            'electricity_fee' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'water_fee' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'description' => 'nullable|string',
            'parking_fee' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'junk_fee' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'internet_fee' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'service_fee' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'status' => $isUpdate ? 'sometimes|required|in:Hoạt động,Không hoạt động' : 'required|in:Hoạt động,Không hoạt động',
            'images' => $isUpdate ? 'nullable|array|max:20' : 'required|array|max:20',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'amenities' => 'nullable|array',
            'amenities.*' => 'integer|exists:amenities,id',
            'main_image_index' => 'nullable|integer|min:0',
            'is_main' => 'nullable|string',
            'new_main_image_index' => 'nullable|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên.',
            'name.unique' => 'Tên đã tồn tại. Vui lòng chọn tên khác.',
            'name.string' => 'Tên phải là một chuỗi.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'address.string' => 'Địa chỉ phải là chuỗi ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 100 ký tự.',
            'district_id.required' => 'Vui lòng chọn quận/huyện.',
            'district_id.integer' => 'Quận/huyện không hợp lệ.',
            'district_id.exists' => 'Quận/huyện không tồn tại.',
            'map_embed_url.required' => 'Vui lòng nhập URL nhúng bản đồ.',
            'map_embed_url.url' => 'URL nhúng bản đồ không hợp lệ.',
            'map_embed_url.max' => 'URL nhúng bản đồ không được vượt quá 1000 ký tự.',
            'electricity_fee.required' => 'Vui lòng nhập phí điện.',
            'electricity_fee.numeric' => 'Phí điện phải là số.',
            'electricity_fee.min' => 'Phí điện không được nhỏ hơn 0.',
            'water_fee.required' => 'Vui lòng nhập phí nước.',
            'water_fee.numeric' => 'Phí nước phải là số.',
            'water_fee.min' => 'Phí nước không được nhỏ hơn 0.',
            'parking_fee.required' => 'Vui lòng nhập phí giữ xe.',
            'parking_fee.numeric' => 'Phí giữ xe phải là số.',
            'parking_fee.min' => 'Phí giữ xe không được nhỏ hơn 0.',
            'junk_fee.required' => 'Vui lòng nhập phí rác.',
            'junk_fee.numeric' => 'Phí rác phải là số.',
            'junk_fee.min' => 'Phí rác không được nhỏ hơn 0.',
            'internet_fee.required' => 'Vui lòng nhập phí internet.',
            'internet_fee.numeric' => 'Phí internet phải là số.',
            'internet_fee.min' => 'Phí internet không được nhỏ hơn 0.',
            'service_fee.required' => 'Vui lòng nhập phí dịch vụ.',
            'service_fee.numeric' => 'Phí dịch vụ phải là số.',
            'service_fee.min' => 'Phí dịch vụ không được nhỏ hơn 0.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'images.required' => 'Vui lòng chọn ít nhất một hình ảnh.',
            'images.array' => 'Hình ảnh phải là mảng.',
            'images.max' => 'Tối đa 20 hình ảnh.',
            'images.*.image' => 'File phải là hình ảnh.',
            'images.*.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif, webp.',
            'images.*.max' => 'Hình ảnh không được vượt quá 2MB.',
            'amenities.array' => 'Tiện ích phải là mảng.',
            'amenities.*.integer' => 'Tiện ích không hợp lệ.',
            'amenities.*.exists' => 'Tiện ích không tồn tại.',
            'main_image_index.integer' => 'Chỉ số ảnh chính không hợp lệ.',
            'main_image_index.min' => 'Chỉ số ảnh chính không được nhỏ hơn 0.',
        ];
    }
}
