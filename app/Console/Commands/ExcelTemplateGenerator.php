<?php

namespace App\Console\Commands;

use App\Exports\Templates\AssetManagementTemplateExport;
use App\Exports\Templates\ElectricityMeterTemplateExport;
use App\Exports\Templates\PropertyManagementTemplateExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ExcelTemplateGenerator extends Command
{
    protected $signature = 'app:generate-import-templates';

    protected $description = 'Generate Excel import templates for Property Management, Electricity Meter, and Asset Management';

    public function handle(): int
    {
        $outputDir = storage_path('app/imports/templates');

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
            $this->info("Created directory: {$outputDir}");
        }

        $templates = [
            [
                'name' => 'Property Management',
                'file' => 'property_management_template.xlsx',
                'export' => new PropertyManagementTemplateExport(),
            ],
            [
                'name' => 'Electricity Meter',
                'file' => 'electricity_meter_template.xlsx',
                'export' => new ElectricityMeterTemplateExport(),
            ],
            [
                'name' => 'Asset Management',
                'file' => 'asset_management_template.xlsx',
                'export' => new AssetManagementTemplateExport(),
            ],
        ];

        foreach ($templates as $template) {
            $this->info("Generating {$template['name']} template...");

            $storagePath = 'imports/templates/' . $template['file'];
            Excel::store($template['export'], $storagePath);

            $fullPath = storage_path('app/' . $storagePath);
            $this->line("  ✓ Saved to: {$fullPath}");
        }

        $this->newLine();
        $this->info('✅ All templates generated successfully!');
        $this->newLine();
        $this->line('Templates are in: ' . $outputDir);
        $this->line('');
        $this->line('Instructions:');
        $this->line('  1. Share templates with users to fill in their data');
        $this->line('  2. Users delete the example rows and enter real data');
        $this->line('  3. Place filled files in storage/app/imports/');
        $this->line('  4. Run seeders to import:');
        $this->line('     php artisan db:seed --class=PropertyManagementSeeder');
        $this->line('     php artisan db:seed --class=ElectricityMeterSeeder');
        $this->line('     php artisan db:seed --class=AssetManagementSeeder');

        return Command::SUCCESS;
    }
}
