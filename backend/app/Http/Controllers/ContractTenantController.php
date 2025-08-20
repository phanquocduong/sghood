<?php
namespace App\Http\Controllers;

use App\Services\ContractTenantService;
use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class ContractTenantController extends Controller
{
    protected $contractTenantService;

    public function __construct(ContractTenantService $contractTenantService)
    {
        $this->contractTenantService = $contractTenantService;
    }

    public function index(Request $request)
    {
        $rooms = Room::orderBy('name')->get();

        $contractTenants = $this->contractTenantService->getAllContractTenants(
            $request->get('querySearch', '') ?? '',
            $request->get('status', '') ?? '',
            $request->get('sort', 'desc') ?? 'desc',
            $request->get('room_id', '') ?? ''
        );

        if (isset($contractTenants['error'])) {
            return redirect()->route('contracts.index')->with('error', $contractTenants['error']);
        }

        $room_id = $request->get('room_id', '') ?? '';
        $primary = null;
        if ($room_id) {
            $contract = \App\Models\Contract::where('room_id', $room_id)->first();
            if ($contract) {
                $primary = $contract->user;
            }
        }

        return view('contracts.contract-tenants', [
            'contractTenants' => $contractTenants['data'],
            'rooms' => $rooms,
            'room_id' => $room_id,
            'querySearch' => $request->get('querySearch', '') ?? '',
            'status' => $request->get('status', '') ?? '',
            'sort' => $request->get('sort', 'desc') ?? 'desc',
            'primary' => $primary,
        ]);
    }

    public function showTenant($id)
    {
        $result = $this->contractTenantService->getContractTenantById($id);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status'] ?? 500);
        }

        if (request()->ajax()) {
            return response()->json([
                'html' => view('contracts.partials.detail-modal', [
                    'contractTenant' => $result['data']
                ])->render()
            ]);
        }

        return view('contracts.show-tenant', [
            'contractTenant' => $result['data']
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Đã duyệt,Từ chối,Đang ở,Đã rời đi',
            'rejection_reason' => 'required_if:status,Từ chối|max:255',
        ]);

        $result = $this->contractTenantService->updateContractTenantStatus(
            $id,
            $request->input('status'),
            $request->input('rejection_reason')
        );

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        $message = 'Trạng thái người ở chung đã được cập nhật thành công!';
        if ($request->input('status') === 'Từ chối') {
            $message .= ' Lý do từ chối đã được lưu.';
        }

        return redirect()->back()->with('success', $message);
    }

    // Hiển thị hình ảnh căn cước công dân của người ở chung với cache để tối ưu hiệu suất
    public function showTenantIdentityDocument(Request $request, $tenantId, $imagePath)
    {
        try {
            // Cache key để tránh decrypt nhiều lần
            $cacheKey = "tenant_identity_doc_{$tenantId}_{$imagePath}";

            // Kiểm tra cache trước
            $cachedContent = Cache::get($cacheKey);
            if ($cachedContent) {
                Log::info('Đang phục vụ tài liệu nhận dạng người ở chung từ bộ nhớ đệm', [
                    'tenantId' => $tenantId,
                    'imagePath' => $imagePath
                ]);

                return Response::make($cachedContent)
                    ->header('Content-Type', 'image/webp')
                    ->header('Cache-Control', 'public, max-age=3600')
                    ->header('Expires', now()->addHour()->toRfc7231String());
            }

            Log::info('Cố gắng xuất trình giấy tờ tùy thân người ở chung', [
                'tenantId' => $tenantId,
                'imagePath' => $imagePath
            ]);

            $tenant = $this->contractTenantService->getContractTenantById($tenantId);
            if (isset($tenant['error'])) {
                Log::error('Contract tenant not found', ['error' => $tenant['error']]);
                abort(404, $tenant['error']);
            }

            $tenant = $tenant['data'];

            if (!$tenant->identity_document) {
                Log::error('Không tìm thấy giấy tờ tùy thân cho người ở chung');
                abort(404, 'Không tìm thấy hình ảnh căn cước công dân');
            }

            $imagePaths = explode('|', $tenant->identity_document);
            $fullImagePath = 'images/tenants/' . $imagePath . '.enc'; // Thêm .enc vì lưu trong DB có .enc

            if (!in_array($fullImagePath, $imagePaths)) {
                Log::error('Invalid tenant image path', [
                    'fullImagePath' => $fullImagePath,
                    'imagePaths' => $imagePaths
                ]);
                abort(404, 'Hình ảnh không hợp lệ');
            }

            // Kiểm tra file tồn tại trước khi đọc
            if (!Storage::disk('private')->exists($fullImagePath)) {
                Log::error('Không tìm thấy tệp giấy tờ tùy thân người ở chung', ['fullImagePath' => $fullImagePath]);
                abort(404, 'File hình ảnh không tồn tại');
            }

            // Đọc file từ disk private
            Log::info('Đọc tập tin được mã hóa từ đĩa riêng cho người ở chung', ['fullImagePath' => $fullImagePath]);
            $encryptedContent = Storage::disk('private')->get($fullImagePath);

            if (!$encryptedContent) {
                Log::error('Không đọc được nội dung được mã hóa người ở chung');
                abort(500, 'Không thể đọc file hình ảnh');
            }

            Log::info('Giải mã nội dung người ở chung', ['contentLength' => strlen($encryptedContent)]);
            $decryptedContent = decrypt($encryptedContent);

            // Cache decrypted content trong 1 giờ
            Cache::put($cacheKey, $decryptedContent, 3600);

            Log::info('Đã gửi thành công giấy tờ tùy thân người ở chung', [
                'tenantId' => $tenantId,
                'imagePath' => $imagePath,
                'contentSize' => strlen($decryptedContent)
            ]);

            return Response::make($decryptedContent)
                ->header('Content-Type', 'image/webp')
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('Expires', now()->addHour()->toRfc7231String())
                ->header('Last-Modified', now()->toRfc7231String());

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::error('Giải mã không thành công cho tài liệu nhận dạng người ở chung', [
                'tenant_id' => $tenantId,
                'image_path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            abort(500, 'Không thể giải mã hình ảnh căn cước công dân');
        } catch (\Throwable $e) {
            Log::error('Lỗi hiển thị giấy tờ tùy thân người ở chung: ' . $e->getMessage(), [
                'tenant_id' => $tenantId,
                'image_path' => $imagePath,
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Đã xảy ra lỗi khi hiển thị hình ảnh căn cước công dân');
        }
    }

    public function deleteTenantIdentity(Request $request, $id)
    {
        try {
            $result = $this->contractTenantService->deleteTenantIdentityDocument($id);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            }

            return redirect()->back()->with('error', $result['message']);
        } catch (\Throwable $e) {
            Log::error('Error deleting tenant identity document: ' . $e->getMessage(), [
                'tenant_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi xóa thông tin căn cước công dân.');
        }
    }
}
