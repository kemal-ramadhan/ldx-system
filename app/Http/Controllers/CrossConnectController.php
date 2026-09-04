<?php

namespace App\Http\Controllers;

use App\Models\CrossConnect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CrossConnectController extends Controller
{
    /**
     * =========================================================
     * INDEX
     * =========================================================
     */
    public function index(Request $request)
    {
        $query = CrossConnect::query()
            ->with([
                'sourcePort.device.client:id,company_code,company_name',
                'sourcePort.device.rack:id,code',

                'destinationPort.device.client:id,company_code,company_name',
                'destinationPort.device.rack:id,code',

                'interconnectionRequest:id,request_number',

                'installer:id,name',
            ])
            ->latest('id');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'cross_connect_number',
                    'like',
                    "%{$search}%"
                );

                $q->orWhereHas(
                    'interconnectionRequest',
                    function ($q) use ($search) {
                        $q->where(
                            'request_number',
                            'like',
                            "%{$search}%"
                        );
                    }
                );

                $q->orWhereHas(
                    'sourcePort.device.client',
                    function ($q) use ($search) {
                        $q->where(
                            'company_name',
                            'like',
                            "%{$search}%"
                        );
                    }
                );

                $q->orWhereHas(
                    'destinationPort.device.client',
                    function ($q) use ($search) {
                        $q->where(
                            'company_name',
                            'like',
                            "%{$search}%"
                        );
                    }
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('status') &&
            $request->status !== 'all'
        ) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $crossConnects = $query
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [
            'total' => CrossConnect::count(),

            'active' => CrossConnect::where(
                'status',
                'active'
            )->count(),

            'planned' => CrossConnect::where(
                'status',
                'planned'
            )->count(),

            'terminated' => CrossConnect::where(
                'status',
                'terminated'
            )->count(),

            'cancelled' => CrossConnect::where(
                'status',
                'cancelled'
            )->count(),
        ];

        return Inertia::render(
            'cross-connects/Index',
            [
                'title' => 'Cross Connects',

                'crossConnects' => $crossConnects,

                'statistics' => $statistics,

                'filters' => [
                    'status' => $request->status ?? 'all',
                    'search' => $request->search ?? '',
                ],
            ]
        );
    }


    /**
     * =========================================================
     * SHOW
     * =========================================================
     */
    public function show(string $id)
    {
        $crossConnect = CrossConnect::query()
            ->with([
                /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            */
                'sourcePort.device.client',
                'sourcePort.device.rack.room.locationDataCenter',

                /*
            |--------------------------------------------------------------------------
            | Destination
            |--------------------------------------------------------------------------
            */
                'destinationPort.device.client',
                'destinationPort.device.rack.room.locationDataCenter',

                /*
            |--------------------------------------------------------------------------
            | Original Interconnection
            |--------------------------------------------------------------------------
            */
                'interconnectionRequest.requesterClient',
                'interconnectionRequest.destinationApprover',
                'interconnectionRequest.dcApprover',

                /*
            |--------------------------------------------------------------------------
            | Installer
            |--------------------------------------------------------------------------
            */
                'installer',
            ])
            ->findOrFail($id);

        return Inertia::render(
            'cross-connects/Show',
            [
                'title' => 'Cross Connect Details',

                'crossConnect' => $crossConnect,
            ]
        );
    }


    /**
     * =========================================================
     * TERMINATE
     * =========================================================
     */
    public function terminate(
        Request $request,
        string $id
    ) {
        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use (
            $id,
            $validated
        ) {

            /*
            |--------------------------------------------------------------------------
            | Lock Cross Connect
            |--------------------------------------------------------------------------
            */

            $crossConnect = CrossConnect::query()
                ->lockForUpdate()
                ->findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if ($crossConnect->status !== 'active') {
                throw ValidationException::withMessages([
                    'status' =>
                    'Only active cross connects can be terminated.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Lock Ports
            |--------------------------------------------------------------------------
            */

            $sourcePort = $crossConnect
                ->sourcePort()
                ->lockForUpdate()
                ->firstOrFail();

            $destinationPort = $crossConnect
                ->destinationPort()
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Update Cross Connect
            |--------------------------------------------------------------------------
            */

            $crossConnect->update([
                'status' => 'terminated',

                'terminated_at' => now(),

                'notes' => $validated['reason'],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Release Source Port
            |--------------------------------------------------------------------------
            */

            if ($sourcePort->status === 'connected') {
                $sourcePort->update([
                    'status' => 'available',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Release Destination Port
            |--------------------------------------------------------------------------
            */

            if ($destinationPort->status === 'connected') {
                $destinationPort->update([
                    'status' => 'available',
                ]);
            }
        });

        return back()->with(
            'success',
            'Cross connect has been terminated successfully.'
        );
    }
}
