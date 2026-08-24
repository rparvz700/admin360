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

class AssetManagementTemplateExport implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    private array $sections;

    public function __construct()
    {
        $this->sections = $this->defineSections();
    }

    public function title(): string
    {
        return 'Asset Data';
    }

    private function defineSections(): array
    {
        return [
            [
                'title' => 'Asset Info',
                'headerColor' => '2E75B6',
                'columnColor' => 'D6E4F0',
                'columns' => [
                    'Asset Tag *', 'Asset Name *', 'Category Name *',
                    'Brand', 'Model', 'Serial Number',
                    'Purchase Date', 'Warranty Expiry',
                    'Building Site Code', 'Floor Label', 'Location Within Floor',
                    'Parent Asset Tag', 'Status', 'Project Name',
                ],
            ],
            [
                'title' => 'Dynamic Attributes (up to 5)',
                'headerColor' => '548235',
                'columnColor' => 'E2EFDA',
                'columns' => $this->buildAttributeColumns(),
            ],
        ];
    }

    private function buildAttributeColumns(): array
    {
        $cols = [];
        for ($i = 1; $i <= 5; $i++) {
            $cols[] = "Attribute {$i} Name";
            $cols[] = "Attribute {$i} Value";
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
     * AC outdoor unit — standalone parent asset with dynamic attributes.
     */
    private function exampleRow1(): array
    {
        return [
            // Asset Info (14)
            'AC-001', 'Split AC Outdoor Unit', 'AC', 'Gree', 'GS-18NFA',
            'SN-AC-001', '2025-06-15', '2027-06-15',
            'DHK-001', '4th Floor', 'Room 401', '', 'active', 'BR Project',
            // Dynamic Attributes 5×2 = 10
            'BTU', '18000', 'Tonnage', '1.5 Ton', 'Refrigerant', 'R32',
            '', '', '', '',
        ];
    }

    /**
     * AC indoor unit — child of AC-001 (demonstrates parent-child hierarchy).
     */
    private function exampleRow2(): array
    {
        return [
            'AC-002', 'Split AC Indoor Unit', 'AC', 'Gree', 'GI-18NFA',
            'SN-AC-002', '2025-06-15', '2027-06-15',
            'DHK-001', '4th Floor', 'Room 401', 'AC-001', 'active', 'BR Project',
            'BTU', '18000', '', '', '', '', '', '', '', '',
        ];
    }

    /**
     * WiFi router — different category with its own attributes.
     */
    private function exampleRow3(): array
    {
        return [
            'RTR-001', 'WiFi Router', 'Router', 'TP-Link', 'Archer C6',
            'SN-RTR-001', '2025-03-10', '2027-03-10',
            'DHK-001', 'Ground Floor', 'Reception', '', 'active', 'BR Project',
            'Speed', '1200Mbps', 'Ports', '4', 'WiFi Standard', 'WiFi 5',
            '', '', '', '',
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

                    // Section header style (row 1)
                    $sheet->getStyle("{$startCol}1:{$endCol}1")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $section['headerColor']]],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Column header style (row 2)
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

                // Freeze panes below header rows
                $sheet->freezePane('A3');

                // Borders on header rows
                $lastCol = Coordinate::stringFromColumnIndex($this->getTotalColumnCount());
                $sheet->getStyle("A1:{$lastCol}2")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // Dropdown: Status (column 13 within Asset Info)
                $this->applyDropdown($sheet, 13, 'active,repair,retired');
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
