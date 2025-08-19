<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;
use App\Services\MeterReadingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;
    protected MeterReadingService $meterReadingService;

    public function __construct(InvoiceService $invoiceService, MeterReadingService $meterReadingService)
    {
        $this->invoiceService = $invoiceService;
        $this->meterReadingService = $meterReadingService;
    }

    // Hiển thị danh sách hóa đơn
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'month' => $request->get('month'),
            'year' => $request->get('year'),
            'status' => $request->get('status'),
        ];

        $perPage = $request->get('per_page', 20);
        $invoices = $this->invoiceService->getAllInvoices($filters, $perPage);
        $stats = $this->invoiceService->getInvoiceStats($filters);

        return view('invoices.index', [
            'invoices' => $invoices,
            'stats' => $stats,
            'months' => $this->invoiceService->getMonths(),
            'years' => $this->invoiceService->getYears(),
            'statuses' => $this->invoiceService->getStatuses(),
            'filters' => $filters
        ]);
    }

    // Hiển thị chi tiết hóa đơn
public function show(int $id): JsonResponse
{
    try {
        $invoice = $this->invoiceService->getInvoiceById($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hóa đơn'
            ], 404);
        }

        // Đảm bảo các relationship được load
        $invoice->load(['contract.user', 'contract.room.motel', 'meterReading']);

        // Lấy số tiêu thụ và chỉ số tháng trước từ MeterReadingService
        $consumptionData = $invoice->type !== 'Đặt cọc' && $invoice->meterReading
            ? $this->meterReadingService->getConsumptionAndPreviousReadings($invoice->meterReading)
            : [
                'electricity_consumption' => 0,
                'water_consumption' => 0,
                'previous_electricity_kwh' => 0,
                'previous_water_m3' => 0,
            ];

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $invoice->id,
                'code' => $invoice->code,
                'type' => $invoice->type ?? 'Hóa đơn tiền phòng',
                'month' => $invoice->month,
                'year' => $invoice->year,
                'status' => $invoice->status,
                'created_at' => $invoice->created_at->format('d/m/Y H:i'),
                'refunded_at' => $invoice->refunded_at ? \Carbon\Carbon::parse($invoice->refunded_at)->format('d/m/Y') : null,
                'customer' => [
                    'name' => $invoice->contract->user->name ?? 'N/A',
                    'email' => $invoice->contract->user->email ?? 'N/A',
                    'phone' => $invoice->contract->user->phone ?? 'N/A',
                ],
                'room' => [
                    'id' => $invoice->contract->id ?? 'N/A',
                    'name' => $invoice->contract->room->name ?? 'N/A',
                    'motel_name' => $invoice->contract->room->motel->name ?? 'N/A',
                ],
                'fees' => [
                    'room_fee' => number_format($invoice->room_fee ?? $invoice->contract->room->price ?? 0, 0, ',', '.'),
                    'electricity_fee' => $invoice->type !== 'Đặt cọc' ? number_format($invoice->electricity_fee ?? 0, 0, ',', '.') : 'N/A',
                    'water_fee' => $invoice->type !== 'Đặt cọc' ? number_format($invoice->water_fee ?? 0, 0, ',', '.') : 'N/A',
                    'parking_fee' => $invoice->type !== 'Đặt cọc' ? number_format($invoice->parking_fee ?? 0, 0, ',', '.') : 'N/A',
                    'junk_fee' => $invoice->type !== 'Đặt cọc' ? number_format($invoice->junk_fee ?? 0, 0, ',', '.') : 'N/A',
                    'internet_fee' => $invoice->type !== 'Đặt cọc' ? number_format($invoice->internet_fee ?? 0, 0, ',', '.') : 'N/A',
                    'service_fee' => $invoice->type !== 'Đặt cọc' ? number_format($invoice->service_fee ?? 0, 0, ',', '.') : 'N/A',
                    'total_amount' => number_format($invoice->total_amount ?? 0, 0, ',', '.'),
                ],
                'meter_reading' => $invoice->type !== 'Đặt cọc' && $invoice->meterReading ? [
                    'electricity_kwh' => $invoice->meterReading->electricity_kwh ?? 0,
                    'water_m3' => $invoice->meterReading->water_m3 ?? 0,
                    'electricity_consumption' => $consumptionData['electricity_consumption'],
                    'water_consumption' => $consumptionData['water_consumption'],
                    'previous_electricity_kwh' => $consumptionData['previous_electricity_kwh'],
                    'previous_water_m3' => $consumptionData['previous_water_m3'],
                ] : null
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error in invoice show: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi tải thông tin hóa đơn'
        ], 500);
    }
}

    // Cập nhật trạng thái hóa đơn
    public function updateStatus(Request $request, int $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:Đã trả'
            ]);

            $this->invoiceService->updateInvoiceStatus($id, $request->status, $request);

            return redirect()->route('invoices.index')
                ->with('success', "Cập nhật trạng thái hóa đơn sang 'Đã trả' thành công");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Dữ liệu đầu vào không hợp lệ');
        } catch (\Exception $e) {
            Log::error('Error updating invoice status: ' . $e->getMessage(), [
                'invoice_id' => $id,
                'user_id' => auth()->user()->id
            ]);

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage());
        }
    }
}
