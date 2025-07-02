<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MeterReadingRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'room_id' => 'required|exists:rooms,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'electricity_kwh' => 'required|numeric|min:1|max:2000',
            'water_m3' => 'required|numeric|min:1|max:200',
        ];
    }

    public function messages()
    {
        return [
            'room_id.required' => 'Vui lòng chọn phòng.',
            'room_id.exists' => 'Phòng không tồn tại.',
            'month.required' => 'Vui lòng nhập tháng.',
            'month.integer' => 'Tháng phải là một số nguyên.',
            'month.min' => 'Tháng không được nhỏ hơn 1.',
            'month.max' => 'Tháng không được lớn hơn 12.',
            'year.required' => 'Vui lòng nhập năm.',
            'year.integer' => 'Năm phải là một số nguyên.',
            'year.min' => 'Năm không được nhỏ hơn 2000.',
            'year.max' => 'Năm không được lớn hơn 2100.',
            'electricity_kwh.required' => 'Vui lòng nhập chỉ số điện.',
            'electricity_kwh.numeric' => 'Chỉ số điện phải là một số.',
            'electricity_kwh.max' => 'Chỉ số điện không được lớn hơn 2000.',
            'electricity_kwh.min' => 'Chỉ số điện không được nhỏ hơn 1.',
            'water_m3.required' => 'Vui lòng nhập chỉ số nước.',
            'water_m3.numeric' => 'Chỉ số nước phải là một số.',
            'water_m3.max' => 'Chỉ số nước không được lớn hơn 200.',
            'water_m3.min' => 'Chỉ số nước không được nhỏ hơn 1.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('open_update_modal', true)
        );
    }

}