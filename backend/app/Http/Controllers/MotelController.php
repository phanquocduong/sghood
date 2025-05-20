<?php
namespace App\Http\Controllers;

use App\Services\MotelService;
use App\Http\Requests\MotelRequest;
use Illuminate\Http\Request;

class MotelController extends Controller
{
    protected $motelService;

    public function __construct(MotelService $motelService)
    {
        $this->motelService = $motelService;
    }

    public function index(Request $request)
    {
        $querySearch = $request->get('query', '');
        $status = $request->get('status', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->motelService->getAvailableMotels($querySearch, $status, $sortOption, $perPage);
        return view('motels.index', ['motels' => $result['data']]);
    }

    // public function show(int $id)
    // {
    //     $result = $this->motelService->getMotel($id);
    //     return view('motels.show', ['motel' => $result['data']]);
    // }
    public function show()
    {
        // $result = $this->motelService->getMotel($id);
        return view('motels.show');
    }

    public function create()
    {
        return view('motels.create');
    }

    public function store(MotelRequest $request)
    {
        $result = $this->motelService->createMotel($request->validated(), $request->file('images'));
        return redirect()->route('motels.index')->with('message', 'Nhà trọ đã được tạo thành công!');
    }

    public function edit(int $id)
    {
        $result = $this->motelService->getMotel($id);
        return view('motels.edit', ['motel' => $result['data']]);
    }

    public function update(MotelRequest $request, int $id)
    {
        $imageFiles = $request->hasFile('images') ? $request->file('images') : [];
        $result = $this->motelService->updateMotel($id, $request->validated(), $imageFiles);
        return redirect()->route('motels.index')->with('message', 'Nhà trọ đã được cập nhật thành công!');
    }

    public function destroy(int $id)
    {
        $this->motelService->deleteMotel($id);
        return redirect()->route('motels.index')->with('message', 'Nhà trọ đã được xóa thành công!');
    }

    public function trash(Request $request)
    {
        $querySearch = $request->get('query', '');
        $status = $request->get('status', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->motelService->getTrashedMotels($querySearch, $status, $sortOption, $perPage);
        return view('motels.trash', ['motels' => $result['data']]);
    }

    public function showTrashed(int $id)
    {
        $result = $this->motelService->getMotel($id, true);
        return view('motels.show-trashed', ['motel' => $result['data']]);
    }

    public function restore(int $id)
    {
        $this->motelService->restoreMotel($id);
        return redirect()->route('motels.trash')->with('message', 'Nhà trọ đã được khôi phục thành công!');
    }

    public function forceDestroy(int $id)
    {
        $this->motelService->forceDeleteMotel($id);
        return redirect()->route('motels.trash')->with('message', 'Nhà trọ đã được xóa vĩnh viễn!');
    }
}