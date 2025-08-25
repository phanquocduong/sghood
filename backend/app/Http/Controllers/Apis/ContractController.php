<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\UpdateContractRequest;
use App\Models\Contract;
use App\Services\Apis\ContractService;
use App\Services\Apis\InvoiceService;
use App\Services\Apis\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controller xử lý các yêu cầu API liên quan đến quản lý hợp đồng.
 */
class ContractController extends Controller
{
    /**
     * Khởi tạo controller với các dịch vụ liên quan.
     *
     * @param ContractService $contractService Dịch vụ xử lý logic hợp đồng
     * @param UserService $userService Dịch vụ xử lý thông tin người dùng
     * @param InvoiceService $invoiceService Dịch vụ xử lý hóa đơn
     */
    public function __construct(
        private readonly ContractService $contractService,
        private readonly UserService $userService,
        private readonly InvoiceService $invoiceService,
    ) {}

    /**
     * Lấy danh sách hợp đồng của người dùng hiện tại.
     *
     * @return JsonResponse Phản hồi JSON chứa danh sách hợp đồng
     */
    public function index(): JsonResponse
    {
        try {
            // Gọi dịch vụ để lấy danh sách hợp đồng của người dùng
            return response()->json([
                'data' => $this->contractService->getUserContracts(),
            ]);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi lấy danh sách hợp đồng:' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi lấy danh sách hợp đồng'], 500);
        }
    }

    /**
     * Lấy chi tiết hợp đồng theo ID.
     *
     * @param int $id ID của hợp đồng
     * @return JsonResponse Phản hồi JSON chứa chi tiết hợp đồng hoặc thông báo lỗi
     */
    public function show(int $id): JsonResponse
    {
        try {
            // Gọi dịch vụ để lấy chi tiết hợp đồng
            $contract = $this->contractService->getContractDetail($id);

            // Kiểm tra nếu có lỗi trong kết quả
            if (isset($contract['error'])) {
                return response()->json([
                    'error' => $contract['error'],
                    'status' => $contract['status'],
                ], $contract['status']);
            }

            // Trả về phản hồi JSON với dữ liệu hợp đồng
            return response()->json(['data' => $contract]);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi lấy chi tiết hợp đồng:' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi lấy chi tiết hợp đồng'], 500);
        }
    }

    /**
     * Hủy hợp đồng theo ID.
     *
     * @param int $id ID của hợp đồng
     * @return JsonResponse Phản hồi JSON với thông báo thành công hoặc lỗi
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            // Gọi dịch vụ để hủy hợp đồng
            $result = $this->contractService->cancelContract($id);

            // Kiểm tra nếu có lỗi trong kết quả
            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            // Trả về phản hồi JSON với thông báo thành công
            return response()->json(['message' => 'Hủy hợp đồng thành công'], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi hủy hợp đồng:' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi hủy hợp đồng'], 500);
        }
    }

    /**
     * Cập nhật nội dung hợp đồng.
     *
     * @param UpdateContractRequest $request Yêu cầu chứa dữ liệu đã xác thực
     * @param int $id ID của hợp đồng
     * @return JsonResponse Phản hồi JSON với thông tin hợp đồng đã cập nhật
     */
    public function update(UpdateContractRequest $request, int $id): JsonResponse
    {
        try {
            // Tìm hợp đồng của người dùng hiện tại
            $contract = Contract::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
            // Gọi dịch vụ để lưu nội dung hợp đồng
            $updatedContract = $this->contractService->saveContract($request->input('contract_content'), $id);

            // Lưu giấy tờ tùy thân nếu hợp đồng ở trạng thái chờ xác nhận và có hình ảnh
            if ($contract->status === 'Chờ xác nhận' && $request->hasFile('identity_images')) {
                $this->userService->saveIdentityDocument(
                    Auth::user(),
                    $request->file('identity_images')
                );
            }

            // Trả về phản hồi JSON với thông báo thành công và dữ liệu hợp đồng
            return response()->json([
                'message' => 'Hợp đồng đã được lưu và đang chờ duyệt',
                'data' => $updatedContract,
            ]);
        } catch (ModelNotFoundException $e) {
            // Trả về lỗi 404 nếu không tìm thấy hợp đồng
            return response()->json(['error' => 'Hợp đồng không tồn tại hoặc bạn không có quyền truy cập.'], 404);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi cập nhật hợp đồng:' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi cập nhật hợp đồng.'], 500);
        }
    }

    /**
     * Ký hợp đồng theo ID.
     *
     * @param Request $request Yêu cầu chứa chữ ký và nội dung hợp đồng
     * @param int $id ID của hợp đồng
     * @return JsonResponse Phản hồi JSON với thông tin hợp đồng đã ký và mã hóa đơn
     */
    public function sign(Request $request, int $id): JsonResponse
    {
        try {
            // Tìm hợp đồng ở trạng thái chờ ký của người dùng hiện tại
            $contract = Contract::where('user_id', Auth::id())
                ->where('id', $id)
                ->where('status', 'Chờ ký')
                ->firstOrFail();

            // Gọi dịch vụ để ký hợp đồng
            $updatedContract = $this->contractService->signContract(
                $id,
                $request->input('signature'),
                $request->input('content')
            );

            // Tạo hóa đơn tiền cọc
            $invoice = $this->invoiceService->createDepositInvoice($contract);

            // Trả về phản hồi JSON với thông báo thành công, dữ liệu hợp đồng và mã hóa đơn
            return response()->json([
                'message' => 'Hợp đồng đã được ký thành công. Vui lòng thanh toán tiền cọc.',
                'data' => $updatedContract,
                'invoice_code' => $invoice->code
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Trả về lỗi 404 nếu không tìm thấy hợp đồng hoặc không ở trạng thái chờ ký
            return response()->json(['error' => 'Hợp đồng không tồn tại hoặc không ở trạng thái chờ ký.'], 404);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi ký hợp đồng:' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi ký hợp đồng. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Tải file PDF của hợp đồng.
     *
     * @param int $id ID của hợp đồng
     * @return JsonResponse Phản hồi JSON với URL của file PDF
     */
    public function downloadPdf(int $id): JsonResponse
    {
        try {
            // Tìm hợp đồng của người dùng hiện tại
            $contract = Contract::where('user_id', Auth::id())->where('id', $id)->firstOrFail();

            // Kiểm tra trạng thái hợp đồng
            if ($contract->status !== 'Hoạt động') {
                return response()->json(['error' => 'Hợp đồng chưa thể tải PDF.'], 400);
            }

            // Tạo và lưu file PDF nếu chưa tồn tại
            if (!$contract->file) {
                $this->contractService->generateAndSaveContractPdf($id);
                $contract->refresh();
            }

            // Tạo URL để tải file PDF
            $fileUrl = url('/contract/pdf/' . $id);

            // Trả về phản hồi JSON với URL file PDF
            return response()->json(['data' => ['file_url' => $fileUrl]]);
        } catch (ModelNotFoundException $e) {
            // Trả về lỗi 404 nếu không tìm thấy hợp đồng
            return response()->json(['error' => 'Hợp đồng không tồn tại hoặc bạn không có quyền truy cập.'], 404);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi tải PDF hợp đồng:' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi tải PDF.'], 500);
        }
    }

    /**
     * Kết thúc hợp đồng sớm.
     *
     * @param int $id ID của hợp đồng
     * @return JsonResponse Phản hồi JSON với thông báo thành công hoặc lỗi
     */
    public function earlyTermination(int $id): JsonResponse
    {
        try {
            // Gọi dịch vụ để kết thúc hợp đồng sớm
            $result = $this->contractService->earlyTermination($id);

            // Kiểm tra nếu có lỗi trong kết quả
            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            // Trả về phản hồi JSON với thông báo thành công
            return response()->json(['message' => 'Hợp đồng của bạn đã được kết thúc sớm.'], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi kết thúc hợp đồng sớm:' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi gửi yêu cầu kết thúc hợp đồng sớm.'], 500);
        }
    }
}
