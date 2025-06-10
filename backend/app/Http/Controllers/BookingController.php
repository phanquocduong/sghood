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
        $booking = $this->bookingService->getAllBooking(
            (string) $request->get('querySearch', ''),
            (string) $request->get('status', ''),
            (int) $request->get('perPage', 10)
        );

        if (isset($booking['error'])) {
            return redirect()->route('bookings.index')->with('error', $booking['error']);
        }

        return view('bookings.index', [
            'booking' => $booking['data'],
            'querySearch' => $request->get('querySearch', ''),
            'status' => $request->get('status', ''),
            'perPage' => $request->get('perPage', 10),
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

        // Update status (and note if provided)
        $result = $this->bookingService->updateBookingStatus($id, $status, $note);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        $message = match($status) {
            'Chấp nhận' => 'Đã chấp nhận đặt phòng thành công!',
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

        // Update note
        $result = $this->bookingService->updateBookingNote($id, $note);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        // If status is provided, also update status
        if ($status) {
            $statusResult = $this->bookingService->updateBookingStatus($id, $status);

            if (isset($statusResult['error'])) {
                return redirect()->back()->with('error', 'Lý do đã được lưu nhưng không thể cập nhật trạng thái.');
            }
        }

        return redirect()->back()->with('success', 'Lý do và trạng thái đã được cập nhật thành công!');
    }
}
