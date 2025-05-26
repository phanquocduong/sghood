<?php
// app/Http/Requests/MotelRequest.php
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
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'address' => $isUpdate ? 'sometimes|required|string|max:100' : 'required|string|max:100',
            'district_id' => $isUpdate ? 'sometimes|required|integer|exists:districts,id' : 'required|integer|exists:districts,id',
            'map_embed_url' => $isUpdate ? 'sometimes|required|string|max:1000' : 'required|string|max:1000',
            'description' => $isUpdate ? 'sometimes|required|string' : 'required|string',
            'electricity_fee' => $isUpdate ? 'sometimes|required|integer|min:0' : 'required|integer|min:0',
            'water_fee' => $isUpdate ? 'sometimes|required|integer|min:0' : 'required|integer|min:0',
            'parking_fee' => $isUpdate ? 'sometimes|required|integer|min:0' : 'required|integer|min:0',
            'junk_fee' => $isUpdate ? 'sometimes|required|integer|min:0' : 'required|integer|min:0',
            'internet_fee' => $isUpdate ? 'sometimes|required|integer|min:0' : 'required|integer|min:0',
            'service_fee' => $isUpdate ? 'sometimes|required|integer|min:0' : 'required|integer|min:0',
            'status' => $isUpdate ? 'sometimes|required|in:Hoạt động,Không hoạt động' : 'required|in:Hoạt động,Không hoạt động',
            'images' => $isUpdate ? 'sometimes|required|array|max:20' : 'required|array|max:20',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048|min:1',
            'amenities' => $isUpdate ? 'sometimes|required|array' : 'required|array',
            'amenities.*' => 'integer|exists:amenities,id',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên.',
            'name.string' => 'Tên phải là một chuỗi.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'address.string' => 'Địa chỉ phải là một chuỗi.',
            'address.max' => 'Địa chỉ không được vượt quá 100 ký tự.',
            'district_id.required' => 'Vui lòng chọn quận/huyện.',
            'district_id.integer' => 'ID quận/huyện phải là một số nguyên.',
            'district_id.exists' => 'khu vực không tồn tại.',
            'map_embed_url.required' => 'Vui lòng nhập URL nhúng bản đồ.',
            'map_embed_url.string' => 'URL nhúng bản đồ phải là một chuỗi.',
            'map_embed_url.max' => 'URL nhúng bản đồ không được vượt quá 1000 ký tự.',
            'description.required' => 'Vui lòng nhập mô tả.',
            'description.string' => 'Mô tả phải là một chuỗi.',
            'electricity_fee.required' => 'Vui lòng nhập phí điện.',
            'electricity_fee.integer' => 'Phí điện phải là một số nguyên.',
            'electricity_fee.min' => 'Phí điện phải lớn hơn hoặc bằng 1000.',
            'water_fee.required' => 'Vui lòng nhập phí nước.',
            'water_fee.integer' => 'Phí nước phải là một số nguyên.',
            'water_fee.min' => 'Phí nước phải lớn hơn hoặc bằng 1000.',
            'parking_fee.required' => 'Vui lòng nhập phí giữ xe.',
            'parking_fee.integer' => 'Phí giữ xe phải là một số nguyên.',
            'parking_fee.min' => 'Phí giữ xe phải lớn hơn hoặc bằng 1000.',
            'junk_fee.required' => 'Vui lòng nhập phí rác.',
            'junk_fee.integer' => 'Phí rác phải là một số nguyên.',
            'junk_fee.min' => 'Phí rác phải lớn hơn hoặc bằng 1000.',
            'internet_fee.required' => 'Vui lòng nhập phí internet.',
            'internet_fee.integer' => 'Phí internet phải là một số nguyên.',
            'internet_fee.min' => 'Phí internet phải lớn hơn hoặc bằng 1000.',
            'service_fee.required' => 'Vui lòng nhập phí dịch vụ.',
            'service_fee.integer' => 'Phí dịch vụ phải là một số nguyên.',
            'service_fee.min' => 'Phí dịch vụ phải lớn hơn hoặc bằng 1000.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ. Chọn "Hoạt động" hoặc "Không hoạt động".',
            'images.required' => 'Vui lòng chọn ít nhất một ảnh.',
            'images.array' => 'Bộ sưu tập ảnh phải là một mảng.',
            'images.max' => 'Giới hạn tối đa là 20 ảnh',
            'images.*.image' => 'Ảnh phải là các file ảnh hợp lệ.',
            'images.*.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg, gif hoặc webp.',
            'images.*.max' => 'Ảnh không được vượt quá 2MB.',
            'images.*.min' => 'Ảnh không được là file rỗng.',
            'amenities.required' => 'Vui lòng chọn ít nhất một tiện ích.',
            'amenities.array' => 'Danh sách tiện ích phải là một mảng.',
            'amenities.*.integer' => 'ID tiện ích phải là một số nguyên.',
            'amenities.*.exists' => 'Tiện ích không tồn tại.',
        ];
    }
}