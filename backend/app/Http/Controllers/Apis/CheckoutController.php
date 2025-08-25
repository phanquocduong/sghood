<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\ReturnRequest;
use App\Services\Apis\CheckoutService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controller xử lý các yêu cầu API liên quan đến trả phòng.
 */
class CheckoutController extends Controller
{
    protected $checkoutService;

    /**
     * Khởi tạo controller với CheckoutService.
     *
     * @param CheckoutService $checkoutService Dịch vụ xử lý logic trả phòng
     */
    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * Gửi yêu cầu trả phòng cho hợp đồng.
     *
     * @param ReturnRequest $request Yêu cầu chứa dữ liệu đã xác thực
     * @param int $id ID của hợp đồng
     * @return JsonResponse Phản hồi JSON với thông tin yêu cầu trả phòng
     */
    public function requestReturn(ReturnRequest $request, int $id): JsonResponse
    {
        try {
            // Lấy dữ liệu đã xác thực từ request
            $validated = $request->validated();

            // Chuẩn bị thông tin ngân hàng nếu không hoàn tiền bằng tiền mặt
            $bankInfo = null;
            if (!$validated['is_cash_refunded'] && isset($validated['bank_name'], $validated['account_number'], $validated['account_holder'])) {
                $bankInfo = [
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'],
                    'account_holder' => $validated['account_holder'],
                ];
            }

            // Chuyển đổi ngày trả phòng sang định dạng Y-m-d
            $check_out_date = \DateTime::createFromFormat('d/m/Y', $validated['check_out_date'])->format('Y-m-d');

            // Gọi dịch vụ để xử lý yêu cầu trả phòng
            $result = $this->checkoutService->requestReturn($id, $bankInfo, $check_out_date);

            // Kiểm tra nếu có lỗi trong kết quả
            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            // Trả về phản hồi JSON với thông báo thành công và dữ liệu
            return response()->json([
                'message' => 'Yêu cầu trả phòng đã được gửi.',
                'data' => $result['data'],
            ], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi yêu cầu trả phòng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi gửi yêu cầu trả phòng.'], 500);
        }
    }

    /**
     * Lấy danh sách yêu cầu trả phòng của người dùng.
     *
     * @return JsonResponse Phản hồi JSON chứa danh sách yêu cầu trả phòng
     */
    public function index()
    {
        try {
            // Gọi dịch vụ để lấy danh sách yêu cầu trả phòng
            $checkouts = $this->checkoutService->getCheckouts();
            // Trả về phản hồi JSON với danh sách yêu cầu
            return response()->json([
                'data' => $checkouts
            ], 200);
        } catch (\Exception $e) {
            // Trả về lỗi nếu có ngoại lệ xảy ra
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi lấy danh sách yêu cầu trả phòng.'
            ], 500);
        }
    }

    /**
     * Hủy yêu cầu trả phòng theo ID.
     *
     * @param int $id ID của yêu cầu trả phòng
     * @return JsonResponse Phản hồi JSON với thông tin yêu cầu đã hủy
     */
    public function cancel($id)
    {
        try {
            // Gọi dịch vụ để hủy yêu cầu trả phòng
            $checkout = $this->checkoutService->cancelCheckout($id);
            // Trả về phản hồi JSON với thông báo thành công và dữ liệu
            return response()->json([
                'message' => 'Hủy yêu cầu trả phòng thành công',
                'data' => $checkout
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Trả về lỗi 404 nếu không tìm thấy yêu cầu trả phòng
            return response()->json([
                'error' => 'Không tìm thấy yêu cầu trả phòng.'
            ], 404);
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ hệ thống
            Log::error('Lỗi huỷ bỏ trả phòng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi hủy yêu cầu trả phòng.'], 500);
        }
    }

    /**
     * Xác nhận hoặc từ chối kết quả kiểm kê trả phòng.
     *
     * @param int $id ID của yêu cầu trả phòng
     * @param Request $request Yêu cầu chứa dữ liệu xác thực
     * @return JsonResponse Phản hồi JSON với thông tin yêu cầu đã xác nhận
     */
    public function confirm(int $id, Request $request): JsonResponse
    {
        try {
            // Xác thực dữ liệu đầu vào
            $validated = $request->validate([
                'status' => 'required|in:Đồng ý,Từ chối', // Trạng thái xác nhận
                'user_rejection_reason' => 'required_if:status,Từ chối|string|max:1000|nullable', // Lý do từ chối
            ]);

            // Gọi dịch vụ để xác nhận hoặc từ chối kiểm kê
            $checkout = $this->checkoutService->confirmCheckout($id, $validated['status'], $validated['user_rejection_reason'] ?? null);

            // Trả về phản hồi JSON với thông báo tương ứng
            return response()->json([
                'message' => $validated['status'] === 'Đồng ý' ? 'Xác nhận kiểm kê thành công' : 'Từ chối kiểm kê thành công',
                'data' => $checkout
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Trả về lỗi 404 nếu không tìm thấy yêu cầu trả phòng
            return response()->json([
                'error' => 'Không tìm thấy yêu cầu trả phòng.'
            ], 404);
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ hệ thống
            Log::error('Lỗi xác nhận kiểm kê', [
                'checkout_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi xác nhận kiểm kê.'
            ], 500);
        }
    }

    /**
     * Xác nhận người dùng đã rời phòng.
     *
     * @param int $id ID của yêu cầu trả phòng
     * @return JsonResponse Phản hồi JSON với thông tin yêu cầu đã xác nhận
     */
    public function leftRoom(int $id): JsonResponse
    {
        try {
            // Gọi dịch vụ để xác nhận người dùng đã rời phòng
            $checkout = $this->checkoutService->confirmLeftRoom($id);
            // Trả về phản hồi JSON với thông báo thành công và dữ liệu
            return response()->json([
                'message' => 'Xác nhận đã rời phòng thành công',
                'data' => $checkout
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Trả về lỗi 404 nếu không tìm thấy yêu cầu trả phòng
            return response()->json([
                'error' => 'Không tìm thấy yêu cầu trả phòng.'
            ], 404);
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ hệ thống
            Log::error('Lỗi xác nhận rời phòng:', [
                'checkout_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi xác nhận rời phòng.'
            ], 500);
        }
    }

    /**
     * Cập nhật thông tin ngân hàng cho yêu cầu trả phòng.
     *
     * @param int $id ID của yêu cầu trả phòng
     * @param Request $request Yêu cầu chứa thông tin ngân hàng
     * @return JsonResponse Phản hồi JSON với thông tin cập nhật
     */
    public function updateBank(int $id, Request $request): JsonResponse
    {
        try {
            // Xác thực dữ liệu đầu vào
            $validated = $request->validate([
                'bank_info' => 'required_if:is_cash_refunded,false|array|nullable', // Thông tin ngân hàng
                'bank_info.bank_name' => 'required_if:is_cash_refunded,false|string|max:255', // Tên ngân hàng
                'bank_info.account_number' => 'required_if:is_cash_refunded,false|string|max:50', // Số tài khoản
                'bank_info.account_holder' => 'required_if:is_cash_refunded,false|string|max:255', // Tên chủ tài khoản
            ]);

            // Gọi dịch vụ để cập nhật thông tin ngân hàng
            $result = $this->checkoutService->updateBankInfo($id, $validated['bank_info']);

            // Kiểm tra nếu có lỗi trong kết quả
            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            // Trả về phản hồi JSON với thông báo thành công và dữ liệu
            return response()->json([
                'message' => 'Cập nhật thông tin hoàn tiền thành công',
                'data' => $result['data'],
            ], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ hệ thống
            Log::error('Lỗi chỉnh sửa thông tin hoàn tiền', [
                'checkout_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Đã xảy ra lỗi khi chỉnh sửa thông tin hoàn tiền.'], 500);
        }
    }
}
