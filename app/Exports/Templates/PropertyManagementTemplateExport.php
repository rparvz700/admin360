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

class PropertyManagementTemplateExport implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    private array $sections;

    public function __construct()
    {
        $this->sections = $this->defineSections();
    }

    public function title(): string
    {
        return 'Property Data';
    }

    private function defineSections(): array
    {
        return [
            [
                'title' => 'Building Info',
                'headerColor' => '4472C4',
                'columnColor' => 'D6E4F0',
                'columns' => [
                    'Site Code *', 'Site Name *', 'Building Code',
                    'Division', 'District', 'Upazila', 'Area', 'Address',
                ],
            ],
            [
                'title' => 'Vendor Info',
                'headerColor' => 'ED7D31',
                'columnColor' => 'FCE4D6',
                'columns' => [
                    'Vendor Code', 'Vendor Name', 'Vendor Phone', 'Vendor Email',
                    'Vendor Address', 'Vendor Bank Name', 'Vendor Account No',
                    'Vendor Routing No', 'Vendor TIN/VAT',
                ],
            ],
            [
                'title' => 'Agreement Info',
                'headerColor' => '7030A0',
                'columnColor' => 'E4D1F0',
                'columns' => [
                    'Agreement Ref No *', 'Agreement Date', 'Payment Start Date',
                    'Expiry Date', 'Agreement Status', 'Agreement Remarks',
                ],
            ],
            [
                'title' => 'Floor Info',
                'headerColor' => '2F5496',
                'columnColor' => 'D6DCE4',
                'columns' => [
                    'Floor Label *', 'Floor Area (SFT)', 'Premises Type',
                    'Car Parking (SFT)', 'DG Space (SFT)', 'Store Space (SFT)',
                    'Project Name', 'Floor Status',
                ],
            ],
            [
                'title' => 'Rent Base',
                'headerColor' => 'C00000',
                'columnColor' => 'F4CCCC',
                'columns' => [
                    'Base Rent', 'VAT (%)', 'Tax (%)', 'At Source (Yes/No)', 'Rent Type',
                ],
            ],
            [
                'title' => 'Rent Increments (up to 5)',
                'headerColor' => 'BF8F00',
                'columnColor' => 'FFF2CC',
                'columns' => $this->buildIncrementColumns(),
            ],
            [
                'title' => 'Security Deposit (up to 2 Adjustable)',
                'headerColor' => '203864',
                'columnColor' => 'C5D9F1',
                'columns' => $this->buildSecurityDepositColumns(),
            ],
            [
                'title' => 'Agreement Utilities (up to 5)',
                'headerColor' => '375623',
                'columnColor' => 'D9E2D5',
                'columns' => $this->buildUtilityColumns(),
            ],
        ];
    }

    private function buildSecurityDepositColumns(): array
    {
        $cols = [
            'SD Total', 'SD Adjustable', 'SD Non-Adjustable',
        ];
        for ($i = 1; $i <= 2; $i++) {
            $cols[] = "Adj {$i} Start Date";
            $cols[] = "Adj {$i} End Date";
            $cols[] = "Adj {$i} Amount";
            $cols[] = "Adj {$i} Percentage";
            $cols[] = "Adj {$i} Frequency";
        }
        return $cols;
    }

    private function buildIncrementColumns(): array
    {
        $cols = [];
        for ($i = 1; $i <= 5; $i++) {
            $cols[] = "Inc {$i} Start Date";
            $cols[] = "Inc {$i} End Date";
            $cols[] = "Inc {$i} Amount";
            $cols[] = "Inc {$i} Percentage";
            $cols[] = "Inc {$i} Incremented Total";
        }
        return $cols;
    }

    private function buildUtilityColumns(): array
    {
        $cols = [];
        for ($i = 1; $i <= 5; $i++) {
            $cols[] = "Utility {$i} Name";
            $cols[] = "Utility {$i} Amount";
        }
        return $cols;
    }

    private function getAllColumns(): array
    {
        $all = [];
        foreach ($this->sections as $section) {
            $all = array_merge($all, $section['columns']);
        }
        return $all;
    }

    private function getTotalColumnCount(): int
    {
        return count($this->getAllColumns());
    }

    private function getSectionHeaderRow(): array
    {
        $row = [];
        foreach ($this->sections as $section) {
            $row[] = $section['title'];
            for ($i = 1; $i < count($section['columns']); $i++) {
                $row[] = '';
            }
        }
        return $row;
    }

    public function array(): array
    {
        return [
            $this->getSectionHeaderRow(),
            $this->getAllColumns(),
            $this->exampleRow1(),
            $this->exampleRow2(),
            $this->exampleRow3(),
        ];
    }

    /**
     * Example row 1: First building, first agreement, first floor — with full rent, increment, deposit, and utility data.
     */
    private function exampleRow1(): array
    {
        return [
            // Section A: Building Info (8)
            'DHK-001', 'Dhaka Head Office', 'BLD-001',
            'Dhaka', 'Dhaka', 'Dhanmondi', 'Dhanmondi 27', '45 Road 27, Dhanmondi',
            // Section B: Vendor Info (9)
            'VEN-0001', 'ABC Properties Ltd', '01711000002', 'abc@email.com',
            'Mirpur Road, Dhaka', 'Dutch Bangla Bank', '9876543210', '987654321', 'TIN-123456',
            // Section C: Agreement Info (6)
            'AGR-2026-001', '2025-12-15', '2026-01-01', '2028-12-31', 'active', '',
            // Section D: Floor Info (8)
            'Ground Floor', 2500, 'Office', 2, 200, 100, 'BR Project', 'active',
            // Section E: Rent Base (5)
            150000, 15, 10, 'No', 'monthly',
            // Section F: Rent Increments 5×5 = 25
            '2027-01-01', '2027-12-31', 15000, 10, 165000,
            '2028-01-01', '2028-12-31', 16500, 10, 181500,
            '', '', '', '', '',
            '', '', '', '', '',
            '', '', '', '', '',
            // Section G: Security Deposit (13)
            500000, 300000, 200000,
            '2026-01-01', '2027-12-31', 3000, '', 'monthly',
            '2028-01-01', '2028-12-31', 2000, '', 'monthly',
            // Section H: Agreement Utilities 5×2 = 10
            'Guard Bill', 5000, 'WASA', 2000, 'Cleaning', 3000, '', '', '', '',
        ];
    }

    /**
     * Example row 2: Same building & agreement, different floor — demonstrates deduplication.
     * Rent/increment/deposit/utility columns are blank because they belong to the same agreement.
     */
    private function exampleRow2(): array
    {
        return [
            // Section A: Building Info (same — will be deduplicated)
            'DHK-001', 'Dhaka Head Office', 'BLD-001',
            'Dhaka', 'Dhaka', 'Dhanmondi', 'Dhanmondi 27', '45 Road 27, Dhanmondi',
            // Section B: Vendor Info (same)
            'VEN-0001', 'ABC Properties Ltd', '01711000002', 'abc@email.com',
            'Mirpur Road, Dhaka', 'Dutch Bangla Bank', '9876543210', '987654321', 'TIN-123456',
            // Section C: Agreement Info (same ref no)
            'AGR-2026-001', '2025-12-15', '2026-01-01', '2028-12-31', 'active', '',
            // Section D: Floor Info (DIFFERENT — this is what makes this row unique)
            '1st Floor', 3000, 'Office', 0, 0, 0, 'BR Project', 'active',
            // Section E–H: blank (same agreement — already defined in row above)
            '', '', '', '', '',
            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            '', '', '', '', '', '', '', '', '', '', '', '', '',
            '', '', '', '', '', '', '', '', '', '',
        ];
    }

    /**
     * Example row 3: Completely different building, vendor, agreement, floor.
     */
    private function exampleRow3(): array
    {
        return [
            // Section A: Building Info
            'CTG-001', 'Chittagong Branch', 'BLD-002',
            'Chittagong', 'Chittagong', 'Kotwali', 'Agrabad', '78 CDA Avenue, Agrabad',
            // Section B: Vendor Info
            'VEN-0002', 'XYZ Realty', '01811000002', 'xyz@email.com',
            'OR Nizam Road, Ctg', 'City Bank', '1111122222', '111112222', 'TIN-987654',
            // Section C: Agreement Info
            'AGR-2026-002', '2026-02-01', '2026-03-01', '2029-02-28', 'active', '',
            // Section D: Floor Info
            '2nd Floor', 1800, 'Office', 1, 0, 50, 'BR Project', 'active',
            // Section E: Rent Base
            80000, 15, 10, 'No', 'monthly',
            // Section F: Rent Increments (1 of 5)
            '2027-03-01', '2028-02-28', 8000, 10, 88000,
            '', '', '', '', '',
            '', '', '', '', '',
            '', '', '', '', '',
            '', '', '', '', '',
            // Section G: Security Deposit (1 of 2 Adjustable)
            200000, 120000, 80000,
            '2026-03-01', '2029-02-28', 10000, '', 'yearly',
            '', '', '', '', '',
            // Section H: Agreement Utilities (1 of 5)
            'Guard Bill', 3000, '', '', '', '', '', '', '', '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $colIndex = 1;

                // Style each section: merge + color section headers (row 1) and column headers (row 2)
                foreach ($this->sections as $section) {
                    $colCount = count($section['columns']);
                    $startCol = Coordinate::stringFromColumnIndex($colIndex);
                    $endCol = Coordinate::stringFromColumnIndex($colIndex + $colCount - 1);

                    // Merge section header cells
                    if ($colCount > 1) {
                        $sheet->mergeCells("{$startCol}1:{$endCol}1");
                    }

                    // Section header style (row 1): bold, white text, colored background
                    $sheet->getStyle("{$startCol}1:{$endCol}1")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $section['headerColor']]],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Column header style (row 2): bold, light colored background, wrap text
                    $sheet->getStyle("{$startCol}2:{$endCol}2")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $section['columnColor']]],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);

                    $colIndex += $colCount;
                }

                // Row heights
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(30);

                // Freeze panes below the two header rows
                $sheet->freezePane('A3');

                // Borders on header rows
                $lastCol = Coordinate::stringFromColumnIndex($this->getTotalColumnCount());
                $sheet->getStyle("A1:{$lastCol}2")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // Data validation: "At Source (Yes/No)" — column index 35
                // (A:8 + B:9 + C:6 + D:8 + E:4th col = 8+9+6+8+3+1 = 35)
                $this->applyDropdown($sheet, 35, 'Yes,No');
            },
        ];
    }

    /**
     * Apply a dropdown data validation to a column range.
     */
    private function applyDropdown($sheet, int $colNumber, string $options, int $startRow = 3, int $endRow = 100): void
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
