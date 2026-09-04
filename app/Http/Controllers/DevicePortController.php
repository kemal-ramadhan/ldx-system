<?php

namespace App\Http\Controllers;

use App\Models\DevicePort;
use Inertia\Inertia;

class DevicePortController extends Controller
{
    /**
     * =========================================================
     * SHOW DEVICE PORT
     * =========================================================
     */
    public function show(string $id)
    {
        $devicePort = DevicePort::query()
            ->with([
                /*
                |--------------------------------------------------------------------------
                | Device
                |--------------------------------------------------------------------------
                */
                'device.client',
                'device.rack.room.locationDataCenter',

                /*
                |--------------------------------------------------------------------------
                | Active Source Cross Connect
                |--------------------------------------------------------------------------
                */
                'activeSourceCrossConnect.sourcePort.device.client',
                'activeSourceCrossConnect.sourcePort.device.rack',

                'activeSourceCrossConnect.destinationPort.device.client',
                'activeSourceCrossConnect.destinationPort.device.rack',

                /*
                |--------------------------------------------------------------------------
                | Active Destination Cross Connect
                |--------------------------------------------------------------------------
                */
                'activeDestinationCrossConnect.sourcePort.device.client',
                'activeDestinationCrossConnect.sourcePort.device.rack',

                'activeDestinationCrossConnect.destinationPort.device.client',
                'activeDestinationCrossConnect.destinationPort.device.rack',

                /*
                |--------------------------------------------------------------------------
                | Historical Cross Connects
                |--------------------------------------------------------------------------
                */
                'sourceCrossConnects',
                'destinationCrossConnects',
            ])
            ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Determine Active Cross Connect
        |--------------------------------------------------------------------------
        */

        $activeCrossConnect = null;
        $connectionSide = null;

        if ($devicePort->activeSourceCrossConnect) {
            $activeCrossConnect = $devicePort->activeSourceCrossConnect;
            $connectionSide = 'source';
        } elseif ($devicePort->activeDestinationCrossConnect) {
            $activeCrossConnect = $devicePort->activeDestinationCrossConnect;
            $connectionSide = 'destination';
        }

        return Inertia::render(
            'device-ports/Show',
            [
                'title' => 'Device Port Details',

                'devicePort' => $devicePort,

                'activeCrossConnect' => $activeCrossConnect,

                'connectionSide' => $connectionSide,
            ]
        );
    }
}