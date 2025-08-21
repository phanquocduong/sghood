<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MeterReadingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $meterReadings;

    public function __construct($meterReadings)
    {
        $this->meterReadings = $meterReadings;
    }

    public function collection()
    {
        return $this->meterReadings;
    }

    public function headings(): array
    {
        return [
            'STT',
            'Tên phòng',
            'Nhà trọ',
            'Tháng',
            'Năm',
            'Điện (kWh)',
            'Nước (m³)',
            'Ngày ghi',
            'Ghi chú'
        ];
    }

    public function map($meterReading): array
    {
        static $index = 1;
        
        return [
            $index++,
            $meterReading->room->name ?? 'N/A',
            $meterReading->room->motel->name ?? 'N/A',
            $meterReading->month,
            $meterReading->year,
            (float) $meterReading->electricity_kwh,
            (float) $meterReading->water_m3,
            $meterReading->created_at->format('d/m/Y H:i'),
            $meterReading->notes ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header row styling
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '366092']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000000']
                ]
            ]
        ]);

        // Data rows styling
        $lastRow = $this->meterReadings->count() + 1;
        $sheet->getStyle("A2:I{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'CCCCCC']
                ]
            ]
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // STT
            'B' => 20,  // Tên phòng
            'C' => 25,  // Nhà trọ
            'D' => 8,   // Tháng
            'E' => 8,   // Năm
            'F' => 15,  // Điện
            'G' => 15,  // Nước
            'H' => 18,  // Ngày ghi
            'I' => 30,  // Ghi chú
        ];
    }

    public function title(): string
    {
        return 'Lịch sử chỉ số điện nước';
    }
}