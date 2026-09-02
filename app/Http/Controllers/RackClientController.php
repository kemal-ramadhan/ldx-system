<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientPic;
use App\Models\Rack;
use App\Models\RackDivice;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RackClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $room = $request->room;

        $client = ClientPic::query()->where('user_id', Auth::id())
            ->firstOrFail()
            ->client;

        $racks = Rack::query()
            ->with([
                'room.locationDataCenter',
                'clientRacks' => function ($query) use ($client) {
                    $query->where('client_id', $client->id);
                }
            ])
            ->whereHas('clientRacks', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->paginate(10);

        $racks->getCollection()->transform(function ($rack) {
            $rack->rented_units = $rack->clientRacks->first()?->rented_units ?? 0;
            $rack->monthly_fee = $rack->clientRacks->first()?->monthly_fee ?? 0;

            return $rack;
        });

        return Inertia::render('racks/RackClient', [
            'title' => 'My Racks',
            'racks' => $racks,
            'filters' => [
                'search' => $search,
                'room' => $room,
            ],
        ]);
    }

    public function show(string $id)
    {
        $client = ClientPic::query()->where('user_id', Auth::id())
            ->firstOrFail()
            ->client;

        $rack = Rack::with([
            'room.locationDataCenter',

            'clientRacks' => function ($query) use ($client) {
                $query->where('client_id', $client->id)
                    ->with('client');
            },

            'rackDivices' => function ($query) use ($client) {
                $query->where('client_id', $client->id)
                    ->with([
                        'client',
                        'rackUnits'
                    ]);
            },
        ])
            ->where('id', $id)
            ->whereHas('clientRacks', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->firstOrFail();

        /**
         * ============================
         * DATA CLIENT RACK
         * ============================
         */
        $clientRack = $rack->clientRacks->first();

        /**
         * ============================
         * UNIT TERPAKAI CLIENT
         * ============================
         */
        $usedUnits = RackDivice::query()
            ->where('client_id', $client->id)
            ->where('rack_id', $rack->id)
            ->sum('total_unit');

        /**
         * ============================
         * OVERRIDE DATA UNTUK CLIENT
         * ============================
         */

        // Total unit yang disewa client
        $rack->total_units = $clientRack?->rented_units ?? 0;

        // Unit yang sudah digunakan device
        $rack->client_racks_sum_rented_units = $usedUnits;

        // Sisa unit yang tersedia
        $rack->available_units = ($clientRack?->rented_units ?? 0) - $usedUnits;

        // Power usage milik client saja
        $rack->rack_divices_sum_power_usage = $rack->rackDivices->sum('power_usage');

        // Weight usage milik client saja
        $rack->rack_divices_sum_weight_usage = $rack->rackDivices->sum('weight_usage');

        // Jumlah device client
        $rack->rack_divices_count = $rack->rackDivices->count();

        // Rental info
        $rack->monthly_fee = $clientRack?->monthly_fee;
        $rack->rental_start_date = $clientRack?->rental_start_date;
        $rack->rental_end_date = $clientRack?->rental_end_date;

        return Inertia::render('racks/RackShowClient', [
            'title' => 'Rack Details',
            'rack' => $rack,
        ]);
    }
}
