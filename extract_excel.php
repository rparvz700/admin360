<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$dir = __DIR__ . '/public/media/360';
$files = [
    "Advance Requisition BR Bill - JHD (2)(AutoRecovered).xlsx",
    "Advance Requisition June -2026.xlsx",
    "Beftn_1719125653 (2).xlsx",
    "Cheque_1696837887.xlsx",
    "Copy of RIO2-BEFTN Ebill Process and Prepaid Meter Recharge Req Format.xlsx",
    "Pre Paid Meter.xlsx",
    "Soft copy of E.bill RIO 1.xlsx"
];

foreach ($files as $file) {
    echo "========================================================\n";
    echo "File: $file\n";
    echo "========================================================\n";
    $path = $dir . '/' . $file;
    if (!file_exists($path)) {
        echo "File not found!\n";
        continue;
    }

    try {
        $spreadsheet = IOFactory::load($path);
        $sheetNames = $spreadsheet->getSheetNames();
        echo "Sheet Names: " . implode(", ", $sheetNames) . "\n\n";

        foreach ($sheetNames as $sheetName) {
            echo "--- Sheet: $sheetName ---\n";
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $maxRow = $sheet->getHighestDataRow();
            $maxCol = $sheet->getHighestDataColumn();
            
            $limit = min(15, $maxRow);
            for ($row = 1; $row <= $limit; $row++) {
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $maxCol . $row, NULL, TRUE, FALSE);
                echo "Row $row: " . json_encode($rowData[0], JSON_UNESCAPED_UNICODE) . "\n";
            }
            echo "\n";
        }
    } catch (\Exception $e) {
        echo "Error loading file: " . $e->getMessage() . "\n";
    }
}
