<?php

namespace App\Http\Controllers;

use App\Services\ConfigService;
use App\Http\Requests\ConfigRequest;
use Illuminate\Http\Request;

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
        $data = $request->validated();
        $result = $this->configService->createConfig($data);

        if (isset($result['error'])) {
            return redirect()->route('configs.index')->with('error', $result['error']);
        }

        return redirect()->route('configs.index')->with('success', 'Cấu hình đã được thêm thành công!');
    }

    public function edit($id)
    {
        $config = $this->configService->getConfigById($id);
        return view('configs.edit', compact('config'));
    }

    public function update(ConfigRequest $request, $id)
    {
        $data = $request->validated();
        $result = $this->configService->updateConfig($id, $data);

        if (isset($result['error'])) {
            return redirect()->route('configs.index')->with('error', $result['error']);
        }

        return redirect()->route('configs.index')->with('success', 'Cấu hình đã được cập nhật thành công!');
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