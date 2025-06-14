<?php
namespace App\Services;

use App\Models\Contract;
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

    // Lấy thông tin đặt phòng theo ID
    public function generateContractPreviewData($booking)
    {
        $currentDate = now()->format('d/m/Y');
        $startDate = $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('d/m/Y') : $currentDate;
        $endDate = $booking->end_date ? \Carbon\Carbon::parse($booking->end_date)->format('d/m/Y') : \Carbon\Carbon::parse($booking->start_date)->addYear()->format('d/m/Y');

        return [
            'current_date' => $currentDate,
            'current_day' => date('d'),
            'current_month' => date('m'),
            'current_year' => date('Y'),

            // Thông tin bên A (chủ nhà) - có thể lấy từ config hoặc database
            'landlord' => [
                'name' => 'SGHood',
                'identity_document' => '123456789',
                'birthdate' => '01/01/1990',
                'address' => '123 Đường ABC, TP Hồ Chí Minh',
                'phone' => '0901234567',
                'email' => 'sghood@gmail.com'
            ],

            // Thông tin bên B (người thuê)
            'tenant' => [
                'name' => $booking->user->name ?? '',
                'identity_document' => $booking->user->identity_document ?? '',
                'birthdate' => $booking->user->birthdate ? \Carbon\Carbon::parse($booking->user->birthdate)->format('d/m/Y') : '',
                'address' => $booking->user->address ?? '',
                'phone' => $booking->user->phone ?? '',
                'email' => $booking->user->email ?? ''
            ],

            // Thông tin phòng
            'room' => [
                'name' => $booking->room->name ?? '',
                'address' => $booking->room->address ?? '',
                'area' => $booking->room->area ?? ''
            ],

            // Thông tin hợp đồng
            'contract' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'rental_price' => $booking->room->price, '',
                'deposit_amount' => $booking->room->price, '',
                'payment_day' => '1-10',
                'additional_terms' => null,
                'signed_date' => null
            ],

            // Các phí
            'fees' => [
                'electricity_fee' => $booking->room->motel->electricity_fee ?? '',
                'water_fee' => $booking->room->motel->water_fee ?? '',
                'parking_fee' => $booking->room->motel->parking_fee ?? '',
                'junk_fee' => $booking->room->motel->junk_fee ?? '',
                'internet_fee' => $booking->room->motel->internet_fee ?? '',
                'service_fee' => $booking->room->motel->service_fee ?? ''
            ]
        ];
    }

    // Tạo nội dung hợp đồng dưới dạng HTML
    private function generateContractContent($booking, $contractData = null)
    {
        if (!$contractData) {
            $contractData = $this->generateContractPreviewData($booking);
        }


        $content = '
        <div class="container-fluid py-5 px-4">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header text-white bg-dark d-flex justify-content-center rounded-top-4">
                <h4 class="mb-0" style="color: #ffffff">HỢP ĐỒNG THUÊ PHÒNG TRỌ</h4>
            </div>
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <p class="fw-bold">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</p>
                <p class="fw-bold">Độc lập - Tự do - Hạnh phúc</p>
                <p class="mb-0">
                    <small>
                        <span class="text-primary">TP.HCM, ngày ' . $contractData['current_day'] . ' tháng ' . $contractData['current_month'] . ' năm ' . $contractData['current_year'] . '</span>
                    </small>
                </p>
            </div>

            <!-- BÊN A (CHỦ NHÀ) -->
            <h5 class="mt-4">BÊN CHO THUÊ PHÒNG TRỌ (BÊN A)</h5>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Họ và tên:</label>
                    <p class="border p-2 rounded bg-light">' . $contractData['landlord']['name'] . '</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">CMND/CCCD:</label>
                    <p class="border p-2 rounded bg-light">' . $contractData['landlord']['identity_document'] . '</p>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Ngày sinh:</label>
                    <p class="border p-2 rounded bg-light">' . $contractData['landlord']['birthdate'] . '</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Địa chỉ thường trú:</label>
                    <p class="border p-2 rounded bg-light">' . $contractData['landlord']['address'] . '</p>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Số điện thoại:</label>
                    <p class="border p-2 rounded bg-light">' . $contractData['landlord']['phone'] . '</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email:</label>
                    <p class="border p-2 rounded bg-light">' . $contractData['landlord']['email'] . '</p>
                </div>
            </div>

            <!-- BÊN B (NGƯỜI THUÊ) -->
            <h5 class="mt-4">BÊN THUÊ PHÒNG TRỌ (BÊN B)</h5>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Họ và tên:</label>
                    <input type="text" class="form-control" value="" name="name">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">CMND/CCCD:</label>
                    <input type="text" class="form-control" value="" name="identity_document">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Ngày sinh:</label>
                    <input type="text" class="form-control" value="" name="birthdate">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Địa chỉ thường trú:</label>
                    <input type="text" class="form-control" value="" name="address">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Ngày cấp:</label>
                    <input type="text" class="form-control" value="" name="date_of_issue">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nơi cấp:</label>
                    <input type="text" class="form-control" value="" name="address_of_issue">
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Số điện thoại:</label>
                    <p class="border p-2 rounded bg-light">' . $contractData['tenant']['phone'] . '</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email:</label>
                    <p class="border p-2 rounded bg-light">' . $contractData['tenant']['email'] . '</p>
                </div>
            </div>

            <!-- NỘI DUNG HỢP ĐỒNG -->
            <h5 class="mt-4">NỘI DUNG HỢP ĐỒNG</h5>
            <ol class="list-group list-group-numbered mb-4">
                <li class="list-group-item">
                    <strong>Nội dung thuê phòng trọ:</strong>
                    Bên A cho thuê Bên B phòng trọ số <span class="text-primary fw-bold">' . $contractData['room']['name'] . '</span>
                    tại địa chỉ <span class="text-primary fw-bold">' . $contractData['room']['address'] . '</span>.
                    Diện tích: <span class="text-primary fw-bold">' . $contractData['room']['area'] . '</span> m².
                </li>
                <li class="list-group-item">
                    <strong>Trách nhiệm bên A (Chủ nhà):</strong>
                    <ul class="mt-2">
                        <li>Cung cấp phòng trọ sạch sẽ, đầy đủ tiện nghi theo thỏa thuận.</li>
                        <li>Hỗ trợ bên B trong trường hợp có sự cố liên quan đến phòng trọ.</li>
                        <li>Đảm bảo an ninh, trật tự tại khu trọ.</li>
                        <li>Cung cấp hóa đơn điện, nước, internet đúng hạn.</li>
                    </ul>
                </li>
                <li class="list-group-item">
                    <strong>Trách nhiệm bên B (Người thuê):</strong>
                    <ul class="mt-2">
                        <li>Đảm bảo thanh toán đầy đủ tiền thuê phòng trọ đúng hạn.</li>
                        <li>Giữ gìn vệ sinh, không làm hư hỏng tài sản của bên A.</li>
                        <li>Chấp hành các quy định về an ninh, trật tự tại khu trọ.</li>
                        <li>Thông báo trước khi có khách hoặc thay đổi số người ở.</li>
                    </ul>
                </li>
                <li class="list-group-item">
                    <strong>Điều khoản thanh toán:</strong>
                    <ul class="mt-2">
                        <li>Đặt cọc: <span class="text-primary fw-bold">' . number_format($contractData['contract']['deposit_amount'], 0, ",", ".") . '</span> VNĐ.</li>
                        <li>Tiền thuê phòng: <span class="text-primary fw-bold">' . number_format($contractData['contract']['rental_price'], 0, ",", ".") . '</span> VNĐ/tháng,
                            thanh toán vào ngày <span class="text-primary fw-bold">' . $contractData['contract']['payment_day'] . '</span> hàng tháng.</li>
                        <li>Thời hạn hợp đồng: Từ ngày <span class="text-primary fw-bold">' . $contractData['contract']['start_date'] . '</span>
                            đến ngày <span class="text-primary fw-bold">' . $contractData['contract']['end_date'] . '</span>.</li>
                        <li>Phí dịch vụ (điện, nước, internet): Theo đơn giá thực tế.</li>
                    </ul>
                </li>
                <li class="list-group-item">
                    <strong>Các phí khác:</strong>
                        <ul class="mt-2">
                            <li>Tiền điện: <span class="text-primary">' . number_format($contractData['fees']['electricity_fee'], 0, ",", ".") . '</span>VNĐ/Kg</li>
                            <li>Tiền nước: <span class="text-primary">' . number_format($contractData['fees']['water_fee'], 0, ",", ".") . '</span>VNĐ/Khối</li>
                            <li>Tiền gửi xe: <span class="text-primary">' . number_format($contractData['fees']['parking_fee'], 0, ",", ".") . '</span> VNĐ/tháng</li>
                            <li>Tiền rác: <span class="text-primary">' . number_format($contractData['fees']['junk_fee'], 0, ",", ".") . '</span> VNĐ/tháng</li>
                            <li>Tiền internet: <span class="text-primary">' . number_format($contractData['fees']['internet_fee'], 0, ",", ".") . '</span> VNĐ/tháng</li>
                            <li>Phí dịch vụ khác (nếu có): <span class="text-primary">' . number_format($contractData['fees']['service_fee'], 0, ",", ".") . '</span> VNĐ/tháng</li>
                        </ul>
                    </li>
                <li class="list-group-item">
                    <strong>Điều khoản chấm dứt hợp đồng:</strong>
                    <ul class="mt-2">
                        <li>Hai bên có thể chấm dứt hợp đồng trước thời hạn bằng thông báo trước 30 ngày.</li>
                        <li>Bên vi phạm nghiêm trọng các điều khoản sẽ bị chấm dứt hợp đồng ngay lập tức.</li>
                        <li>Khi chấm dứt hợp đồng, bên B phải trả phòng trong tình trạng ban đầu.</li>
                    </ul>
                </li>
                <li class="list-group-item">
                    <strong>Điều khoản khác:</strong>
                    <div class="mt-2">
                        <p class="border p-2 rounded bg-light">' . ($contractData['contract']['additional_terms'] ?? 'Hai bên cam kết thực hiện đúng các điều khoản đã nêu trên. Mọi tranh chấp sẽ được giải quyết thông qua thương lượng hoặc cơ quan có thẩm quyền.') . '</p>
                    </div>
                </li>
            </ol>

            <!-- CHỮ KÝ XÁC NHẬN -->
            <h5 class="mt-4">CHỮ KÝ XÁC NHẬN</h5>
            <div class="row mb-4">
                <div class="col-md-6 text-center">
                    <p class="fw-bold">BÊN A (CHỦ NHÀ)</p>
                    <p class="text-muted">(Ký, ghi rõ họ tên)</p>
                    <div class="border p-4 rounded bg-light d-flex flex-column justify-content-center" style="min-height: 120px;">
                        <p class="fw-bold mb-1 fs-5">' . $contractData['landlord']['name'] . '</p>
                        <small class="text-muted">' . $contractData['current_date'] . '</small>
                    </div>
                </div>
                <div class="col-md-6 text-center">
                    <p class="fw-bold">BÊN B (NGƯỜI THUÊ)</p>
                    <p class="text-muted">(Ký, ghi rõ họ tên)</p>
                    <div class="border p-4 rounded bg-light d-flex flex-column justify-content-center" style="min-height: 120px;">
                        <p class="fw-bold mb-1 fs-5">' . $contractData['tenant']['name'] . '</p>
                        <small class="text-muted">' . ($contractData['contract']['signed_date'] ?? 'Chưa ký') . '</small>
                    </div>
                </div>
            </div>
        </div>
            </div>
    </div>
        ';
    //     </div>
    // </div>

        return $content;
    }

    // Lấy xem trước hợp đồng dựa trên ID đặt phòng
    public function getContractPreview($bookingId)
    {
        try {
            $booking = Booking::with(['user', 'room'])->findOrFail($bookingId);
            $contractData = $this->generateContractPreviewData($booking);

            return [
                'success' => true,
                'data' => [
                    'booking' => $booking,
                    'contract_data' => $contractData,
                    'preview_html' => $this->generateContractContent($booking, $contractData)
                ]
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ['error' => 'Không tìm thấy đặt phòng', 'status' => 404];
        } catch (\Throwable $e) {
            Log::error('Error generating contract preview: ' . $e->getMessage(), ['booking_id' => $bookingId]);
            return ['error' => 'Đã xảy ra lỗi khi tạo xem trước hợp đồng', 'status' => 500];
        }
    }

    public function updateBookingStatus($id, $status, $note = null)
    {
        try {
            $booking = Booking::with(['user', 'room'])->findOrFail($id);
            $oldStatus = $booking->status;

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
            $booking->refresh();

            if ($status === 'Chấp nhận' && $oldStatus !== 'Chấp nhận') {
                $contractPreviewData = $this->generateContractPreviewData($booking);

                $contractData = [
                    'booking_id' => $id,
                    'user_id' => $booking->user_id,
                    'room_id' => $booking->room_id,
                    'start_date' => $booking->start_date,
                    'end_date' => $booking->end_date,
                    'rental_price' => $booking->room->price ?? 0,
                    'deposit_amount' => $contractPreviewData['contract']['deposit_amount'],
                    'content' => $this->generateContractContent($booking, $contractPreviewData),
                    'created_at' => now(),
                    'status' => 'Chờ xác nhận',
                ];

                try {
                    $contract = Contract::create($contractData);
                    Log::info('Contract created successfully', ['contract_id' => $contract->id, 'booking_id' => $id]);
                } catch (\Throwable $e) {
                    Log::error('Failed to create contract: ' . $e->getMessage(), ['booking_id' => $id, 'contract_data' => $contractData]);
                    // Không dừng toàn bộ quá trình, chỉ ghi log lỗi
                }
            }

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

                if ($status === 'Chấp nhận' && $oldStatus !== 'Chấp nhận') {
                    $contractPreviewData = $this->generateContractPreviewData($booking);

                    $contractData = [
                        'booking_id' => $id,
                        'user_id' => $booking->user_id,
                        'room_id' => $booking->room_id,
                        'start_date' => $booking->start_date,
                        'end_date' => $booking->end_date,
                        'rental_price' => $contractPreviewData['contract']['rental_price'],
                        'deposit_amount' => $contractPreviewData['contract']['deposit_amount'],
                        'content' => $this->generateContractContent($booking, $contractPreviewData),
                        'created_at' => now(),
                        'status' => 'Chờ xác nhận',
                    ];

                    try {
                        $contract = Contract::create($contractData);
                        Log::info('Contract created successfully', ['contract_id' => $contract->id, 'booking_id' => $id]);
                    } catch (\Throwable $e) {
                        Log::error('Failed to create contract: ' . $e->getMessage(), ['booking_id' => $id, 'contract_data' => $contractData]);
                    }
                }

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
