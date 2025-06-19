<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\ContractService;
use App\Services\Apis\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContractController extends Controller
{
    protected $contractService;
    protected $userService;

    public function __construct(ContractService $contractService, UserService $userService)
    {
        $this->contractService = $contractService;
        $this->userService = $userService;
    }

    public function index()
    {
        try {
            $contracts = $this->contractService->getUserContracts();

            return response()->json([
                'data' => $contracts,
                'status' => 200,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching user contracts: ' . $e->getMessage(), [
                'user_id' => Auth::id() ?? null,
            ]);

            return response()->json([
                'error' => 'Đã xảy ra lỗi khi lấy danh sách hợp đồng',
                'status' => 500,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $contract = $this->contractService->getContractDetail($id);

            if (isset($contract['error'])) {
                return response()->json([
                    'error' => $contract['error'],
                    'status' => $contract['status'],
                ], $contract['status']);
            }

            return response()->json([
                'data' => $contract,
                'status' => 200,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching contract detail: ' . $e->getMessage(), [
                'contract_id' => $id,
                'user_id' => Auth::id() ?? null,
            ]);

            return response()->json([
                'error' => 'Đã xảy ra lỗi khi lấy chi tiết hợp đồng',
                'status' => 500,
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $result = $this->contractService->rejectContract($id);

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            return response()->json([
                'message' => 'Hủy hợp đồng thành công',
                'status' => 200,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error rejecting contract: ' . $e->getMessage(), [
                'contract_id' => $id,
                'user_id' => Auth::id() ?? null,
            ]);

            return response()->json([
                'error' => 'Đã xảy ra lỗi khi hủy hợp đồng',
                'status' => 500,
            ], 500);
        }
    }

    public function extractIdentityImages(Request $request)
    {
        try {
            $request->validate([
                'identity_images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $identityImages = $request->file('identity_images');
            $cccdData = $this->contractService->extractIdentityImages($identityImages);

            return response()->json([
                'status' => 'success',
                'data' => $cccdData,
                'message' => 'Trích xuất thông tin căn cước công dân thành công.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error extracting CCCD: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function save(Request $request)
    {
        try {
            $request->validate([
                'contract_content' => 'required|string',
                'identity_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $content = $request->input('contract_content');
            $identityImages = $request->file('identity_images');

            // Lưu hợp đồng
            $contract = $this->contractService->saveContract($content, 'Chờ duyệt');

            // Nếu có ảnh CCCD, lưu thông tin CCCD vào user
            if ($identityImages) {
                $user = Auth::user();
                $cccdData = $this->userService->extractAndSaveIdentityImages($user, $identityImages);
            }

            return response()->json([
                'message' => 'Hợp đồng đã được lưu và đang chờ duyệt',
                'data' => $contract,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error saving contract and identity images: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'status' => 422,
            ], 422);
        }
    }
}
