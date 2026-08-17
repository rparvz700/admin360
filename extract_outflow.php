<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$path = __DIR__ . '/public/media/360/finance/outflow.xlsx';

if (!file_exists($path)) {
    echo "ERROR: File not found at: $path\n";
    exit(1);
}

echo "File found: $path\n";
echo "File size: " . filesize($path) . " bytes\n\n";

try {
    $spreadsheet = IOFactory::load($path);
    $sheetNames = $spreadsheet->getSheetNames();
    echo "Total Sheets: " . count($sheetNames) . "\n";
    echo "Sheet Names: " . implode(", ", $sheetNames) . "\n\n";

    foreach ($sheetNames as $sheetName) {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        $maxRow = $sheet->getHighestDataRow();
        $maxCol = $sheet->getHighestDataColumn();
        
        echo "================================================================\n";
        echo "SHEET: \"$sheetName\"\n";
        echo "Rows: $maxRow, Columns: up to $maxCol\n";
        echo "================================================================\n\n";

        // Print ALL rows (not just 15)
        for ($row = 1; $row <= $maxRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $maxCol . $row, NULL, TRUE, FALSE);
            // Filter out completely empty rows
            $hasData = false;
            foreach ($rowData[0] as $cell) {
                if ($cell !== null && $cell !== '') {
                    $hasData = true;
                    break;
                }
            }
            if ($hasData) {
                echo "Row $row: " . json_encode($rowData[0], JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
        echo "\n";

        // Also extract formulas
        echo "--- FORMULAS in \"$sheetName\" ---\n";
        $colRange = range('A', $maxCol);
        $formulaCount = 0;
        for ($row = 1; $row <= $maxRow; $row++) {
            foreach ($colRange as $col) {
                $cell = $sheet->getCell($col . $row);
                if ($cell->isFormula()) {
                    $formulaCount++;
                    echo "  $col$row: " . $cell->getValue() . " => " . $cell->getCalculatedValue() . "\n";
                }
            }
        }
        if ($formulaCount === 0) {
            echo "  (No formulas found)\n";
        }
        echo "\n\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
