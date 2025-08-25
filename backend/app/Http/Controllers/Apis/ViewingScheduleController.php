<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\ViewingScheduleService;
use App\Http\Requests\Apis\StoreScheduleRequest;
use App\Http\Requests\Apis\UpdateScheduleRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controller xử lý các yêu cầu API liên quan đến lịch xem nhà trọ.
 */
class ViewingScheduleController extends Controller
{
    /**
     * @var ViewingScheduleService Dịch vụ xử lý logic lịch xem nhà trọ
     */
    protected $viewingScheduleService;

    /**
     * Khởi tạo controller với dịch vụ quản lý lịch xem nhà trọ.
     *
     * @param ViewingScheduleService $viewingScheduleService Dịch vụ xử lý logic lịch xem
     */
    public function __construct(ViewingScheduleService $viewingScheduleService)
    {
        $this->viewingScheduleService = $viewingScheduleService;
    }

    /**
     * Lấy danh sách lịch xem nhà trọ của người dùng.
     *
     * @param Request $request Yêu cầu chứa các tham số lọc
     * @return JsonResponse Phản hồi JSON chứa danh sách lịch xem
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Lấy các bộ lọc từ yêu cầu (sắp xếp, trạng thái)
            $filters = $request->only(['sort', 'status']);
            // Gọi dịch vụ để lấy danh sách lịch xem
            $schedules = $this->viewingScheduleService->getSchedules($filters);
            // Trả về phản hồi JSON với danh sách lịch xem
            return response()->json(['data' => $schedules], 200);
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ
            Log::error('Đã có lỗi xảy ra khi lấy danh sách lịch xem nhà trọ: ' . $e->getMessage());
            // Trả về phản hồi JSON với thông báo lỗi
            return response()->json(['error' => 'Đã có lỗi xảy ra khi lấy danh sách lịch xem nhà trọ.'], 500);
        }
    }

    /**
     * Tạo mới lịch xem nhà trọ.
     *
     * @param StoreScheduleRequest $request Yêu cầu chứa dữ liệu đã xác thực
     * @return JsonResponse Phản hồi JSON chứa thông tin lịch xem vừa tạo
     */
    public function store(StoreScheduleRequest $request): JsonResponse
    {
        try {
            // Lấy dữ liệu đã xác thực và thêm ID người dùng
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();
            // Gọi dịch vụ để tạo lịch xem
            $schedule = $this->viewingScheduleService->createSchedule($validated);
            // Trả về phản hồi JSON với thông tin lịch xem và thông báo thành công
            return response()->json([
                'message' => 'Đặt lịch xem nhà trọ thành công',
                'data' => $schedule,
            ], 201);
        } catch (HttpResponseException $e) {
            // Ném lại ngoại lệ nếu có lỗi xác thực
            throw $e;
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ
            Log::error('Đã có lỗi xảy ra khi đặt lịch xem nhà trọ: ' . $e->getMessage());
            // Trả về phản hồi JSON với thông báo lỗi
            return response()->json(['error' => 'Đã có lỗi xảy ra khi đặt lịch xem nhà trọ.'], 500);
        }
    }

    /**
     * Cập nhật lịch xem nhà trọ.
     *
     * @param UpdateScheduleRequest $request Yêu cầu chứa dữ liệu đã xác thực
     * @param int $id ID của lịch xem
     * @return JsonResponse Phản hồi JSON chứa thông tin lịch xem đã cập nhật
     */
    public function update(UpdateScheduleRequest $request, $id): JsonResponse
    {
        try {
            // Gọi dịch vụ để cập nhật lịch xem
            $schedule = $this->viewingScheduleService->updateSchedule($id, $request->validated());
            // Trả về phản hồi JSON với thông tin lịch xem và thông báo thành công
            return response()->json([
                'message' => 'Cập nhật lịch xem nhà trọ thành công',
                'data' => $schedule,
            ]);
        } catch (ModelNotFoundException) {
            // Trả về lỗi nếu không tìm thấy lịch xem
            return response()->json(['error' => 'Không tìm thấy lịch xem nhà trọ.'], 404);
        } catch (HttpResponseException $e) {
            // Ném lại ngoại lệ nếu có lỗi xác thực
            throw $e;
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ
            Log::error('Đã có lỗi xảy ra khi cập nhật lịch xem nhà trọ: ' . $e->getMessage());
            // Trả về phản hồi JSON với thông báo lỗi
            return response()->json(['error' => 'Đã có lỗi xảy ra khi cập nhật lịch xem nhà trọ.'], 500);
        }
    }

    /**
     * Hủy lịch xem nhà trọ.
     *
     * @param int $id ID của lịch xem
     * @return JsonResponse Phản hồi JSON chứa thông tin lịch xem đã hủy
     */
    public function cancel($id): JsonResponse
    {
        try {
            // Gọi dịch vụ để hủy lịch xem
            $schedule = $this->viewingScheduleService->cancelSchedule($id);
            // Trả về phản hồi JSON với thông tin lịch xem và thông báo thành công
            return response()->json([
                'message' => 'Hủy lịch xem nhà trọ thành công',
                'data' => $schedule,
            ], 200);
        } catch (ModelNotFoundException) {
            // Trả về lỗi nếu không tìm thấy lịch xem
            return response()->json(['error' => 'Không tìm thấy lịch xem nhà trọ.'], 404);
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ
            Log::error('Đã có lỗi xảy ra khi hủy lịch xem nhà trọ: ' . $e->getMessage());
            // Trả về phản hồi JSON với thông báo lỗi
            return response()->json(['error' => 'Đã có lỗi xảy ra khi hủy lịch xem nhà trọ.'], 500);
        }
    }
}
