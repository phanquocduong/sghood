<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'config_key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('configs', 'config_key')->ignore($this->route('id')),
            ],
            'config_type' => 'required|in:TEXT,URL,HTML,JSON,IMAGE,BANK',
            'description' => 'nullable|string|max:255',
        ];

        switch ($this->input('config_type')) {
            case 'IMAGE':
                $rules['config_image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
                break;

            case 'JSON':
                $rules['config_json'] = 'required|array|min:1';
                // Cho phép cả string và array (từ BANK chuyển sang)
                $rules['config_json.*'] = 'required';
                break;
                
            case 'BANK':
                $rules['config_json'] = 'required|array|min:1';
                $rules['config_json.*.value'] = 'required|string|max:255';
                $rules['config_json.*.label'] = 'required|string|max:255';
                $rules['config_json.*.logo'] = 'nullable|url|max:500';
                break;
                
            default:
                $rules['config_value'] = 'required|string|max:1000';
                $rules['config_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
                break;
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $configType = $this->input('config_type');
        $configJson = $this->input('config_json', []);

        // Xử lý chuyển đổi từ BANK sang JSON
        if ($configType === 'JSON' && !empty($configJson)) {
            $convertedJson = [];
            
            foreach ($configJson as $item) {
                if (is_array($item) && isset($item['value'])) {
                    // Dữ liệu từ BANK format, chuyển đổi thành string
                    // Ưu tiên value, fallback về label nếu value rỗng
                    $value = !empty($item['value']) ? $item['value'] : ($item['label'] ?? '');
                    if (!empty(trim($value))) {
                        $convertedJson[] = trim($value);
                    }
                } else {
                    // Dữ liệu đã là string (JSON format)
                    if (!empty(trim($item))) {
                        $convertedJson[] = trim($item);
                    }
                }
            }
            
            $this->merge([
                'config_json' => array_values($convertedJson)
            ]);
        }
        // Xử lý BANK type
        elseif ($configType === 'BANK' && !empty($configJson)) {
            // Lọc bỏ các item rỗng
            $filteredJson = array_filter($configJson, function($item) {
                return is_array($item) && (!empty($item['value']) || !empty($item['label']));
            });
            
            $this->merge([
                'config_json' => array_values($filteredJson)
            ]);
        }
    }

    /**
     * Get custom error messages for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'config_key.required' => 'Khóa cấu hình là bắt buộc.',
            'config_key.string' => 'Khóa cấu hình phải là chuỗi ký tự.',
            'config_key.max' => 'Khóa cấu hình không được dài quá 255 ký tự.',
            'config_key.unique' => 'Khóa cấu hình đã tồn tại, vui lòng chọn khóa khác.',
            'config_value.required' => 'Giá trị cấu hình là bắt buộc.',
            'config_image.image' => 'Ảnh cấu hình phải là một tệp hình ảnh.',
            'config_image.mimes' => 'Ảnh cấu hình phải có định dạng: jpeg, png, jpg, gif.',
            'config_image.max' => 'Ảnh cấu hình không được lớn hơn 2MB.',
            'config_json.required' => 'Vui lòng thêm ít nhất một lựa chọn.',
            'config_json.array' => 'Dữ liệu phải là một mảng.',
            'config_json.min' => 'Vui lòng thêm ít nhất một lựa chọn.',
            'config_json.*.required' => 'Không được để trống lựa chọn.',
            
            // BANK validation messages
            'config_json.*.value.required' => 'Mã ngân hàng là bắt buộc.',
            'config_json.*.value.string' => 'Mã ngân hàng phải là chuỗi ký tự.',
            'config_json.*.value.max' => 'Mã ngân hàng không được dài quá 255 ký tự.',
            'config_json.*.label.required' => 'Tên ngân hàng là bắt buộc.',
            'config_json.*.label.string' => 'Tên ngân hàng phải là chuỗi ký tự.',
            'config_json.*.label.max' => 'Tên ngân hàng không được dài quá 255 ký tự.',
            'config_json.*.logo.url' => 'Logo URL phải là đường dẫn hợp lệ.',
            'config_json.*.logo.max' => 'Logo URL không được dài quá 500 ký tự.',
            
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'description.max' => 'Mô tả không được dài quá 255 ký tự.',
            'config_type.required' => 'Loại cấu hình là bắt buộc.',
            'config_type.in' => 'Loại cấu hình phải là một trong các giá trị: TEXT, URL, HTML, JSON, IMAGE, BANK.',
        ];
    }
}