<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\VehicleType;
use PhpOffice\PhpSpreadsheet\IOFactory;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('media/import/vehicle_data.xlsx');

        if (!file_exists($filePath)) {
            $this->command?->error("File not found: {$filePath}");
            return;
        }

        $this->command?->info("Loading vehicle data from: {$filePath}");

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            $this->command?->warn("No data found in {$filePath}");
            return;
        }

        // First row is the header row
        $headers = array_shift($rows);

        // Normalize header mapping: lowercase trim header name => column key (A, B, C...)
        $headerMap = [];
        foreach ($headers as $colKey => $headerName) {
            if ($headerName !== null && trim((string)$headerName) !== '') {
                $headerMap[strtolower(trim((string)$headerName))] = $colKey;
            }
        }

        // Explicit mapping from model text to vehicle_types.type_name
        $modelToTypeMap = [
            'Sedan'                       => 'Sedan',
            'Jeep'                        => 'Jeep',
            'Microbus: Noah'              => 'Microbus: Noah',
            'Microbus: Hiace (16 Seated)' => 'Microbus: Hiace (16 Seated)',
            'Microbus: Hiace'             => 'Microbus: Hiace',
            'Microbus: X Noah'            => 'Microbus: X Noah',
            'Pickup: Double Cabin'        => 'Pickup: Double Cabin',
            'Pickup: Single Cabin'        => 'Pickup: Single Cabin',
            'Van: Delivery'               => 'Van: Delivery',
            'Van: Cover'                  => 'Van: Cover',
            'HDD Mover-Lowbad'            => 'HDD Mover-Lowbad',
            'Forklift'                    => 'Forklift',
        ];

        // Cache existing vehicle types
        $vehicleTypeCache = [];
        foreach (VehicleType::all() as $vt) {
            $vehicleTypeCache[strtolower(trim($vt->type_name))] = $vt->id;
        }

        $importedCount = 0;
        $updatedCount = 0;

        foreach ($rows as $rowIndex => $row) {
            $getVal = function ($field) use ($row, $headerMap) {
                $key = $headerMap[$field] ?? null;
                if (!$key || !isset($row[$key])) {
                    return null;
                }
                $val = trim((string)$row[$key]);
                return $val !== '' ? $val : null;
            };

            $registrationNumber = $getVal('registration_number');
            if (empty($registrationNumber)) {
                continue;
            }

            $modelText = $getVal('model');

            // Map model to vehicle_type_id
            if ($modelText && isset($modelToTypeMap[$modelText])) {
                $typeName = $modelToTypeMap[$modelText];
            } elseif ($modelText && str_contains($modelText, ':')) {
                $typeName = trim(explode(':', $modelText)[0]);
            } elseif ($modelText) {
                $typeName = trim($modelText);
            } else {
                $typeName = 'Other';
            }

            // Find or create VehicleType
            $typeKey = strtolower($typeName);
            if (!isset($vehicleTypeCache[$typeKey])) {
                $newType = VehicleType::firstOrCreate(['type_name' => $typeName]);
                $vehicleTypeCache[$typeKey] = $newType->id;
            }
            $vehicleTypeId = $vehicleTypeCache[$typeKey];

            // Parse purchase_date
            $purchaseDateRaw = $getVal('purchase_date');
            $purchaseDate = null;
            if ($purchaseDateRaw) {
                try {
                    $purchaseDate = (new \DateTime($purchaseDateRaw))->format('Y-m-d');
                } catch (\Throwable $e) {
                    $purchaseDate = null;
                }
            }

            // Parse isRented
            $isRentedRaw = $getVal('isrented');
            $isRented = false;
            if ($isRentedRaw !== null) {
                $isRented = in_array(strtolower($isRentedRaw), ['1', 'true', 'yes'], true);
            }

            // Parse numeric fields
            $mfgYear = $getVal('manufacture_year');
            $manufactureYear = $mfgYear !== null ? (int)$mfgYear : null;

            $seats = $getVal('seating_capacity');
            $seatingCapacity = $seats !== null ? (int)$seats : null;

            $engineCcRaw = $getVal('engine_cc');
            $engineCc = $engineCcRaw !== null ? (int)$engineCcRaw : null;

            $price = $getVal('purchase_price');
            $purchasePrice = $price !== null ? (float)$price : null;

            $vehicleData = [
                'vehicle_type_id'  => $vehicleTypeId,
                'brand'            => $getVal('brand'),
                'model'            => $modelText, // Kept as text in model column
                'manufacture_year' => $manufactureYear,
                'color'            => $getVal('color'),
                'seating_capacity' => $seatingCapacity,
                'engine_number'    => $getVal('engine_number'),
                'chassis_number'   => $getVal('chassis_number'),
                'engine_cc'        => $engineCc,
                'use_purpose'      => $getVal('use_purpose'),
                'use_company'      => $getVal('use_company'),
                'isRented'         => $isRented,
                'purchase_price'   => $purchasePrice,
                'purchase_date'    => $purchaseDate,
                'status'           => 'active',
            ];

            $vehicle = Vehicle::where('registration_number', $registrationNumber)->first();
            if ($vehicle) {
                $vehicle->update($vehicleData);
                $updatedCount++;
            } else {
                $vehicleData['registration_number'] = $registrationNumber;
                Vehicle::create($vehicleData);
                $importedCount++;
            }
        }

        $this->command?->info("Vehicle import completed. Inserted: {$importedCount}, Updated: {$updatedCount}");
    }
}
