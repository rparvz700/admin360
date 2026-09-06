<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('media/import/driver_data.xlsx');

        if (!file_exists($filePath)) {
            $this->command?->error("File not found: {$filePath}");
            return;
        }

        $this->command?->info("Loading driver data from: {$filePath}");

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

        // Helper to clean null/empty values including MySQL '\N' export strings
        $cleanVal = function ($val) {
            if ($val === null) {
                return null;
            }
            $val = trim((string)$val);
            if ($val === '' || $val === '\N' || strtolower($val) === 'null') {
                return null;
            }
            return $val;
        };

        // Helper to format HR ID (if less than 4 characters, add leading 0)
        $formatHrId = function ($hrId) use ($cleanVal) {
            $hrId = $cleanVal($hrId);
            if (!$hrId) {
                return null;
            }
            if (strlen($hrId) < 4) {
                $hrId = str_pad($hrId, 4, '0', STR_PAD_LEFT);
            }
            return $hrId;
        };

        // Helper to format BD phone / contact numbers
        $formatPhone = function ($phone) use ($cleanVal) {
            $phone = $cleanVal($phone);
            if (!$phone) {
                return null;
            }
            // Add leading 0 for 10-digit numbers starting with 1
            if (preg_match('/^1[3-9]\d{8}$/', $phone)) {
                return '0' . $phone;
            }
            return $phone;
        };

        // Helper to format NID (handles numbers that might be in scientific notation)
        $formatNid = function ($nid) use ($cleanVal) {
            $nid = $cleanVal($nid);
            if (!$nid) {
                return null;
            }
            if (stripos($nid, 'E+') !== false) {
                $nid = sprintf('%.0f', (float)$nid);
            }
            return $nid;
        };

        // Helper to format dates to Y-m-d
        $formatDate = function ($dateStr) use ($cleanVal) {
            $dateStr = $cleanVal($dateStr);
            if (!$dateStr || $dateStr === '0000-00-00') {
                return null;
            }
            try {
                return (new \DateTime($dateStr))->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $importedCount = 0;
        $updatedCount = 0;

        foreach ($rows as $rowIndex => $row) {
            $getVal = function ($field) use ($row, $headerMap, $cleanVal) {
                $key = $headerMap[$field] ?? null;
                if (!$key || !isset($row[$key])) {
                    return null;
                }
                return $cleanVal($row[$key]);
            };

            $rawHrId = $getVal('hr_id');
            if (empty($rawHrId)) {
                continue;
            }
            $hrId = $formatHrId($rawHrId);

            $name = $getVal('name');
            if (empty($name)) {
                continue;
            }

            $contractRenewed = in_array(
                strtolower((string)$getVal('contract_renewed')),
                ['1', 'true', 'yes'],
                true
            );

            $driverData = [
                'hr_id'                 => $hrId,
                'name'                  => $name,
                'sur_name'              => $getVal('sur_name'),
                'joining_date'          => $formatDate($getVal('joining_date')),
                'employment_contract'   => $getVal('employment_contract'),
                'gender'                => $getVal('gender'),
                'email'                 => $getVal('email'),
                'phone'                 => $formatPhone($getVal('phone')),
                'blood_group'           => $getVal('blood_group'),
                'marital_status'        => $getVal('marital_status'),
                'date_of_birth'         => $formatDate($getVal('date_of_birth')),
                'contract_renewed'      => $contractRenewed,
                'confirmation_date'     => $formatDate($getVal('confirmation_date')),
                'contract_end_date'     => $formatDate($getVal('contract_end_date')),
                'passport_no'           => $getVal('passport_no'),
                'designation'           => $getVal('designation'),
                'department'            => $getVal('department'),
                'division'              => $getVal('division'),
                'office_location'       => $getVal('office_location'),
                'subcenter'             => $getVal('subcenter'),
                'job_location'          => $getVal('job_location'),
                'supervisor_name'       => $getVal('supervisor_name'),
                'supervisor_email'      => $getVal('supervisor_email'),
                'supervisor_hr_id'      => $formatHrId($getVal('supervisor_hr_id')),
                'supervisor_company'    => $getVal('supervisor_company'),
                'bill_reviewer_name'    => $getVal('bill_reviewer_name'),
                'bill_reviewer_email'   => $getVal('bill_reviewer_email'),
                'bill_reviewer_hr_id'   => $formatHrId($getVal('bill_reviewer_hr_id')),
                'bill_reviewer_company' => $getVal('bill_reviewer_company'),
                'nid'                   => $formatNid($getVal('nid')),
                'emergency_contact'     => $formatPhone($getVal('emergency_contact')),
            ];

            $driver = Driver::where('hr_id', $hrId)
                ->when($rawHrId !== $hrId, function ($q) use ($rawHrId) {
                    $q->orWhere('hr_id', $rawHrId);
                })
                ->first();

            if ($driver) {
                $driver->update($driverData);
                $updatedCount++;
            } else {
                Driver::create($driverData);
                $importedCount++;
            }
        }

        $this->command?->info("Driver import completed. Inserted: {$importedCount}, Updated: {$updatedCount}");
    }
}
