<?php

namespace App\Exports\Templates;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ElectricityMeterTemplateExport implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    private array $columns = [
        'Meter Number *',
        'Meter Type *',
        'Provider Name',
        'Authority Name',
        'Payment Process',
        'Meter Owner',
        'Building Site Code *',
        'Floor Label',
        'Vendor Code',
        'Consumer No',
        'Due Date Day',
        'Sanctioned Load (KW)',
        'Unit Charge Off-Peak (BDT)',
        'Unit Charge Peak (BDT)',
        'Location Notes',
        'Active (Yes/No)',
        'Additional Floor 1',
        'Additional Floor 2',
    ];

    public function title(): string
    {
        return 'Electricity Meters';
    }

    public function array(): array
    {
        return [
            $this->columns,
            $this->exampleRow1(),
            $this->exampleRow2(),
            $this->exampleRow3(),
        ];
    }

    /**
     * Postpaid main meter serving multiple floors.
     */
    private function exampleRow1(): array
    {
        return [
            'MTR-001', 'postpaid_main', 'DESCO', 'DESCO Dhanmondi', 'BEFTN', 'Company',
            'DHK-001', 'Ground Floor', '', '123456789', 15, 50.00,
            8.50, 11.20,
            'Main entrance panel', 'Yes', '1st Floor', '',
        ];
    }

    /**
     * Prepaid meter at a different building.
     */
    private function exampleRow2(): array
    {
        return [
            'MTR-002', 'prepaid', 'DPDC', 'DPDC Kotwali', 'bKash', 'House Owner',
            'CTG-001', '2nd Floor', 'VEN-0002', '987654321', 20, 30.00,
            7.80, 10.50,
            'Basement meter room', 'Yes', '', '',
        ];
    }

    /**
     * Sub-meter within the first building.
     */
    private function exampleRow3(): array
    {
        return [
            'MTR-003', 'postpaid_sub', 'DESCO', 'DESCO Dhanmondi', 'Cheque', 'Company',
            'DHK-001', '1st Floor', 'VEN-0001', '111222333', 10, 25.00,
            8.50, 11.20,
            'Floor junction box', 'Yes', '', '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count($this->columns));

                // Style header row (row 1): bold, white text, blue background
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->freezePane('A2');

                // Dropdown: Meter Type (column 2)
                $this->applyDropdown($sheet, 2, 'postpaid_main,postpaid_sub,prepaid', 2);

                // Dropdown: Active (column 16)
                $this->applyDropdown($sheet, 16, 'Yes,No', 2);
            },
        ];
    }

    /**
     * Apply a dropdown data validation to a column range.
     */
    private function applyDropdown($sheet, int $colNumber, string $options, int $startRow = 2, int $endRow = 100): void
    {
        $col = Coordinate::stringFromColumnIndex($colNumber);
        $validation = $sheet->getCell("{$col}{$startRow}")->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setFormula1('"' . $options . '"');
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);

        for ($row = $startRow + 1; $row <= $endRow; $row++) {
            $sheet->getCell("{$col}{$row}")->setDataValidation(clone $validation);
        }
    }
}
