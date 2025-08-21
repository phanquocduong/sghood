@if(isset($searchSummary) && $searchSummary)
    <div class="alert alert-info mb-3">
        <i class="fas fa-search me-2"></i>
        <strong>Kết quả tìm kiếm cho "{{ $searchSummary['term'] }}":</strong> 
        Tìm thấy {{ number_format($searchSummary['total_found']) }} kết quả, 
        hiển thị {{ $searchSummary['page_showing'] }} trên trang này.
    </div>
@endif

<div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th class="text-center" style="width: 60px;">STT</th>
                <th class="text-center" style="width: 120px;">
                    <i class="fas fa-home me-1"></i>Phòng
                </th>
                <th class="text-center" style="width: 120px;">
                    <i class="fas fa-building me-1"></i>Nhà trọ
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
                        <span class="fw-semibold">{{ $meterReading->room->name }}</span>
                        @if($meterReading->room->room_number)
                            <br><small class="text-muted">{{ $meterReading->room->room_number }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="text-primary fw-semibold">
                            {{ $meterReading->room->motel->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary fs-6">
                            {{ str_pad($meterReading->month, 2, '0', STR_PAD_LEFT) }}/{{ $meterReading->year }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="">
                            <span class="fw-bold text-warning m-1">
                                {{ number_format($meterReading->electricity_kwh, 2) }}
                            </span>
                            <span class="text-muted"> kWh</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="">
                            <span class="fw-bold text-info m-1">
                                {{ number_format($meterReading->water_m3, 2) }}
                            </span>
                            <small class="text-muted">m³</small>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">{{ $meterReading->created_at->format('d/m/Y') }}</span>
                            <small class="text-muted">{{ $meterReading->created_at->format('H:i') }}</small>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>
                            Đã nhập
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không tìm thấy kết quả</h5>
                            <p class="text-muted mb-3">
                                @if(request('search'))
                                    Không tìm thấy chỉ số điện nước nào phù hợp với từ khóa "{{ request('search') }}"
                                @else
                                    Chưa có chỉ số điện nước nào được ghi nhận
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($meterReadings->hasPages())
    <div class="mt-4">
        {{ $meterReadings->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endif