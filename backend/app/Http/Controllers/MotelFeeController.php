<?php
namespace App\Http\Controllers;

use App\Models\MotelFee;
use App\Http\Requests\MotelFeeRequest;
use App\Services\MotelFeeService;
use Illuminate\Http\Request;

class MotelFeeController extends Controller
{
    protected $motelFeeService;

    public function __construct(MotelFeeService $motelFeeService)
    {
        $this->motelFeeService = $motelFeeService;
    }

    public function index($motelId)
    {
        try {
            $fees = $this->motelFeeService->getAllByMotel($motelId);
            return response()->json($fees);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không tìm thấy nhà trọ'], 404);
        }
    }

    public function store(MotelFeeRequest $request)
    {
        try {
            $fee = $this->motelFeeService->create($request->validated());
            return response()->json([
                'message' => 'Thêm phí thành công.',
                'fee' => $fee,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể thêm phí'], 500);
        }
    }

    public function update(MotelFeeRequest $request, MotelFee $motelFee)
    {
        try {
            $fee = $this->motelFeeService->update($motelFee, $request->validated());
            return response()->json([
                'message' => 'Cập nhật phí thành công.',
                'fee' => $fee,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể cập nhật phí'], 500);
        }
    }

    public function destroy(MotelFee $motelFee)
    {
        try {
            $this->motelFeeService->delete($motelFee);
            return response()->json(['message' => 'Xóa phí thành công.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể xóa phí'], 500);
        }
    }
}
