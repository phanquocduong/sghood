<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class CheckoutController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['inventory_status', 'querySearch', 'sort_order']);
        $checkouts = $this->checkoutService->getCheckouts($filters);

        return view('checkouts.index', compact('checkouts'));
    }

    public function show($id)
    {
        $checkout = $this->checkoutService->getCheckoutById($id);
        return view('checkouts.show', compact('checkout'));
    }

    public function edit($id)
    {
        $checkout = $this->checkoutService->getCheckoutById($id);
        return view('checkouts.edit', compact('checkout'));
    }

    public function update(CheckoutRequest $request, $id)
    {
        try {
            $data = $request->validated();
            Log::info('Validated data in CheckoutController:', $data);
            $checkout = $this->checkoutService->updateCheckout($id, $data);
            Log::info('Checkout updated:', $checkout->toArray());

            return redirect()->route('checkouts.index')->with('success', 'Cập nhật checkout thành công');
        } catch (\Exception $e) {
            Log::error('Update error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('checkouts.index')->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
    public function reInventory($id)
    {
        try {
            $checkout = $this->checkoutService->reInventoryCheckout($id);

            return redirect()->back()->with('success', 'Đã chuyển trạng thái checkout sang "Kiểm kê lại" thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
