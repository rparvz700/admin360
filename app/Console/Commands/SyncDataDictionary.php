<?php

namespace App\Console\Commands;

use App\Services\DataDictionarySyncService;
use Illuminate\Console\Command;

class SyncDataDictionary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:sync-dictionary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan database schema and synchronize the data dictionary and join registry';

    /**
     * Execute the console command.
     */
    public function handle(DataDictionarySyncService $syncService): int
    {
        $this->info('Starting database schema inspection and Data Dictionary synchronization...');

        $result = $syncService->sync();

        if (($result['status'] ?? '') === 'skipped') {
            $this->warn($result['message']);
            return Command::SUCCESS;
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['New Columns Registered', $result['created_columns'] ?? 0],
                ['Existing Columns Preserved (Customizations Kept)', $result['existing_columns'] ?? 0],
                ['Column Types Updated', $result['updated_columns'] ?? 0],
                ['New Join Relationships Registered', $result['created_joins'] ?? 0],
            ]
        );

        $this->info('Data Dictionary synchronization completed successfully!');

        return Command::SUCCESS;
    }
}
