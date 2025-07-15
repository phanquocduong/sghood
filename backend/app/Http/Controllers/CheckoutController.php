<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status']);
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

    public function update(Request $request, $id)
    {
        $request->validate([
            'check_out_date' => 'required|date',
            'has_left' => 'required|boolean',
            'status' => 'required|in:Chờ kiểm kê,Đã kiểm kê',
            'deduction_amount' => 'nullable|numeric',
            'inventory_details' => 'required|json',
            'inventory_value_text.*' => 'nullable|string',
            'inventory_value_image.*' => 'nullable|image|mimes:jpeg,png,gif|max:2048',
        ], [
            'inventory_details.json' => 'Dữ liệu kiểm kê phải là JSON hợp lệ.',
            'inventory_value_image.*.image' => 'File phải là hình ảnh.',
        ]);

        $checkout = $this->checkoutService->updateCheckout($id, $request->all());

        return redirect()->back()->with('success', 'Cập nhật checkout thành công!');
    }
}
