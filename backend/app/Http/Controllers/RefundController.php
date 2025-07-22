<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RefundService;

class RefundController extends Controller
{
    protected $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    public function index(Request $request)
    {
        // Lấy danh sách yêu cầu hoàn tiền từ service
        $refunds = $this->refundService->getRefunds($request);

        // Lấy các tham số để truyền lại cho view
        $querySearch = $request->input('querySearch');
        $status = $request->input('status');
        $sort = $request->input('sort', 'desc');

        // Trả về view với dữ liệu
        return view('refunds.index', compact('refunds', 'querySearch', 'status', 'sort'));
    }

    public function confirm(Request $request, $id)
    {
        try {
            // Xác nhận yêu cầu hoàn tiền
            $refund = $this->refundService->confirmRefund($id, $request);

            return redirect()->route('refunds.index')->with('success', 'Yêu cầu hoàn tiền đã được xác nhận thành công.');
        } catch (\Exception $e) {
            return redirect()->route('refunds.index')->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
