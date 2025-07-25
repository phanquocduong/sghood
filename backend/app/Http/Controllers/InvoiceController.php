<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
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

        $perPage = $request->get('per_page', 15);
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

                    // Thông tin khách hàng
                    'customer' => [
                        'name' => $invoice->contract->user->name ?? 'N/A',
                        'email' => $invoice->contract->user->email ?? 'N/A',
                        'phone' => $invoice->contract->user->phone ?? 'N/A',
                    ],

                    // Thông tin phòng
                    'room' => [
                        'name' => $invoice->contract->room->name ?? 'N/A',
                        'motel_name' => $invoice->contract->room->motel->name ?? 'N/A',
                    ],

                    // Chi tiết chi phí
                    'fees' => [
                        'room_fee' => number_format($invoice->room_fee ?? $invoice->contract->room->price, 0, ',', '.'),
                        'electricity_fee' => number_format($invoice->electricity_fee ?? 0, 0, ',', '.'),
                        'water_fee' => number_format($invoice->water_fee ?? 0, 0, ',', '.'),
                        'parking_fee' => number_format($invoice->parking_fee ?? 0, 0, ',', '.'),
                        'junk_fee' => number_format($invoice->junk_fee ?? 0, 0, ',', '.'),
                        'internet_fee' => number_format($invoice->internet_fee ?? 0, 0, ',', '.'),
                        'service_fee' => number_format($invoice->service_fee ?? 0, 0, ',', '.'),
                        'total_amount' => number_format($invoice->total_amount ?? 0, 0, ',', '.'),
                    ],

                    // Thông tin chỉ số điện nước
                    'meter_reading' => [
                        'electricity_kwh' => $invoice->meterReading->electricity_kwh ?? 0,
                        'water_m3' => $invoice->meterReading->water_m3 ?? 0,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in invoice show: ' . $e->getMessage());

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
            // Validate input
            $request->validate([
                'status' => 'required|in:Đã trả'
            ]);

            // Gọi service để cập nhật trạng thái
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
