<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Location;

class SyncLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync locations table from Baseline API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching data from API...');

        try {
            $url = config('app.baseline_base_url').'api/locations';
            $token = config('app.baseline_api_token');

            $response = Http::timeout(60)
                            ->withToken($token)
                            ->get($url);

            if (!$response->successful()) {
                $this->error('API request failed!');
                return Command::FAILURE;
            }

            $data = $response->json();
            $items = $data['data'] ?? [];

            if (!is_array($items)) {
                $this->error('Invalid API response format');
                return Command::FAILURE;
            }

            $bar = $this->output->createProgressBar(count($items));
            $bar->start();

            foreach ($items as $item) {
                Location::updateOrCreate(
                    [
                        // Unique key (VERY IMPORTANT)
                        'district_code' => $item['district_code'] ?? null,
                        'upazilla'      => $item['upazilla'] ?? null,
                    ],
                    [
                        'district'            => $item['district'] ?? null,
                        'district_kmz_code'   => $item['district_kmz_code'] ?? null,
                        'division'            => $item['division'] ?? null,
                        'subcenter'           => $item['subcenter'] ?? null,
                        'rio'                 => $item['rio'] ?? null,
                        'thana_short_code'    => $item['thana_short_code'] ?? null,
                        'district_opus_id'    => $item['district_opus_id'] ?? null,
                        'is_metro'            => $item['is_metro'] ?? 0,
                    ]
                );

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('Sync completed successfully!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
