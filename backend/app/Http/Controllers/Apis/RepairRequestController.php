<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\StoreRepairRequest;
use App\Services\Apis\RepairRequestService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RepairRequestController extends Controller
{
    protected $repairRequestService;

    public function __construct(RepairRequestService $repairRequestService)
    {
        $this->repairRequestService = $repairRequestService;
    }

    /**
     * Lấy tất cả repair requests của user
     */
    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $repairRequests = $this->repairRequestService->getUserRepairRequests($userId);

            $repairRequests->transform(function ($request) {
                if ($request->images) {
                    $request->images = explode('|', $request->images);
                }
                return $request;
            });

            return response()->json([
                'status' => 'success',
                'data' => $repairRequests
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy danh sách yêu cầu sửa chữa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo mới repair request
     */
    public function store(StoreRepairRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $repairRequest = $this->repairRequestService->createRepairRequest(
                $userId,
                $request->validated()
            );

            if ($repairRequest->images) {
                $repairRequest->images = explode('|', $repairRequest->images);
            }

            return response()->json([
                'status' => 'success',
                'data' => $repairRequest,
                'message' => 'Tạo yêu cầu sửa chữa thành công'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy hợp đồng đang hoạt động'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể tạo yêu cầu sửa chữa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hủy yêu cầu sửa chữa
     */
    public function cancel(int $repairRequestId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $repairRequest = $this->repairRequestService->cancelRepairRequest($userId, $repairRequestId);

            if ($repairRequest->images) {
                $repairRequest->images = explode('|', $repairRequest->images);
            }

            return response()->json([
                'status' => 'success',
                'data' => $repairRequest,
                'message' => 'Hủy yêu cầu sửa chữa thành công'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy yêu cầu sửa chữa hoặc hợp đồng'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể hủy yêu cầu sửa chữa: ' . $e->getMessage()
            ], 500);
        }
    }
}
