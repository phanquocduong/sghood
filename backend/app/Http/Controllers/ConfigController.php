<?php

namespace App\Http\Controllers;

use App\Services\ConfigService;
use App\Http\Requests\ConfigRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

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
        // Tạm thời bỏ qua validation, lấy tất cả input
        $data = $request->all();
        $imageFile = $data['config_type'] === 'IMAGE' ? $request->file('config_image') : null;
        $jsonData = null;

        // Debug log để xem dữ liệu gửi lên
        \Log::info('Store Config Debug:', [
            'config_type' => $data['config_type'] ?? 'null',
            'object_data' => $request->input('object_data', []),
            'all_data' => $data
        ]);

        // Xử lý các loại dữ liệu
        if ($data['config_type'] === 'JSON' && $request->has('config_json')) {
            $jsonData = $request->input('config_json');
        } elseif ($data['config_type'] === 'BANK' && $request->has('config_json')) {
            $jsonData = $request->input('config_json');
        } elseif ($data['config_type'] === 'OBJECT' && $request->has('object_data')) {
            // Lấy dữ liệu raw
            $objectData = $request->input('object_data');
            \Log::info('Raw Object Data:', ['object_data' => $objectData]);

            // Xử lý object data thủ công
            $processedObjects = [];

            if (!empty($objectData)) {
                foreach ($objectData as $groupId => $group) {
                    $groupObject = [];

                    if (is_array($group)) {
                        foreach ($group as $keyId => $keyData) {
                            if (
                                isset($keyData['key']) && !empty(trim($keyData['key'])) &&
                                isset($keyData['values']) && is_array($keyData['values'])
                            ) {

                                $key = trim($keyData['key']);
                                $values = array_filter($keyData['values'], function ($value) {
                                    return !empty(trim($value));
                                });

                                if (!empty($values)) {
                                    $values = array_map('trim', $values);

                                    if (count($values) === 1) {
                                        $groupObject[$key] = $values[0];
                                    } else {
                                        $groupObject[$key] = array_values($values);
                                    }
                                }
                            }
                        }
                    }

                    if (!empty($groupObject)) {
                        $processedObjects[] = $groupObject;
                    }
                }
            }

            $jsonData = $processedObjects;
            \Log::info('Processed Object Data:', ['processed' => $jsonData]);
        }

        // Chuẩn bị data cho service (bỏ qua validation)
        $serviceData = [
            'config_key' => $data['config_key'] ?? '',
            'config_type' => $data['config_type'] ?? 'TEXT',
            'config_value' => $data['config_value'] ?? '',
            'description' => $data['description'] ?? null,
        ];

        $result = $this->configService->createConfig($serviceData, $imageFile, $jsonData);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error'])->withInput();
        }

        // ✅ Tự động xóa cache sau khi tạo cấu hình thành công
        $this->clearConfigCache();

        return redirect()->route('configs.index')->with('success', 'Cấu hình đã được tạo thành công và cache đã được xóa!');
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
        // Tạm thời bỏ qua validation
        $data = $request->all();
        $imageFile = $data['config_type'] === 'IMAGE' && $request->hasFile('config_image') ? $request->file('config_image') : null;
        $jsonData = null;

        // Debug log
        \Log::info('Update Config Debug:', [
            'config_type' => $data['config_type'] ?? 'null',
            'object_data' => $request->input('object_data', []),
            'all_data' => $data
        ]);

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

            $jsonData = array_values($jsonArray);
        }
        // Xử lý BANK
        elseif ($data['config_type'] === 'BANK' && $request->has('config_json')) {
            $bankArray = $request->input('config_json');

            $validBanks = array_filter($bankArray, function ($bank) {
                return !empty($bank['value']) && !empty($bank['label']);
            });

            if (empty($validBanks)) {
                return back()->with('error', 'Vui lòng thêm ít nhất một ngân hàng hợp lệ')->withInput();
            }

            $jsonData = array_values($validBanks);
        }
        // Xử lý OBJECT
        elseif ($data['config_type'] === 'OBJECT' && $request->has('object_data')) {
            $objectData = $request->input('object_data');
            \Log::info('Raw Object Data for Update:', ['object_data' => $objectData]);

            // Xử lý object data thủ công (giống như store)
            $processedObjects = [];

            if (!empty($objectData)) {
                foreach ($objectData as $groupId => $group) {
                    $groupObject = [];

                    if (is_array($group)) {
                        foreach ($group as $keyId => $keyData) {
                            if (
                                isset($keyData['key']) && !empty(trim($keyData['key'])) &&
                                isset($keyData['values']) && is_array($keyData['values'])
                            ) {

                                $key = trim($keyData['key']);
                                $values = array_filter($keyData['values'], function ($value) {
                                    return !empty(trim($value));
                                });

                                if (!empty($values)) {
                                    $values = array_map('trim', $values);

                                    if (count($values) === 1) {
                                        $groupObject[$key] = $values[0];
                                    } else {
                                        $groupObject[$key] = array_values($values);
                                    }
                                }
                            }
                        }
                    }

                    if (!empty($groupObject)) {
                        $processedObjects[] = $groupObject;
                    }
                }
            }

            $jsonData = $processedObjects;
            \Log::info('Processed Object Data for Update:', ['processed' => $jsonData]);

            if (empty($jsonData)) {
                return back()->with('error', 'Vui lòng thêm ít nhất một nhóm đối tượng hợp lệ')->withInput();
            }
        }

        // Chuẩn bị data cho service
        $serviceData = [
            'config_key' => $data['config_key'] ?? '',
            'config_type' => $data['config_type'] ?? 'TEXT',
            'config_value' => $data['config_value'] ?? '',
            'description' => $data['description'] ?? null,
        ];

        $result = $this->configService->updateConfig($id, $serviceData, $imageFile, $jsonData);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error'])->withInput();
        }

        // ✅ Tự động xóa cache sau khi cập nhật cấu hình thành công
        $this->clearConfigCache();

        return redirect()->route('configs.index')->with('success', 'Cấu hình đã được cập nhật thành công và cache đã được xóa!');
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

        // ✅ Tự động xóa cache sau khi xóa cấu hình thành công
        $this->clearConfigCache();

        return redirect()->route('configs.index')->with('success', 'Cấu hình đã được xóa thành công và cache đã được xóa!');
    }

    /**
     * ✅ Method riêng để xóa cache
     */
    private function clearConfigCache()
    {
        try {
            // Xóa tất cả các loại cache liên quan đến config
            Artisan::call('cache:clear');           // Xóa application cache
            Artisan::call('config:clear');          // Xóa config cache
            Artisan::call('view:clear');            // Xóa compiled views
            Artisan::call('route:clear');           // Xóa route cache
            Artisan::call('optimize:clear');        // Xóa tất cả cache optimization

            Log::info('Cache đã được xóa thành công sau khi thay đổi cấu hình');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa cache sau khi thay đổi cấu hình: ' . $e->getMessage());
            // Không throw exception để không ảnh hưởng đến quá trình chính
        }
    }

    /**
     * ✅ Endpoint để xóa cache thủ công (tùy chọn - cho admin)
     */
    public function clearCache()
    {
        try {
            $this->clearConfigCache();
            return redirect()->back()->with('success', 'Cache đã được xóa thành công!');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa cache thủ công: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Không thể xóa cache: ' . $e->getMessage());
        }
    }
}