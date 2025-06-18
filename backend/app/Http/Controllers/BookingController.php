<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $querySearch = $request->query('query', '');
        $status = $request->query('status', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 10);

        $result = $this->bookingService->getAllBooking(
            $querySearch, $status, $sortOption, $perPage
        );

        if (isset($result['error'])) {
            return redirect()->route('bookings.index')->with('error', $result['error']);
        }

        return view('bookings.index', [
            'booking' => $result['data'],
            'querySearch' => $querySearch,
            'status' => $status,
            'sortOption' => $sortOption,
            'perPage' => $perPage,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Chờ xác nhận,Chấp nhận,Từ chối',
            'note' => 'nullable|string|max:500',
        ]);

        $status = $request->input('status');
        $note = $request->input('note');

        // Cập nhật trạng thái booking
        $result = $this->bookingService->updateBookingStatus($id, $status, $note);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        $message = match($status) {
            'Chấp nhận' => 'Đã chấp nhận đặt phòng thành công và gửi thông báo điền hợp đồng cho khách hàng!',
            'Từ chối' => 'Đã từ chối đặt phòng và gửi email thông báo cho khách hàng!',
            default => 'Trạng thái đã được cập nhật thành công!'
        };

        return redirect()->back()->with('success', $message);
    }

    public function updateNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|max:500',
            'status' => 'nullable|string|in:Chờ xác nhận,Chấp nhận,Từ chối',
        ]);

        $note = $request->input('note');
        $status = $request->input('status');

        // Cập nhật lý do hủy booking
        $result = $this->bookingService->updateBookingCancellation($id, $note);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        // Nếu có trạng thái, cập nhật trạng thái booking
        if ($status) {
            $statusResult = $this->bookingService->updateBookingStatus($id, $status);

            if (isset($statusResult['error'])) {
                return redirect()->back()->with('error', 'Lý do đã được lưu nhưng không thể cập nhật trạng thái.');
            }
        }

        return redirect()->back()->with('success', 'Lý do và trạng thái đã được cập nhật thành công!');
    }
}
