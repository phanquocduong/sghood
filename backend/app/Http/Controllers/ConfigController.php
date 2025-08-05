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

    /**
     * Display a listing of configurations.
     */
    public function index(Request $request)
    {
        $search = (string) $request->query('search', '');
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
        $jsonData = null;

        // Xử lý JSON (OPTION)
        if ($data['config_type'] === 'JSON' && $request->has('config_json')) {
            $jsonArray = $request->input('config_json');
            $jsonArray = array_filter($jsonArray, fn($v) => trim($v) !== '');

            if (empty($jsonArray)) {
                return back()->with('error', 'Vui lòng thêm ít nhất một lựa chọn')->withInput();
            }

            $jsonData = array_values($jsonArray); // Reset array keys
        }
        // Xử lý BANK
        elseif ($data['config_type'] === 'BANK' && $request->has('config_json')) {
            $bankArray = $request->input('config_json');
            
            // Lọc bỏ các bank rỗng
            $validBanks = array_filter($bankArray, function($bank) {
                return !empty($bank['value']) && !empty($bank['label']);
            });

            if (empty($validBanks)) {
                return back()->with('error', 'Vui lòng thêm ít nhất một ngân hàng hợp lệ')->withInput();
            }

            $jsonData = array_values($validBanks); // Reset array keys
        }

        $result = $this->configService->createConfig($data, $imageFile, $jsonData);

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
        $jsonData = null;

        // Xử lý JSON (OPTION)
        if ($data['config_type'] === 'JSON' && $request->has('config_json')) {
            $jsonArray = $request->input('config_json');
            
            // Nếu chuyển từ BANK sang JSON, xử lý conversion
            if (is_array($jsonArray) && isset($jsonArray[0]) && is_array($jsonArray[0])) {
                // Dữ liệu từ BANK format, chuyển đổi thành JSON format
                $convertedArray = [];
                foreach ($jsonArray as $item) {
                    if (is_array($item)) {
                        $value = $item['value'] ?? $item['label'] ?? '';
                        if (!empty(trim($value))) {
                            $convertedArray[] = trim($value);
                        }
                    }
                }
                $jsonArray = $convertedArray;
            } else {
                // Dữ liệu JSON format bình thường
                $jsonArray = array_filter($jsonArray, fn($v) => trim($v) !== '');
            }

            if (empty($jsonArray)) {
                return back()->with('error', 'Vui lòng thêm ít nhất một lựa chọn')->withInput();
            }

            $jsonData = array_values($jsonArray); // Reset array keys
        }
        // Xử lý BANK
        elseif ($data['config_type'] === 'BANK' && $request->has('config_json')) {
            $bankArray = $request->input('config_json');
            
            // Lọc bỏ các bank rỗng
            $validBanks = array_filter($bankArray, function($bank) {
                return !empty($bank['value']) && !empty($bank['label']);
            });

            if (empty($validBanks)) {
                return back()->with('error', 'Vui lòng thêm ít nhất một ngân hàng hợp lệ')->withInput();
            }

            $jsonData = array_values($validBanks); // Reset array keys
        }

        $result = $this->configService->updateConfig($id, $data, $imageFile, $jsonData);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error'])->withInput();
        }

        return redirect()->route('configs.index')->with('success', 'Cấu hình đã được cập nhật thành công!');
    }

    /**
     * Remove the specified configuration from storage.
     */
    public function destroy($id)
    {
        $result = $this->configService->deleteConfig($id);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->route('configs.index')->with('success', 'Cấu hình đã được xóa thành công!');
    }
}