<?php

namespace App\Http\Controllers;

use App\Services\ConfigService;
use App\Http\Requests\ConfigRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConfigController extends Controller
{
    protected $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $this->configService = $configService;
    }

    /**
     * Display a listing of configurations.
     */
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $configs = $this->configService->getConfigs($search);

        return view('configs.index', compact('configs'));
    }

    /**
     * Show the form for creating a new configuration.
     */
    public function create()
    {
        return view('configs.create');
    }

    /**
     * Store a newly created configuration.
     */
    public function store(ConfigRequest $request)
    {
        $data = $request->validated();
        $imageFile = $data['config_type'] === 'IMAGE' ? $request->file('config_image') : null;

        $result = $this->configService->createConfig($data, $imageFile);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error'])->withInput();
        }

        return redirect()->route('configs.index')->with('success', 'Cấu hình đã được tạo thành công!');
    }

    /**
     * Show the form for editing a configuration.
     */
    public function edit($id)
    {
        $config = $this->configService->getConfigById($id);
        return view('configs.edit', compact('config'));
    }

    /**
     * Update an existing configuration.
     */
    public function update(ConfigRequest $request, $id)
    {
        $data = $request->validated();
        $imageFile = $data['config_type'] === 'IMAGE' && $request->hasFile('config_image') ? $request->file('config_image') : null;

        $result = $this->configService->updateConfig($id, $data, $imageFile);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error'])->withInput();
        }

        return redirect()->route('configs.index')->with('success', 'Cấu hình đã được cập nhật thành công!');
    }

    /**
     * Soft delete a configuration.
     */
    public function destroy($id)
    {
        $result = $this->configService->deleteConfig($id);

        if (isset($result['error'])) {
            return redirect()->route('configs.index')->with('error', $result['error']);
        }

        return redirect()->route('configs.index')->with('success', 'Cấu hình đã được xóa thành công!');
    }

    /**
     * Display trashed configurations.
     */
    public function trash(Request $request)
    {
        $search = $request->query('search', '');
        $configs = $this->configService->getTrashedConfigs($search);

        return view('configs.trash', compact('configs'));
    }

    /**
     * Restore a soft-deleted configuration.
     */
    public function restore($id)
    {
        $result = $this->configService->restoreConfig($id);

        if (isset($result['error'])) {
            return redirect()->route('configs.trash')->with('error', $result['error']);
        }

        return redirect()->route('configs.trash')->with('success', 'Cấu hình đã được khôi phục thành công!');
    }

    /**
     * Permanently delete a configuration.
     */
    public function forceDelete($id)
    {
        $result = $this->configService->forceDeleteConfig($id);

        if (isset($result['error'])) {
            return redirect()->route('configs.trash')->with('error', $result['error']);
        }

        return redirect()->route('configs.trash')->with('success', 'Cấu hình đã được xóa vĩnh viễn!');
    }
}