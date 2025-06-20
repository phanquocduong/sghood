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
    public function getAllBooking(string $querySearch = '', string $status = '', string $sortOption = '', int $perPage = 10)
    {
        try {
            $query = Booking::with(['user', 'room']);

            // Áp dụng từng bộ lọc riêng biệt
            $this->applySearchFilter($query, $querySearch);
            $this->applyStatusFilter($query, $status);
            $this->applySorting($query, $sortOption);

            $bookings = $query->paginate($perPage);
            return ['data' => $bookings];
        } catch (\Throwable $e) {
            Log::error('Error getting bookings: ' . $e->getMessage(), [
                'query_search' => $querySearch,
                'status' => $status,
                'sort_option' => $sortOption,
                'per_page' => $perPage
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách đặt phòng', 'status' => 500];
        }
    }

    // Áp dụng bộ lọc tìm kiếm
    private function applySearchFilter($query, string $querySearch): void
    {
        if ($querySearch !== '') {
            $query->where(function ($q) use ($querySearch) {
                $q->whereHas('room', function($roomQuery) use ($querySearch) {
                        $roomQuery->where('name', 'LIKE', '%' . $querySearch . '%');
                    })
                ->orWhereHas('user', function($userQuery) use ($querySearch) {
                    $userQuery->where('name', 'LIKE', '%' . $querySearch . '%');
                });
            });
        }
    }

    // Áp dụng bộ lọc trạng thái
    private function applyStatusFilter($query, string $status): void
    {
        if (!empty($status)) {
            $query->where('status', $status);
        }
    }

    // Áp dụng sắp xếp cho truy vấn
    private function applySorting($query, string $sortOption): void
    {
        $sort = $this->handleSortOption($sortOption);
        $query->orderBy($sort['field'], $sort['order']);
    }

    // Xử lý tùy chọn sắp xếp
    public function handleSortOption(string $sortOption): array
    {
        switch ($sortOption) {
            case 'created_at_asc':
                return ['field' => 'created_at', 'order' => 'asc'];
            case 'created_at_desc':
                return ['field' => 'created_at', 'order' => 'desc'];
            default:
                return ['field' => 'created_at', 'order' => 'desc'];
        }
    }

   // Tính số tháng hợp đồng dựa trên ngày bắt đầu và kết thúc
    private function calculateContractMonths($startDate, $endDate)
    {
        try {
            if (!$startDate || !$endDate) {
                return 12;
            }

            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);

            // Sử dụng ceil để làm tròn lên
            $months = ceil($start->floatDiffInMonths($end));

            // Đảm bảo ít nhất là 1 tháng
            return max(1, (int)$months);

        } catch (\Throwable $e) {
            Log::error('Error calculating contract months: ' . $e->getMessage(), [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return 12;
        }
    }

    // Định dạng thời gian hợp đồng
    private function formatContractDuration($months)
    {
        if ($months < 12) {
            return $months . ' tháng';
        } else {
            $years = floor($months / 12);
            $remainingMonths = $months % 12;

            if ($remainingMonths == 0) {
                return $years . ' năm';
            } else {
                return $years . ' năm ' . $remainingMonths . ' tháng';
            }
        }
    }

    // Lấy thông tin đặt phòng theo ID
    public function generateContractPreviewData($booking)
    {
        $currentDate = now()->format('d/m/Y');
        $startDate = $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('d/m/Y') : $currentDate;
        $endDate = $booking->end_date ? \Carbon\Carbon::parse($booking->end_date)->format('d/m/Y') : \Carbon\Carbon::parse($booking->start_date)->addYear()->format('d/m/Y');

        // Tính số tháng hợp đồng
        $contractMonths = $this->calculateContractMonths($booking->start_date, $booking->end_date);

        return [
            'current_date' => $currentDate,
            'current_day' => date('d'),
            'current_month' => date('m'),
            'current_year' => date('Y'),

            // Thông tin bên A (chủ nhà) - có thể lấy từ config hoặc database
            'landlord' => [
                'name' => 'Phan Quốc Dương',
                'identity_number' => '083205001354',
                'year_of_birth' => '2005',
                'date_of_issue' => '01/01/2020',
                'place_of_issue' => 'Cục CSQLHC về TTXH',
                'permanent_address' => '123 Đường ABC, TP Hồ Chí Minh'
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
                'address' => $booking->room->motel->address ?? '',
                'motel_name' => $booking->room->motel->name ?? '',
                'area' => $booking->room->area ?? ''
            ],

            // Thông tin hợp đồng
            'contract' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'contract_months' => $contractMonths,
                'contract_duration' => $this->formatContractDuration($contractMonths),
                'rental_price' => $booking->room->price ?? 0,
                'deposit_amount' => $booking->room->price ?? 0,
                'payment_day' => '1-10',
                'additional_terms' => null,
                'signed_date' => null
            ],

            // Các phí
            'fees' => [
                'electricity_fee' => $booking->room->motel->electricity_fee ?? 0,
                'water_fee' => $booking->room->motel->water_fee ?? 0,
                'parking_fee' => $booking->room->motel->parking_fee ?? 0,
                'junk_fee' => $booking->room->motel->junk_fee ?? 0,
                'internet_fee' => $booking->room->motel->internet_fee ?? 0,
                'service_fee' => $booking->room->motel->service_fee ?? 0
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
        <div class="container-fluid p-0">
            <div class="contract-document mx-auto" style="max-width: 210mm; min-height: 297mm; background: white; font-family: \'Times New Roman\', serif; font-size: 13px; line-height: 1.4; padding: 15mm 20mm;">

                <!-- Header -->
                <div class="text-center mb-4">
                    <div class="mb-2">
                        <strong style="font-size: 14px; letter-spacing: 0.5px;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</strong>
                    </div>
                    <div class="mb-3">
                        <u><strong>Độc lập - Tự do - Hạnh phúc</strong></u>
                    </div>

                    <div class="my-4">
                        <h3 class="mb-0" style="font-size: 18px; font-weight: bold; letter-spacing: 1px;">
                            HỢP ĐỒNG CHO THUÊ
                        </h3>
                    </div>

                    <div class="text-end mb-4">
                        <div class="d-inline-block border border-dark px-2 py-1">
                            <strong>SGHood</strong>
                        </div>
                    </div>
                </div>

                <!-- Thông tin ngày tháng và bên ký -->
                <div class="mb-4">
                    <p class="mb-2">
                        Hôm nay ngày <strong>' . $contractData['current_day'] . '</strong>
                        tháng <strong>' . $contractData['current_month'] . '</strong>
                        năm <strong>' . $contractData['current_year'] . '</strong>
                    </p>

                    <div class="row mb-3">
                        <div class="col-6">
                            <p class="mb-1"><strong>CHỦ CHO THUÊ (Chủ nhà):</strong> (Gọi tắt là Bên A)</p>
                            <p class="mb-1">Họ và tên: <strong>' . $contractData['landlord']['name'] . '</strong> Sinh năm: <strong>' . $contractData['landlord']['year_of_birth'] . '</strong></p>
                            <p class="mb-1">CCCD số: <strong>' . $contractData['landlord']['identity_number'] . '</strong> Ngày cấp: <strong>' . $contractData['landlord']['date_of_issue'] . '</strong> Nơi cấp: <strong>' . $contractData['landlord']['place_of_issue'] . '</strong></p>
                            <p class="mb-0">Địa chỉ thường trú: <strong>' . $contractData['landlord']['permanent_address'] . '</strong></p>
                        </div>

                        <div class="col-6">
                            <div class="text-end">
                                <div class="border border-dark px-2 py-1 d-inline-block">
                                    <small>Mẫu số: 01/...</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="mb-1"><strong>BÊN THUÊ:</strong> (Gọi tắt là Bên B)</p>
                        <p class="mb-1">Họ và tên: <strong>' . $contractData['tenant']['name'] . '</strong> Sinh: <strong>' . ($contractData['tenant']['birthdate'] ? date('Y', strtotime($contractData['tenant']['birthdate'])) : '1995') . '</strong></p>
                        <p class="mb-1">CCCD Số: <strong>' . $contractData['tenant']['identity_document'] . '</strong> Ngày cấp: <strong>01/01/2021</strong> Nơi cấp: <strong>Cục CSQLHC về TTXH</strong></p>
                        <p class="mb-0">Địa chỉ thường trú: <strong>' . $contractData['tenant']['address'] . '</strong></p>
                    </div>
                </div>

                <!-- Nội dung thỏa thuận -->
                <div class="mb-4">
                    <p class="text-center mb-3">
                        <em>Sau khi bàn bạc hai bên thống nhất ký hợp đồng cho thuê với các điều khoản sau:</em>
                    </p>

                    <div class="mb-4">
                        <p class="mb-2"><strong>1. NỘI DUNG HỢP ĐỒNG:</strong></p>
                        <div class="ms-3">
                            <p class="mb-2">- Bên A đồng ý cho thuê phòng số: <strong>' . $contractData['room']['name'] . '</strong></p>
                            <p class="mb-2">- Địa chỉ: <strong>' . $contractData['room']['address'] . '</strong></p>
                            <p class="mb-2">- Mục đích thuê: <strong>Để ở</strong></p>
                            <p class="mb-2">- Thời hạn cho thuê là: <strong>' . $contractData['contract']['contract_duration'] . '</strong>, bắt đầu từ ngày <strong>' . $contractData['contract']['start_date'] . '</strong> đến hết ngày <strong>' . $contractData['contract']['end_date'] . '</strong></p>
                            <p class="mb-2">- Sau khi ký hợp đồng bên B sẽ đặt cọc cho bên A: <strong>' . number_format($contractData['contract']['deposit_amount'], 0, ",", ".") . '</strong> đ.</p>
                            <p class="mb-2">- Bằng chữ: <strong>' . $this->convertNumberToWords($contractData['contract']['deposit_amount']) . ' đồng</strong></p>
                            <p class="mb-2">- Giá cho thuê: <strong>' . number_format($contractData['contract']['rental_price'], 0, ",", ".") . '</strong> đ/tháng.</p>
                            <p class="mb-2">- Bằng chữ: <strong>' . $this->convertNumberToWords($contractData['contract']['rental_price']) . ' đồng</strong></p>
                            <p class="mb-0">- Phương thức thanh toán: Mỗi tháng bên B thanh toán cho bên A bằng tiền mặt hoặc chuyển khoản. Bên A thu tiền từ ngày 01 đến ngày 10 hàng tháng.</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="mb-2"><strong>2. TRÁCH NHIỆM MỖI BÊN</strong></p>

                        <div class="mb-3">
                            <p class="mb-2"><strong>a) Bên A:</strong></p>
                            <div class="ms-3">
                                <p class="mb-1">- Trong thời gian hợp đồng chủ nhà sẽ không tăng giá tiền nhà.</p>
                                <p class="mb-1">- Kịp thời sửa chữa hư hỏng trong quá trình sử dụng.</p>
                                <p class="mb-0">- Tạo mọi điều kiện cho Bên B trong ăn, ở, sinh hoạt, học tập.</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <p class="mb-2"><strong>b) Bên B:</strong></p>
                            <div class="ms-3">
                                <p class="mb-1">- Có trách nhiệm bảo quản nhà, mọi hư hỏng phải báo ngay cho bên A.</p>
                                <p class="mb-1">- Thanh toán tiền nhà cho bên A theo đúng thời hạn quy định.</p>
                                <p class="mb-1">- Không được mang chất dễ cháy, chất nổ, vũ khí ma túy vào nhà thuê.</p>
                                <p class="mb-1">- Không đánh bạc, uống rượu, bia, gây gổ làm mất an ninh trật tự.</p>
                                <p class="mb-1">- Không tự ý để người không đăng ký ở lại nhà thuê.</p>
                                <p class="mb-1">- Nếu vi phạm các quy định trên thì bên B sẽ tự chịu trách nhiệm khi cơ quan công an xử phạt hành chính theo pháp luật.</p>
                                <p class="mb-1">- Trường hợp bên B trả nhà trong thời gian hợp đồng ' . $contractData['contract']['contract_duration'] . ' thì chủ nhà không trả lại tiền đặt cọc.</p>
                                <p class="mb-1">- Sau khi hết hợp đồng chủ nhà sẽ trả lại tiền đặt cọc là: <strong>' . number_format($contractData['contract']['deposit_amount'], 0, ",", ".") . '</strong> đ cho bên B.</p>
                                <p class="mb-1">- Nếu mọi hư hỏng do bên B gây ra thì bên B sẽ chịu bồi thường mọi chi phí sửa chữa.</p>
                                <p class="mb-1">- Không được tự ý sang, chuyển nhượng phòng trọ cho người khác khi không được chủ nhà đồng ý.</p>
                                <p class="mb-0">- Trong thời hạn 02 ngày kể từ khi đến ở trọ phải đăng ký tạm trú, đăng ký xe máy để làm thẻ xe.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="mb-2"><strong>3. ĐIỀU KHOẢN CHUNG</strong></p>
                        <div class="ms-3">
                            <p class="mb-1">- Hợp Đồng này có hiệu lực kể từ ngày đại diện hai bên cùng ký.</p>
                            <p class="mb-1">- Mọi thay đổi của hợp đồng phải được hai bên thỏa thuận (nếu có).</p>
                            <p class="mb-0">- Hai bên cam kết thực hiện nghiêm chính hợp đồng này</p>
                        </div>

                        <p class="mt-3 mb-0">
                            <em>Hợp đồng này đã được lập thành 02 bản, mỗi bên giữa 01 bản và có giá trị pháp lý như nhau.</em>
                        </p>
                    </div>
                </div>

                <!-- Chữ ký -->
                <div class="row mt-5 pt-4">
                    <div class="col-6 text-center">
                        <p class="mb-1"><strong>BÊN A</strong></p>
                        <p class="mb-5"><em>(Ký, ghi rõ họ tên)</em></p>
                        <div class="mt-5 pt-3">
                            <p class="mb-0"><strong>' . $contractData['landlord']['name'] . '</strong></p>
                        </div>
                    </div>

                    <div class="col-6 text-center">
                        <p class="mb-1"><strong>BÊN B</strong></p>
                        <p class="mb-5"><em>(Ký, ghi rõ họ tên)</em></p>
                        <div class="mt-5 pt-3">
                            <p class="mb-0"><strong>' . $contractData['tenant']['name'] . '</strong></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <style>
            @media print {
                .container-fluid {
                    padding: 0 !important;
                }

                .contract-document {
                    max-width: none !important;
                    margin: 0 !important;
                    padding: 15mm 20mm !important;
                }

                body {
                    font-size: 12pt !important;
                }
            }

            .contract-document {
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }

            .border-dark {
                border-color: #000 !important;
            }

            p {
                margin-bottom: 0.5rem;
            }

            .text-justify {
                text-align: justify;
            }

            /* Đảm bảo font Times New Roman hiển thị đúng */
            .contract-document * {
                font-family: \'Times New Roman\', serif !important;
            }
        </style>
        ';

        return $content;
    }

    // Thêm method chuyển đổi số thành chữ (có thể đặt ở cuối class)
    private function convertNumberToWords($number)
    {
        $ones = array(
            '', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'
        );

        $tens = array(
            '', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi',
            'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'
        );

        if ($number == 0) return 'không';

        $result = '';

        // Xử lý hàng triệu
        if ($number >= 1000000) {
            $millions = intval($number / 1000000);
            $result .= $this->convertHundreds($millions) . ' triệu ';
            $number %= 1000000;
        }

        // Xử lý hàng nghìn
        if ($number >= 1000) {
            $thousands = intval($number / 1000);
            $result .= $this->convertHundreds($thousands) . ' nghìn ';
            $number %= 1000;
        }

        // Xử lý hàng trăm
        if ($number > 0) {
            $result .= $this->convertHundreds($number);
        }

        return trim($result);
    }

    private function convertHundreds($number)
    {
        $ones = array(
            '', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'
        );

        $result = '';

        if ($number >= 100) {
            $hundreds = intval($number / 100);
            $result .= $ones[$hundreds] . ' trăm ';
            $number %= 100;
        }

        if ($number >= 20) {
            $tens = intval($number / 10);
            $result .= $ones[$tens] . ' mươi ';
            $number %= 10;
            if ($number > 0) {
                $result .= $ones[$number] . ' ';
            }
        } elseif ($number >= 10) {
            $result .= 'mười ';
            $number %= 10;
            if ($number > 0) {
                $result .= $ones[$number] . ' ';
            }
        } elseif ($number > 0) {
            $result .= $ones[$number] . ' ';
        }

        return trim($result);
    }

    // Lấy xem trước hợp đồng dựa trên ID đặt phòng
    public function getContractPreview($bookingId)
    {
        try {
            $booking = Booking::with(['user', 'room.motel'])->findOrFail($bookingId);
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

    public function updateBookingStatus($id, $status, $cancellation_reason = null)
    {
        try {
            $booking = Booking::with(['user', 'room.motel'])->findOrFail($id);
            $oldStatus = $booking->status;

            Log::info('Updating booking status', [
                'booking_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'cancellation_reason' => $cancellation_reason
            ]);

            $updateData = ['status' => $status];
            if ($cancellation_reason) {
                $updateData['cancellation_reason'] = $cancellation_reason;
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
                    'contract_months' => $contractPreviewData['contract']['contract_months'],
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
                    Mail::to($booking->user->email)->send(new BookingRejected($booking, $cancellation_reason ?? ''));
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

    public function updateBookingCancellation($id, $cancellation_reason)
    {
        try {
            $booking = Booking::findOrFail($id);

            // Log before update
            Log::info('Updating booking cancellation_reason', [
                'booking_id' => $id,
                'old_cancellation_reason' => $booking->cancellation_reason,
                'new_cancellation_reason' => $cancellation_reason
            ]);

            $booking->update(['cancellation_reason' => $cancellation_reason]);

            // Reload to get fresh data
            $booking->refresh();

            return ['data' => $booking];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Booking not found: ' . $e->getMessage(), ['booking_id' => $id]);
            return ['error' => 'Không tìm thấy đặt phòng', 'status' => 404];
        } catch (\Throwable $e) {
            Log::error('Error updating booking Cancellation: ' . $e->getMessage(), [
                'booking_id' => $id,
                'cancellation_reason' => $cancellation_reason
            ]);
            return ['error' => 'Đã xảy ra lỗi khi cập nhật lý do', 'status' => 500];
        }
    }

    public function updateBookingStatusAndCancellation_reason($id, $status, $cancellation_reason)
    {
        try {
            return DB::transaction(function () use ($id, $status, $cancellation_reason) {
                $booking = Booking::with(['user', 'room.motel'])->findOrFail($id);
                $oldStatus = $booking->status;

                Log::info('Updating booking status and cancellation_reason', [
                    'booking_id' => $id,
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'old_cancellation_reason' => $booking->cancellation_reason,
                    'new_cancellation_reason' => $cancellation_reason
                ]);

                $updateData = ['status' => $status];
                if ($cancellation_reason) {
                    $updateData['cancellation_reason'] = $cancellation_reason;
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
                        'contract_months' => $contractPreviewData['contract']['contract_months'],
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
                        Mail::to($booking->user->email)->send(new BookingRejected($booking, $cancellation_reason ?? ''));
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
            Log::error('Error updating booking status and cancellation_reason: ' . $e->getMessage(), [
                'booking_id' => $id,
                'status' => $status,
                'cancellation_reason' => $cancellation_reason
            ]);
            return ['error' => 'Đã xảy ra lỗi khi cập nhật thông tin đặt phòng', 'status' => 500];
        }
    }
}
