<?php
namespace App\Services;

use App\Models\Booking;
use App\Mail\BookingRejected;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingService
{
    public function getAllBooking(string $querySearch = '', string $status = '', int $perPage = 10)
    {
        try {
            $query = Booking::with(['user', 'room']);

            if ($querySearch) {
                $query->where(function ($q) use ($querySearch) {
                    $q->where('note', 'like', "%$querySearch%")
                      ->orWhereHas('user', function($userQuery) use ($querySearch) {
                          $userQuery->where('name', 'like', "%$querySearch%");
                      });
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            $booking = $query->orderBy('created_at', 'desc')->paginate($perPage);
            return ['data' => $booking];
        } catch (\Throwable $e) {
            Log::error('Error getting bookings: ' . $e->getMessage(), [
                'query_search' => $querySearch,
                'status' => $status,
                'per_page' => $perPage
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách đặt phòng', 'status' => 500];
        }
    }

    public function updateBookingStatus($id, $status, $note = null)
    {
        try {
            $booking = Booking::with(['user', 'room'])->findOrFail($id);
            $oldStatus = $booking->status;

            // Log before update
            Log::info('Updating booking status', [
                'booking_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'note' => $note
            ]);

            $updateData = ['status' => $status];
            if ($note) {
                $updateData['note'] = $note;
            }

            $booking->update($updateData);

            // Reload to get fresh data
            $booking->refresh();

            // Send email if status changed to "Từ chối" and user has email
            if ($status === 'Từ chối' && $oldStatus !== 'Từ chối' && $booking->user && $booking->user->email) {
                try {
                    Mail::to($booking->user->email)->send(new BookingRejected($booking, $note ?? ''));
                    Log::info('Rejection email sent successfully', [
                        'booking_id' => $id,
                        'user_email' => $booking->user->email
                    ]);
                } catch (\Exception $mailException) {
                    Log::error('Failed to send rejection email: ' . $mailException->getMessage(), [
                        'booking_id' => $id,
                        'user_email' => $booking->user->email
                    ]);
                    // Don't fail the entire operation if email fails
                }
            }

            return ['data' => $booking];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Booking not found: ' . $e->getMessage(), ['booking_id' => $id]);
            return ['error' => 'Không tìm thấy đặt phòng', 'status' => 404];
        } catch (\Throwable $e) {
            Log::error('Error updating booking status: ' . $e->getMessage(), [
                'booking_id' => $id,
                'status' => $status
            ]);
            return ['error' => 'Đã xảy ra lỗi khi cập nhật trạng thái', 'status' => 500];
        }
    }

    public function updateBookingNote($id, $note)
    {
        try {
            $booking = Booking::findOrFail($id);

            // Log before update
            Log::info('Updating booking note', [
                'booking_id' => $id,
                'old_note' => $booking->note,
                'new_note' => $note
            ]);

            $booking->update(['note' => $note]);

            // Reload to get fresh data
            $booking->refresh();

            return ['data' => $booking];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Booking not found: ' . $e->getMessage(), ['booking_id' => $id]);
            return ['error' => 'Không tìm thấy đặt phòng', 'status' => 404];
        } catch (\Throwable $e) {
            Log::error('Error updating booking note: ' . $e->getMessage(), [
                'booking_id' => $id,
                'note' => $note
            ]);
            return ['error' => 'Đã xảy ra lỗi khi cập nhật lý do', 'status' => 500];
        }
    }

    public function updateBookingStatusAndNote($id, $status, $note)
    {
        try {
            return DB::transaction(function () use ($id, $status, $note) {
                $booking = Booking::with(['user', 'room'])->findOrFail($id);
                $oldStatus = $booking->status;

                Log::info('Updating booking status and note', [
                    'booking_id' => $id,
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'old_note' => $booking->note,
                    'new_note' => $note
                ]);

                $updateData = ['status' => $status];
                if ($note) {
                    $updateData['note'] = $note;
                }

                $booking->update($updateData);
                $booking->refresh();

                // Send email if status changed to "Từ chối" and user has email
                if ($status === 'Từ chối' && $oldStatus !== 'Từ chối' && $booking->user && $booking->user->email) {
                    try {
                        Mail::to($booking->user->email)->send(new BookingRejected($booking, $note ?? ''));
                        Log::info('Rejection email sent successfully', [
                            'booking_id' => $id,
                            'user_email' => $booking->user->email
                        ]);
                    } catch (\Exception $mailException) {
                        Log::error('Failed to send rejection email: ' . $mailException->getMessage(), [
                            'booking_id' => $id,
                            'user_email' => $booking->user->email
                        ]);
                        // Don't fail the entire operation if email fails
                    }
                }

                return ['data' => $booking];
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Booking not found: ' . $e->getMessage(), ['booking_id' => $id]);
            return ['error' => 'Không tìm thấy đặt phòng', 'status' => 404];
        } catch (\Throwable $e) {
            Log::error('Error updating booking status and note: ' . $e->getMessage(), [
                'booking_id' => $id,
                'status' => $status,
                'note' => $note
            ]);
            return ['error' => 'Đã xảy ra lỗi khi cập nhật thông tin đặt phòng', 'status' => 500];
        }
    }
}
