<?php

namespace Database\Seeders;

use App\Services\DataDictionarySyncService;
use Illuminate\Database\Seeder;

class DataDictionarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $syncService = app(DataDictionarySyncService::class);
        $syncService->sync();
    }
}
