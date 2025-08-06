<?php

namespace App\Http\Controllers;

use App\Http\Requests\AmenityRequest;
use App\Services\AmenityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmenityController extends Controller
{
    protected AmenityService $amenityService;

    public function __construct(AmenityService $amenityService)
    {
        $this->amenityService = $amenityService;
    }

    // Hiển thị danh sách tiện nghi
    public function index(Request $request): View
    {
        $querySearch = $request->query('query', '');
        $type = $request->query('type', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 15);

        // Sửa: Thêm tham số $type
        $result = $this->amenityService->getAvailableAmenities($querySearch, $type, $sortOption, $perPage);

        if (isset($result['error'])) {
            return view('amenities.index')
                ->with('error', $result['error'])
                ->with('amenities', collect())
                ->with('querySearch', $querySearch)
                ->with('type', $type)
                ->with('sortOption', $sortOption);
        }

        return view('amenities.index', [
            'amenities' => $result['data'],
            'querySearch' => $querySearch,
            'type' => $type,
            'sortOption' => $sortOption,
        ]);
    }

    // Hiển thị trang tạo tiện nghi
    public function create(): View
    {
        return view('amenities.create');
    }

    // Xử lý lưu tiện nghi
    public function store(AmenityRequest $request): RedirectResponse
    {
        $result = $this->amenityService->createAmenity($request->validated());

        if (isset($result['error'])) {
            return redirect()->back()
                ->with('error', $result['error'])
                ->withInput();
        }

        return redirect()->route('amenities.index')
            ->with('success', 'Tiện nghi đã được tạo thành công!');
    }

    // Hiển thị trang chỉnh sửa tiện nghi
    public function edit(string $id): View|RedirectResponse
    {
        $result = $this->amenityService->getAmenity($id);

        if (isset($result['error'])) {
            return redirect()->route('amenities.index')
                ->with('error', $result['error']);
        }

        return view('amenities.edit', [
            'amenity' => $result['data']
        ]);
    }

    // Xử lý cập nhật tiện nghi
    public function update(AmenityRequest $request, string $id): RedirectResponse
    {
        $result = $this->amenityService->updateAmenity($id, $request->validated());

        if (isset($result['error'])) {
            return redirect()->back()
                ->with('error', $result['error'])
                ->withInput();
        }

        return redirect()->route('amenities.index')
            ->with('success', 'Tiện nghi đã được cập nhật thành công!');
    }

    // Xóa tiện nghi
    public function destroy(string $id): RedirectResponse
    {
        $result = $this->amenityService->deleteAmenity($id);

        if (isset($result['error'])) {
            return redirect()->back()
                ->with('error', $result['error']);
        }

        return redirect()->route('amenities.index')
            ->with('success', 'Tiện nghi đã được xóa thành công!');
    }

    // Hiển thị danh sách tiện nghi đã xóa
    public function trash(Request $request): View
    {
        $querySearch = $request->query('query', '');
        $type = $request->query('type', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 10);

        // Sửa: Thêm tham số $type
        $result = $this->amenityService->getTrashedAmenities($querySearch, $type, $sortOption, $perPage);

        if (isset($result['error'])) {
            return view('amenities.trash')
                ->with('error', $result['error'])
                ->with('amenities', collect())
                ->with('querySearch', $querySearch)
                ->with('type', $type)
                ->with('sortOption', $sortOption);
        }

        return view('amenities.trash', [
            'amenities' => $result['data'],
            'querySearch' => $querySearch,
            'type' => $type,
            'sortOption' => $sortOption,
        ]);
    }

    // Khôi phục tiện nghi đã xóa
    public function restore(string $id): RedirectResponse
    {
        $result = $this->amenityService->restoreAmenity($id);

        if (isset($result['error'])) {
            return redirect()->back()
                ->with('error', $result['error']);
        }

        return redirect()->route('amenities.trash')
            ->with('success', 'Tiện nghi đã được khôi phục thành công!');
    }

    // Xóa vĩnh viễn tiện nghi
    public function forceDelete(string $id): RedirectResponse
    {
        $result = $this->amenityService->forceDeleteAmenity($id);

        if (isset($result['error'])) {
            return redirect()->back()
                ->with('error', $result['error']);
        }

        return redirect()->route('amenities.trash')
            ->with('success', 'Tiện nghi đã được xóa vĩnh viễn!');
    }

    public function changeOrder(Request $request): View
    {
        $typeFilter = $request->get('type');
        $searchQuery = $request->get('search');

        $amenitiesByType = $this->amenityService->getAllAmenitiesByType($typeFilter, $searchQuery);

        if (isset($amenitiesByType['error'])) {
            return view('amenities.change_order')
                ->with('error', $amenitiesByType['error'])
                ->with('amenitiesByType', [])
                ->with('selectedType', $typeFilter)
                ->with('searchQuery', $searchQuery);
        }

        return view('amenities.change_order', [
            'amenitiesByType' => $amenitiesByType['data'],
            'selectedType' => $typeFilter,
            'searchQuery' => $searchQuery
        ]);
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|string',
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:amenities,id'
        ]);

        $result = $this->amenityService->reorderAmenyties($data['type'], $data['order']);

        if (isset($result['error'])) {
            return response()->json(['success' => false, 'error' => $result['error']], $result['status'] ?? 400);
        }

        return response()->json(['success' => true, 'message' => 'Thứ tự đã được cập nhật thành công']);
    }
}
