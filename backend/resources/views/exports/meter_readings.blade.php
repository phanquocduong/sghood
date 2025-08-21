<table>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #366092; color: white;">STT</th>
            <th style="font-weight: bold; background-color: #366092; color: white;">Tên phòng</th>
            <th style="font-weight: bold; background-color: #366092; color: white;">Nhà trọ</th>
            <th style="font-weight: bold; background-color: #366092; color: white;">Tháng</th>
            <th style="font-weight: bold; background-color: #366092; color: white;">Năm</th>
            <th style="font-weight: bold; background-color: #366092; color: white;">Điện (kWh)</th>
            <th style="font-weight: bold; background-color: #366092; color: white;">Nước (m³)</th>
            <th style="font-weight: bold; background-color: #366092; color: white;">Ngày ghi</th>
            <th style="font-weight: bold; background-color: #366092; color: white;">Ghi chú</th>
        </tr>
    </thead>
    <tbody>
        @foreach($meterReadings as $index => $meterReading)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $meterReading->room->name ?? 'N/A' }}</td>
                <td>{{ $meterReading->room->motel->name ?? 'N/A' }}</td>
                <td>{{ $meterReading->month }}</td>
                <td>{{ $meterReading->year }}</td>
                <td>{{ number_format($meterReading->electricity_kwh, 2) }}</td>
                <td>{{ number_format($meterReading->water_m3, 2) }}</td>
                <td>{{ $meterReading->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $meterReading->notes ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>