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
            'address' => $isUpdate ? 'sometimes|required|string|max:100' : 'required|string|max:100',
            'district_id' => $isUpdate ? 'sometimes|required|integer' : 'required|integer',
            'map_embed_url' => $isUpdate ? 'sometimes|required|string|max:1000' : 'required|string|max:1000',
            'description' => $isUpdate ? 'sometimes|required|string' : 'required|string',
            'status' => $isUpdate ? 'sometimes|required|in:Hoạt động,Không hoạt động' : 'required|in:Hoạt động,Không hoạt động',
        ];
    }
    public function messages()
    {
        return [
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'address.string' => 'Địa chỉ phải là một chuỗi.',
            'address.max' => 'Địa chỉ không được vượt quá 100 ký tự.',
            'district_id.required' => 'Vui lòng chọn quận/huyện.',
            'district_id.integer' => 'ID quận/huyện phải là một số nguyên.',
            'map_embed_url.required' => 'Vui lòng nhập URL nhúng bản đồ.',
            'map_embed_url.string' => 'URL nhúng bản đồ phải là một chuỗi.',
            'map_embed_url.max' => 'URL nhúng bản đồ không được vượt quá 1000 ký tự.',
            'description.required' => 'Vui lòng nhập mô tả.',
            'description.string' => 'Mô tả phải là một chuỗi.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ. Chọn "Hoạt động" hoặc "Không hoạt động".',
        ];
    }
}
