<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistrictRequest;
use App\Services\DistrictService;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    protected $districtService;

    public function __construct(DistrictService $districtService)
    {
        $this->districtService = $districtService;
    }

    public function index(Request $request)
    {
        $querySearch = (string) $request->input('query', '');
        $sortOption = (string) $request->input('sortOption', '');
        $perPage = (int) $request->get('perPage', 10);

        $result = $this->districtService->getAvailableDistricts($querySearch, $sortOption, $perPage);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return view('districts.index', ['districts' => $result['data']]);
    }

    public function create()
    {
        return view('districts.create');
    }

    public function store(DistrictRequest $request)
    {
        $result = $this->districtService->createDistrict($request->validated(), $request->file('image'));

        if (isset($result['error'])) {
            return redirect()->back()->withInput()->with('error', $result['error']);
        }

        return redirect()->route('districts.index')->with('success', 'Khu vực đã được tạo thành công!');
    }

    public function edit(int $id)
    {
        $result = $this->districtService->getDistrict($id);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return view('districts.edit', ['district' => $result['data']]);
    }

    public function update(DistrictRequest $request, int $id)
    {
        $imageFile = $request->hasFile('image') ? $request->file('image') : null;

        $result = $this->districtService->updateDistrict($id, $request->validated(), $imageFile);

        if (isset($result['error'])) {
            return redirect()->back()->withInput()->with('error', $result['error']);
        }

        return redirect()->route('districts.index')->with('success', 'Khu vực đã được cập nhật thành công!');
    }

    public function destroy(int $id)
    {
        $result = $this->districtService->deleteDistrict($id);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->route('districts.index')->with('success', 'Khu vực đã được xoá thành công!');
    }

    public function trash(Request $request)
    {
        $querySearch = (string) $request->get('query', '');
        $sortOption = (string) $request->get('sortOption', '');
        $perPage = (int) $request->get('perPage', 25);

        $result = $this->districtService->getTrashedDistricts($querySearch, $sortOption, $perPage);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return view('districts.trash', ['districts' => $result['data']]);
    }

    public function showTrashed(int $id)
    {
        $result = $this->districtService->getDistrict($id, true);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return view('districts.show-trashed', ['district' => $result['data']]);
    }

    public function restore(int $id)
    {
        $result = $this->districtService->restoreDistrict($id);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->route('districts.trash')->with('success', 'Khu vực đã được khôi phục thành công!');
    }

    public function forceDestroy(int $id)
    {
        $result = $this->districtService->forceDeleteDistrict($id);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->route('districts.trash')->with('success', 'Khu vực đã được xóa vĩnh viễn!');
    }
}
