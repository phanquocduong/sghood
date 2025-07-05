<table class="table table-hover table-bordered align-middle">
    <thead class="table-success">
        <tr>
            <th class="text-center">STT</th>
            <th class="text-center">Phòng</th>
            <th class="text-center">Tháng/Năm</th>
            <th class="text-center">Điện (kWh)</th>
            <th class="text-center">Nước (m³)</th>
            <th class="text-center">Ngày ghi</th>
            <th class="text-center">Thao tác</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($meterReadings as $index => $meterReading)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $meterReading->room->name }}</td>
                <td class="text-center">{{ $meterReading->month }}/{{ $meterReading->year }}</td>
                <td class="text-center">{{ number_format($meterReading->electricity_kwh, 2) }} kWh</td>
                <td class="text-center">{{ number_format($meterReading->water_m3, 2) }} m³</td>
                <td class="text-center">{{ $meterReading->created_at->format('d/m/Y') }}</td>
                <td class="text-center">
                    <button class="btn btn-danger btn-sm">
                        <i class="fas fa-lock"></i> Đã cập nhật
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
            </tr>
        @endforelse
    </tbody>
</table>
{{ $meterReadings->links('pagination::bootstrap-4') }}

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('filterForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const query = new URLSearchParams(formData).toString();

            fetch("{{ route('meter_readings.filter') }}?" + query, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => res.json())
                .then(data => {
                    document.getElementById('displayResults').innerHTML = data.html;
                })
                .catch(err => console.error('Filter error:', err));
        });
    });
</script>
