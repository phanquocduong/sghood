<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\StoreRepairRequest;
use App\Services\Apis\RepairRequestService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Controller xử lý các yêu cầu API liên quan đến yêu cầu sửa chữa.
 */
class RepairRequestController extends Controller
{
    /**
     * @var RepairRequestService Dịch vụ xử lý logic yêu cầu sửa chữa
     */
    protected $repairRequestService;

    /**
     * Khởi tạo controller với dịch vụ quản lý yêu cầu sửa chữa.
     *
     * @param RepairRequestService $repairRequestService Dịch vụ xử lý logic yêu cầu sửa chữa
     */
    public function __construct(RepairRequestService $repairRequestService)
    {
        $this->repairRequestService = $repairRequestService;
    }

    /**
     * Lấy danh sách tất cả yêu cầu sửa chữa của người dùng.
     *
     * @return JsonResponse Phản hồi JSON chứa danh sách yêu cầu sửa chữa
     */
    public function index(): JsonResponse
    {
        try {
            // Lấy ID người dùng đang đăng nhập
            $userId = Auth::id();
            // Gọi dịch vụ để lấy danh sách yêu cầu sửa chữa
            $repairRequests = $this->repairRequestService->getUserRepairRequests($userId);

            // Biến đổi dữ liệu: tách chuỗi ảnh thành mảng
            $repairRequests->transform(function ($request) {
                if ($request->images) {
                    $request->images = explode('|', $request->images);
                }
                return $request;
            });

            // Trả về phản hồi JSON với danh sách yêu cầu sửa chữa
            return response()->json([
                'status' => 'success',
                'data' => $repairRequests
            ], 200);
        } catch (\Exception $e) {
            // Trả về phản hồi JSON với thông báo lỗi nếu có ngoại lệ
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy danh sách yêu cầu sửa chữa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo mới một yêu cầu sửa chữa.
     *
     * @param StoreRepairRequest $request Yêu cầu chứa dữ liệu đã xác thực
     * @return JsonResponse Phản hồi JSON chứa thông tin yêu cầu sửa chữa vừa tạo
     */
    public function store(StoreRepairRequest $request): JsonResponse
    {
        try {
            // Lấy ID người dùng đang đăng nhập
            $userId = Auth::id();
            // Gọi dịch vụ để tạo yêu cầu sửa chữa
            $repairRequest = $this->repairRequestService->createRepairRequest(
                $userId,
                $request->validated()
            );

            // Tách chuỗi ảnh thành mảng nếu có
            if ($repairRequest->images) {
                $repairRequest->images = explode('|', $repairRequest->images);
            }

            // Trả về phản hồi JSON với thông tin yêu cầu sửa chữa và thông báo thành công
            return response()->json([
                'status' => 'success',
                'data' => $repairRequest,
                'message' => 'Tạo yêu cầu sửa chữa thành công'
            ], 201);
        } catch (ModelNotFoundException $e) {
            // Trả về lỗi nếu không tìm thấy hợp đồng đang hoạt động
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy hợp đồng đang hoạt động'
            ], 404);
        } catch (\Exception $e) {
            // Trả về phản hồi JSON với thông báo lỗi nếu có ngoại lệ
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể tạo yêu cầu sửa chữa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hủy một yêu cầu sửa chữa.
     *
     * @param int $repairRequestId ID của yêu cầu sửa chữa
     * @return JsonResponse Phản hồi JSON chứa thông tin yêu cầu sửa chữa đã hủy
     */
    public function cancel(int $repairRequestId): JsonResponse
    {
        try {
            // Lấy ID người dùng đang đăng nhập
            $userId = Auth::id();
            // Gọi dịch vụ để hủy yêu cầu sửa chữa
            $repairRequest = $this->repairRequestService->cancelRepairRequest($userId, $repairRequestId);

            // Tách chuỗi ảnh thành mảng nếu có
            if ($repairRequest->images) {
                $repairRequest->images = explode('|', $repairRequest->images);
            }

            // Trả về phản hồi JSON với thông tin yêu cầu sửa chữa và thông báo thành công
            return response()->json([
                'status' => 'success',
                'data' => $repairRequest,
                'message' => 'Hủy yêu cầu sửa chữa thành công'
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Trả về lỗi nếu không tìm thấy yêu cầu sửa chữa hoặc hợp đồng
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy yêu cầu sửa chữa hoặc hợp đồng'
            ], 404);
        } catch (\Exception $e) {
            // Trả về phản hồi JSON với thông báo lỗi nếu có ngoại lệ
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể hủy yêu cầu sửa chữa: ' . $e->getMessage()
            ], 500);
        }
    }
}
