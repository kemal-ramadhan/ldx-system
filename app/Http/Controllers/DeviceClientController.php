<?php

namespace App\Http\Controllers;

use App\Models\ClientPic;
use App\Models\RackDivice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DeviceClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $client = ClientPic::with('client')
            ->where('user_id', Auth::id())
            ->firstOrFail()
            ->client;

        $devices = RackDivice::query()
            ->with([
                'rack.room.locationDataCenter',
                'rackUnits'
            ])

            ->where('client_id', $client->id)

            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {

                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('divice_name', 'like', "%{$search}%")
                        ->orWhere('divice_type', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('devices/DevicesClient', [
            'title' => 'My Devices',

            'devices' => $devices,

            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
