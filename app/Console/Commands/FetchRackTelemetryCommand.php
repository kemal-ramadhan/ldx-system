<?php

namespace App\Console\Commands;

use App\Http\Controllers\RackMonitoringController;
use App\Models\Rack;
use App\Models\RackTelemetryLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchRackTelemetryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telemetry:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch telemetry sensor data from LDX monitoring API, record logs, and prune records older than 7 days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching telemetry data from LDX API...');

        // 1. Ambil data all rack dalam 1 panggilan efisien
        $response = RackMonitoringController::fetchMonitoringData();

        if (empty($response['ok']) || empty($response['data']) || !is_array($response['data'])) {
            $this->warn('No valid telemetry data received from API.');
            return 0;
        }

        // 2. Petakan rack lokal berdasarkan api_suhu
        $localRacks = Rack::whereNotNull('api_suhu')
            ->where('api_suhu', '!=', '')
            ->get()
            ->keyBy(function ($r) {
                return strtolower(trim($r->api_suhu));
            });

        $insertedCount = 0;
        $now = now();

        foreach ($response['data'] as $item) {
            $apiRackName = $item['rack'] ?? null;
            if (!$apiRackName) continue;

            $matchedRack = $localRacks->get(strtolower(trim($apiRackName)));
            $recordedAt = !empty($item['terakhir'])
                ? Carbon::parse($item['terakhir'])
                : $now;

            // Hindari duplikasi bacaan jika timestamp sensor sama persis sudah tercatat
            $exists = RackTelemetryLog::where('api_rack_name', $apiRackName)
                ->where('recorded_at', $recordedAt)
                ->exists();

            if (!$exists) {
                RackTelemetryLog::create([
                    'rack_id' => $matchedRack?->id,
                    'api_rack_name' => $apiRackName,
                    'slave' => $item['slave'] ?? null,
                    'status' => $item['status'] ?? 'normal',

                    // Suhu & Hum
                    'temp' => isset($item['suhu']['temp']) ? (float) $item['suhu']['temp'] : null,
                    'hum' => isset($item['suhu']['hum']) ? (float) $item['suhu']['hum'] : null,
                    'status_temp' => $item['suhu']['status'] ?? null,
                    'status_hum' => $item['suhu']['status_hum'] ?? null,

                    // PLN
                    'pln_ada' => !empty($item['pln']['ada']),
                    'pln_voltage' => isset($item['pln']['voltage']) ? (float) $item['pln']['voltage'] : null,
                    'pln_current' => isset($item['pln']['current']) ? (float) $item['pln']['current'] : null,
                    'pln_power' => isset($item['pln']['power']) ? (float) $item['pln']['power'] : null,
                    'pln_status' => $item['pln']['status'] ?? null,

                    // UPS
                    'ups_ada' => !empty($item['ups']['ada']),
                    'ups_voltage' => isset($item['ups']['voltage']) ? (float) $item['ups']['voltage'] : null,
                    'ups_current' => isset($item['ups']['current']) ? (float) $item['ups']['current'] : null,
                    'ups_power' => isset($item['ups']['power']) ? (float) $item['ups']['power'] : null,
                    'ups_status' => $item['ups']['status'] ?? null,

                    'recorded_at' => $recordedAt,
                ]);

                $insertedCount++;
            }
        }

        $this->info("Successfully recorded {$insertedCount} telemetry logs.");

        // 3. RETENSI DATA 7 HARI: Hapus log yang berumur lebih dari 7 hari
        $sevenDaysAgo = Carbon::now()->subDays(7);
        $deletedCount = RackTelemetryLog::where('recorded_at', '<', $sevenDaysAgo)->delete();

        if ($deletedCount > 0) {
            $this->info("Pruned {$deletedCount} old telemetry logs older than 7 days.");
        }

        return 0;
    }
}
