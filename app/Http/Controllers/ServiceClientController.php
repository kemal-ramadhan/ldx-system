<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ServiceClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $clientId = Auth::user()->clientPic?->client_id;

        $services = Service::with([
            'rack.room.locationDataCenter',
            'client'
        ])
            ->where('client_id', $clientId)

            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('billing_cycle', 'like', "%{$search}%");
                });
            })

            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('services/services/ServiceClient', [

            'title' => 'Services Management',

            'services' => $services,

            'filters' => [
                'search' => $search,
                'status' => $status,
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
        $service = Service::with([

            /**
             * =========================
             * RELATIONS
             * =========================
             */
            'client',

            'rack.room.locationDataCenter',

            'serviceItems.product',
        ])->findOrFail($id);

        return Inertia::render('services/services/ServiceShowClient', [

            'title' => 'Service Detail',

            'service' => $service,
        ]);
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
