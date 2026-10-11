<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RackMonitoringController extends Controller
{
    /**
     * Display the Rack Monitoring (Suhu Monitoring) page.
     */
    public function index()
    {
        $initialData = self::fetchMonitoringData();

        return Inertia::render('racks/RackMonitoring', [
            'title' => 'Suhu Monitoring',
            'initialData' => $initialData,
        ]);
    }

    /**
     * API endpoint for AJAX polling to fetch fresh monitoring data.
     */
    public function data(Request $request)
    {
        $rack = $request->query('rack');
        $data = self::fetchMonitoringData($rack);

        return response()->json($data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Fetch monitoring data from external endpoint using configured API key.
     */
    public static function fetchMonitoringData(?string $rack = null): array
    {
        $baseUrl = config('services.ldx_monitoring.url', env('LDX_MONITORING_API_URL', 'https://ldx.theinvention.net/api/data.php'));
        $apiKey = config('services.ldx_monitoring.key', env('LDX_MONITORING_API_KEY'));

        $url = $rack ? $baseUrl . '?rack=' . urlencode($rack) : $baseUrl;

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
            ])
            ->timeout(12)
            ->withoutVerifying() // handle self-signed or custom SSL certificates if necessary
            ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('LDX Monitoring API returned error status: ' . $response->status(), [
                'body' => $response->body()
            ]);

            return [
                'ok' => false,
                'error' => 'API returned status ' . $response->status(),
                'server_time' => now()->format('Y-m-d H:i:s'),
                'ambang' => null,
                'jumlah' => 0,
                'data' => [],
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to fetch LDX Monitoring data: ' . $e->getMessage());

            return [
                'ok' => false,
                'error' => 'Gagal terhubung ke API monitoring: ' . $e->getMessage(),
                'server_time' => now()->format('Y-m-d H:i:s'),
                'ambang' => null,
                'jumlah' => 0,
                'data' => [],
            ];
        }
    }
}
