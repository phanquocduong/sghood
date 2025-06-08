<?php

namespace App\Http\Controllers;

use App\Services\ConfigService;
use App\Http\Requests\ConfigRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    protected $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $configs = $this->configService->getConfigs($search);

        return view('configs.index', compact('configs'));
    }

    public function create()
    {
        return view('configs.create');
    }

    public function store(ConfigRequest $request)
    {
        try {
            // Use validated data from ConfigRequest
            $data = $request->validated();

            // Handle image or text value
            if ($request->input('config_type') === 'IMAGE') {
                $imageFile = $request->file('config_image');
                if (!$imageFile) {
                    return redirect()->back()->with('error', 'Vui lòng tải lên file ảnh!')->withInput();
                }
                $result = $this->configService->storeConfig($data, $imageFile);
            } else {
                $result = $this->configService->storeConfig($data);
            }

            if (isset($result['error'])) {
                return redirect()->back()->with('error', $result['error'])->withInput();
            }

            return redirect()->route('configs.index')->with('success', 'Cấu hình đã được tạo thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $config = $this->configService->getConfigById($id);
        return view('configs.edit', compact('config'));
    }

    public function update(ConfigRequest $request, $id)
    {
        try {
            // Use validated data from ConfigRequest
            $data = $request->validated();

            // Handle image or text value
            if ($request->input('config_type') === 'IMAGE') {
                $imageFile = $request->hasFile('config_image') ? $request->file('config_image') : null;
                $result = $this->configService->updateConfig($id, $data, $imageFile);
            } else {
                $data['config_value'] = $request->input('config_value');
                $result = $this->configService->updateConfig($id, $data);
            }

            if (isset($result['error'])) {
                return redirect()->back()->with('error', $result['error'])->withInput();
            }

            return redirect()->route('configs.index')->with('success', 'Cấu hình đã được cập nhật thành công!');
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật cấu hình: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $result = $this->configService->deleteConfig($id);

        if (isset($result['error'])) {
            return redirect()->route('configs.index')->with('error', $result['error']);
        }

        return redirect()->route('configs.index')->with('success', 'Cấu hình đã được xóa thành công!');
    }

    public function trash(Request $request)
    {
        $search = $request->query('search', '');
        $configs = $this->configService->getTrashedConfigs($search);

        return view('configs.trash', compact('configs'));
    }

    public function restore($id)
    {
        $result = $this->configService->restoreConfig($id);

        if (isset($result['error'])) {
            return redirect()->route('configs.trash')->with('error', $result['error']);
        }

        return redirect()->route('configs.trash')->with('success', 'Cấu hình đã được khôi phục thành công!');
    }

    public function forceDelete($id)
    {
        $result = $this->configService->forceDeleteConfig($id);

        if (isset($result['error'])) {
            return redirect()->route('configs.trash')->with('error', $result['error']);
        }

        return redirect()->route('configs.trash')->with('success', 'Cấu hình đã được xóa vĩnh viễn!');
    }
}