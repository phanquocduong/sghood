<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

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
            'readings' => 'required|array|min:1',
            'readings.*.room_id' => 'required|exists:rooms,id',
            'readings.*.electricity_kwh' => 'required|numeric|min:1',
            'readings.*.water_m3' => 'required|numeric|min:1',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'motel_id' => 'required|exists:motels,id',
            'motel_name' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'readings.required' => 'Danh sách chỉ số không được để trống.',
            'readings.array' => 'Danh sách chỉ số phải là một mảng.',
            'readings.min' => 'Phải có ít nhất một chỉ số được cung cấp.',
            'readings.*.room_id.required' => 'Vui lòng chọn phòng cho chỉ số :index.',
            'readings.*.room_id.exists' => 'Phòng ID :input không tồn tại.',
            'readings.*.electricity_kwh.required' => 'Vui lòng nhập chỉ số điện.',
            'readings.*.electricity_kwh.numeric' => 'Chỉ số điện phải là số.',
            'readings.*.electricity_kwh.min' => 'Chỉ số điện phải lớn hơn 0.',
            'readings.*.electricity_kwh.max' => 'Chỉ số điện không được vượt quá 2000.',
            'readings.*.water_m3.required' => 'Vui lòng nhập chỉ số nước.',
            'readings.*.water_m3.numeric' => 'Chỉ số nước phải là số.',
            'readings.*.water_m3.min' => 'Chỉ số nước phải lớn hơn 0.',
            'readings.*.water_m3.max' => 'Chỉ số nước không được vượt quá 200.',
            'month.required' => 'Vui lòng chọn tháng.',
            'month.integer' => 'Tháng phải là số nguyên.',
            'month.min' => 'Tháng phải từ 1 đến 12.',
            'month.max' => 'Tháng phải từ 1 đến 12.',
            'year.required' => 'Vui lòng chọn năm.',
            'year.integer' => 'Năm phải là số nguyên.',
            'year.min' => 'Năm phải từ 2000 trở lên.',
            'year.max' => 'Năm không được vượt quá 2100.',
            'motel_id.required' => 'Vui lòng chọn nhà trọ.',
            'motel_id.exists' => 'Nhà trọ không tồn tại.',
            'motel_name.string' => 'Tên nhà trọ phải là chuỗi ký tự.',
            'motel_name.max' => 'Tên nhà trọ không được vượt quá 255 ký tự.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $request = $this->all();

        // Log input data for debugging
        Log::debug('MeterReadingRequest Input:', $request);

        $readings = $request['readings'] ?? [];
        $month = $request['month'] ?? now()->month;
        $year = $request['year'] ?? now()->year;
        $motel_id = $request['motel_id'] ?? null;
        $motel_name = $request['motel_name'] ?? 'Unknown';

        // Construct rooms array for motel_data
        $rooms = collect($readings)->map(function ($reading, $index) {
            $room = \App\Models\Room::find($reading['room_id'] ?? null);
            return [
                'id' => $reading['room_id'] ?? null,
                'name' => optional($room)->name ?? 'Phòng không xác định',
                'electricity_kwh' => $reading['electricity_kwh'] ?? null,
                'water_m3' => $reading['water_m3'] ?? null,
            ];
        })->toArray();

        // Log constructed motel_data for debugging
        Log::debug('MeterReadingRequest motel_data:', [
            'motel_id' => $motel_id,
            'motel_name' => $motel_name,
            'month' => $month,
            'year' => $year,
            'rooms' => $rooms,
        ]);

        // Log validation errors
        Log::debug('MeterReadingRequest Validation Errors:', $validator->errors()->toArray());

        throw new HttpResponseException(
            redirect()
                ->route('meter_readings.index')
                ->withErrors($validator)
                ->withInput()
                ->with('motel_data', [
                    'motel_id' => $motel_id,
                    'motel_name' => $motel_name,
                    'month' => $month,
                    'year' => $year,
                    'rooms' => $rooms,
                ])
                ->with('open_update_modal', true)
        );
    }
}