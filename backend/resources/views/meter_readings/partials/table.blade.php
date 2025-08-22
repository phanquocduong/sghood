<div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th class="text-center" style="width: 60px;">STT</th>
                <th class="text-center" style="width: 120px;">
                    <i class="fas fa-home me-1"></i>Phòng
                </th>
                <th class="text-center" style="width: 100px;">
                    <i class="fas fa-calendar me-1"></i>Tháng/Năm
                </th>
                <th class="text-center" style="width: 120px;">
                    <i class="fas fa-bolt me-1 text-warning"></i>Điện (kWh)
                </th>
                <th class="text-center" style="width: 120px;">
                    <i class="fas fa-tint me-1 text-info"></i>Nước (m³)
                </th>
                <th class="text-center" style="width: 120px;">
                    <i class="fas fa-clock me-1"></i>Ngày ghi
                </th>
                <th class="text-center" style="width: 120px;">Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($meterReadings as $index => $meterReading)
            <tr class="table-row-hover">
                <td class="text-center fw-bold">
                    {{ ($meterReadings->currentPage() - 1) * $meterReadings->perPage() + $index + 1 }}
                </td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="badge bg-primary me-2">{{ $meterReading->room->room_number ?? 'N/A' }}</div>
                        <span class="fw-semibold">{{ $meterReading->room->name }}</span>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary fs-6">
                        {{ str_pad($meterReading->month, 2, '0', STR_PAD_LEFT) }}/{{ $meterReading->year }}
                    </span>
                </td>
                <td class="text-center">
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-warning">
                            {{ number_format($meterReading->electricity_kwh, 2) }}
                        </span>
                        <small class="text-muted">kWh</small>
                    </div>
                </td>
                <td class="text-center">
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-info">
                            {{ number_format($meterReading->water_m3, 2) }}
                        </span>
                        <small class="text-muted">m³</small>
                    </div>
                </td>
                <td class="text-center">
                    <div class="d-flex flex-column">
                        <span class="fw-semibold">{{ $meterReading->created_at->format('d/m/Y') }}</span>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-success">
                        <i class="fas fa-check me-1"></i>
                        Đã xác nhận
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Không có dữ liệu</h5>
                        <p class="text-muted mb-3">Chưa có chỉ số điện nước nào được ghi nhận</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($meterReadings->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Hiển thị {{ $meterReadings->firstItem() ?? 0 }} - {{ $meterReadings->lastItem() ?? 0 }} 
        trong tổng số {{ $meterReadings->total() }} bản ghi
    </div>
    <div>
        {{ $meterReadings->links() }}
    </div>
</div>
@endif