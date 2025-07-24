<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Contract;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    public function run()
    {
        // Lấy tất cả booking có status 'Chấp nhận'
        $bookings = Booking::where('status', Booking::STATUS_ACCEPTED)->get();

        foreach ($bookings as $booking) {
            // Lấy thông tin liên quan
            $room = $booking->room;
            $room_price = number_format($room->price, 0, ",", ".");
            $user = $booking->user;
            $motel = $room->motel;

            // Tạo CCCD ngẫu nhiên 12 chữ số
            $cccd = '';
            do {
                $cccd = str_pad(mt_rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
            } while (Contract::where('content', 'like', '%' . $cccd . '%')->exists());

            // Tạo ngày cấp ngẫu nhiên (không lớn hơn hiện tại)
            $issueDate = Carbon::today()->subDays(mt_rand(0, 365 * 5))->format('d/m/Y');

            // Tính thời hạn hợp đồng (năm)
            $startDate = Carbon::parse($booking->start_date);
            $endDate = Carbon::parse($booking->end_date);
            $contractDuration = $endDate->year - $startDate->year;

            // Chuyển đổi số tiền thành chữ
            $priceInWords = $this->convertNumberToWords($room->price);

            // Tạo nội dung HTML cho contract
            $content = <<<HTML
<head></head><body><div class="container-fluid p-0">
    <div class="contract-document mx-auto" style="max-width: 210mm; min-height: 297mm; background: white; font-size: 14px; line-height: 1.5; padding: 15mm 20mm;">
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
        </div>

        <!-- Thông tin ngày tháng và bên ký -->
        <div class="mb-4">
            <p class="mb-2">
                Hôm nay ngày <strong>24</strong> tháng <strong>07</strong> năm <strong>2025</strong>
            </p>
            <div class="row mb-3">
                <div class="col-10">
                    <p class="mb-1"><strong>CHỦ CHO THUÊ (Chủ nhà):</strong> (Gọi tắt là Bên A)</p>
                    <p class="mb-1">Họ và tên: <strong>Nguyễn Hoàng Minh</strong> <span class="ms-3">Sinh năm: <strong>1985</strong></span></p>
                    <p class="mb-1">CCCD số: <strong>079108004672</strong> <span class="ms-3">Ngày cấp: <strong>25/09/2018</strong></span></p>
                    <p class="mb-1"> Nơi cấp: <strong>Cục Cảnh Sát Quản Lý Hành Chính Về Trật Tự Xã Hội</strong></p>
                    <p class="mb-0">Địa chỉ thường trú: <strong>45/7, Khu Phố 3, Phường Tân Phú, Quận 7, Thành phố Hồ Chí Minh</strong></p>
                </div>
                <div class="col-2">
                    <div class="text-end">
                        <div class="d-inline-block border border-dark px-2 py-1">
                            <strong>SGHood</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin bên B -->
            <div class="mb-3">
                <p class="mb-2"><strong>BÊN THUÊ:</strong> (Gọi tắt là Bên B)</p>
                <div class="mb-2">
                    <span>Họ và tên: </span>
                    <input type="text" class="form-control flat-line d-inline-block" style="width: 200px;" name="full_name" value="{$user->name}" readonly="">
                    <span class="ms-3">Sinh năm: </span>
                    <input type="text" class="form-control flat-line d-inline-block" style="width: 100px;" name="year_of_birth" value="{$user->birthdate->year}" readonly="">
                </div>
                <div class="mb-2">
                    <span>CCCD Số: </span>
                    <input type="text" class="form-control flat-line d-inline-block" style="width: 150px;" name="identity_number" value="{$cccd}" readonly="">
                    <span class="ms-3">Ngày cấp: </span>
                    <input type="text" class="form-control flat-line d-inline-block" style="width: 150px;" name="date_of_issue" value="{$issueDate}" readonly="">
                </div>
                <div class="mb-2">
                    <span>Nơi cấp: </span>
                    <input type="text" class="form-control flat-line d-inline-block" style="width: 500px;" name="place_of_issue" value="Cục Trưởng Cục Cảnh Sát Quản Lý Hành Chính Về Trật Tự Xã Hội" readonly="">
                </div>
                <div class="mb-0">
                    <span>Địa chỉ thường trú: </span>
                    <input type="text" class="form-control flat-line d-inline-block" style="width: 500px;" name="permanent_address" value="{$user->address}" readonly="">
                </div>
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
                    <p class="mb-2">- Bên A đồng ý cho thuê phòng số: <strong>{$room->name}</strong></p>
                    <p class="mb-2">- Địa chỉ: <strong>{$motel->address}</strong></p>
                    <p class="mb-2">- Mục đích thuê: <strong>Để ở</strong></p>
                    <p class="mb-2">- Thời hạn cho thuê là: <strong>{$contractDuration} năm</strong>, bắt đầu từ ngày <strong>{$startDate->format('d/m/Y')}</strong> đến hết ngày <strong>{$endDate->format('d/m/Y')}</strong></p>
                    <p class="mb-2">- Sau khi ký hợp đồng bên B sẽ đặt cọc cho bên A: <strong>{$room_price}</strong> đ.</p>
                    <p class="mb-2">- Bằng chữ: <strong>{$priceInWords} đồng</strong></p>
                    <p class="mb-2">- Giá cho thuê: <strong>{$room_price}</strong> đ/tháng.</p>
                    <p class="mb-2">- Bằng chữ: <strong>{$priceInWords} đồng</strong></p>
                    <p class="mb-0">- Phương thức thanh toán: Mỗi tháng bên B thanh toán cho bên A bằng tiền mặt hoặc chuyển khoản. Bên A thu tiền từ ngày 01 đến ngày 10 hàng tháng.</p>
                </div>
            </div>

            <div class="mb-4">
                <p class="mb-2"><strong>2. TRÁCH NHIỆM MỖI BÊN</strong></p>
                <div class="mb-3">
                    <p class="mb-2 page-break"><strong>a) Bên A:</strong></p>
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
                        <p class="mb-1">- Trường hợp bên B trả nhà trong thời gian hợp đồng {$contractDuration} năm thì chủ nhà không trả lại tiền đặt cọc.</p>
                        <p class="mb-1">- Sau khi hết hợp đồng chủ nhà sẽ trả lại tiền đặt cọc là: <strong>{$room_price}</strong> đ cho bên B.</p>
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
                <p><em>(Ký, ghi rõ họ tên)</em></p>
                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAADICAYAAADGFbfiAAAAAXNSR0IArs4c6QAAFiRJREFUeF7tnWuIVVUbxx8jyywlMMNbmHaBmiAvRWaa5Yfwi6RmYeWgMo2QEqZfIirLDArCzBBE8daMIRYp4xeJyBpGU0vT/CDSBRMLg7LLpN1jXp5Na97t6Zwz+6yz9zlrrf1bIK82a639PL9nvf5dt2f16urq6hIKBCAAAQhAoEICvRCQColRHQIQgAAEIgIICAMBAhCAAASsCCAgVthoBAEIQAACCAhjAAIQgAAErAggIFbYaAQBCEAAAggIYwACEIAABKwIICBW2GgEAQhAAAIICGMAAhCAAASsCCAgVthoBAEIQAACCAhjAAIQgAAErAggIFbYaAQBCEAAAggIYwACEIAABKwIICBW2GgEAQhAAAIICGMAAhCAAASsCCAgVthoBAEIQAACCAhjAAIQgAAErAggIFbYaAQBCEAAAggIYwACEIAABKwIICBW2GgEAQhAAAIICGMAAhCAAASsCCAgVthoBAEIQAACCAhjAAIQgAAErAggIFbYaAQBCEAAAggIYwACEIAABKwIICBW2GgEAQhAAAIICGMAAhCAAASsCCAgVthoBAEIQAACCAhjAAIQgAAErAggIFbYaAQBCEAAAggIYwACEIAABKwIICBW2GgEAQhAAAIICGMAAhCAAASsCCAgVthoBAEIQAACCAhjAAIQgAAErAggIFbYaAQBCEAAAggIYwACEIAABKwIICBW2GgEAQhAAAIICGMAAhCAAASsCCAgVthoBAEIQAACCAhjAAIQgAAErAggIFbYaAQBCEAAAggIYyDXBBYvXizt7e3ywQcfSP/+/XPNAuchUCkBBKRSYtQPisAtt9wihw4dkoMHD8rYsWOD8g1nIJA1AQQka8L07zQBBMTp8GCc4wQQEMcDhHnZEkBAsuVL72ETQEDCji/elSFw9OhRmTx5spw5c0Z27dolU6ZMgRcEIFABAQSkAlhUDYeACsf777/f7dDbb78tM2bMCMdBPIFADQggIDWAzCfcI9CrV6/IqAULFkhjY6OMGzfOPSOxCAKOE0BAHA8Q5qVPQI/s3n333TJs2DA5depU+h+gRwjkhAACkpNA4+b/CTz33HOybNkyefbZZ0V/T4EABOwIICB23GjlMQGzfHXixAm5+uqr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" style="width: 200px; height: 100px">
                <p><strong>Nguyễn Hoàng Minh</strong></p>
            </div>
            <div class="col-6 text-center">
                <p class="mb-1"><strong>BÊN B</strong></p>
                <p class="mark-sign"><em>(Ký, ghi rõ họ tên)</em></p>
                <img src="data:image/png;base64,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" class="signature-image" alt="Chữ ký Bên B">
                <p class="signature-name"><strong>{$user->name}</strong></p>
            </div>
        </div>
    </div>
    <style>
        @media print {
            .container-fluid { padding: 0 !important; }
            .contract-document { max-width: none !important; margin: 0 !important; padding: 15mm 20mm !important; }
            body { font-size: 12pt !important; }
        }
        .contract-document { box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .border-dark { border-color: #000 !important; }
        p { margin-bottom: 0.5rem; }
        .text-justify { text-align: justify; }
        .contract-document * { font-family: 'Times New Roman', serif !important; color: #212529 !important; }
        .form-control.flat-line {
            border: none; border-bottom: 1px dotted #666; border-radius: 0; outline: none;
            background: transparent; height: 25px; box-shadow: none !important; font-weight: bold;
            display: inline-block; vertical-align: bottom; margin: 0 0 5px 0;
        }
        .form-control.flat-line:focus { border: none; border-bottom: 1px dotted #666; }
    </style>
</div></body>
HTML;

            // Tạo created_at ngẫu nhiên giữa start_date và created_at của booking
            $bookingCreatedAt = Carbon::parse($booking->created_at);
            $createdAt = Carbon::createFromTimestamp(mt_rand($bookingCreatedAt->timestamp, $startDate->timestamp));

            // Tạo signed_at và updated_at (cộng thêm vài phút ngẫu nhiên)
            $signedAt = $createdAt->copy()->addMinutes(mt_rand(5, 30));
            $updatedAt = $signedAt->copy()->addMinutes(mt_rand(5, 30));

            // Tạo contract
            Contract::create([
                'room_id' => $booking->room_id,
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'rental_price' => $room->price,
                'deposit_amount' => $room->price,
                'content' => $content,
                'signature' => 'images/signatures/contract-8-1753325922.png',
                'status' => 'Hoạt động',
                'file' => 'pdf/contracts/contract-8-1753326013.pdf',
                'created_at' => $createdAt,
                'signed_at' => $signedAt,
                'updated_at' => $updatedAt,
            ]);

            // Cập nhật role của user thành "Người thuê"
            $user->update([
                'identity_document' => 'images/identity_document/user-106-1753325616-0.webp.enc|images/identity_document/user-106-1753325618-1.webp.enc',
                'role' => 'Người thuê'
            ]);

            // Cập nhật status của room thành "Đã thuê"
            $room->update(['status' => 'Đã thuê']);
        }
    }

    // Thêm method chuyển đổi số thành chữ (có thể đặt ở cuối class)
    private function convertNumberToWords($number)
    {
        $ones = array(
            '',
            'một',
            'hai',
            'ba',
            'bốn',
            'năm',
            'sáu',
            'bảy',
            'tám',
            'chín'
        );

        $tens = array(
            '',
            '',
            'hai mươi',
            'ba mươi',
            'bốn mươi',
            'năm mươi',
            'sáu mươi',
            'bảy mươi',
            'tám mươi',
            'chín mươi'
        );

        if ($number == 0)
            return 'không';

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
            '',
            'một',
            'hai',
            'ba',
            'bốn',
            'năm',
            'sáu',
            'bảy',
            'tám',
            'chín'
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
}
