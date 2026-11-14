<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Data boundary for the client portal (interconnections & cross connects).
 *
 * A client may only see records where at least one end is a port on a device
 * it owns (device_ports -> rack_divices.client_id). Relations that can belong
 * to another client are loaded with a minimal column set, so no contact data
 * (email, phone, NPWP, address), IPs, serials or sensor ids leak to the browser.
 */
class ClientPortAccess
{
    /**
     * Records (InterconnectionRequest / CrossConnect) touching the client's ports.
     * $includeRequester adds requests the client submitted itself.
     */
    public static function touchingClient(Builder $query, int $clientId, bool $includeRequester = false): Builder
    {
        return $query->where(function (Builder $q) use ($clientId, $includeRequester) {
            if ($includeRequester) {
                $q->where('requester_client_id', $clientId);
            }

            $q->orWhereHas('sourcePort.device', fn (Builder $d) => $d->where('client_id', $clientId))
                ->orWhereHas('destinationPort.device', fn (Builder $d) => $d->where('client_id', $clientId));
        });
    }

    /**
     * Eager loads for both port ends with only the columns the portal shows.
     */
    public static function portRelations(bool $withLocation = false): array
    {
        $relations = [];

        foreach (['sourcePort', 'destinationPort'] as $port) {
            $relations[] = "{$port}:id,rack_divice_id,port_name,port_number,port_type,connector_type,status";
            $relations[] = "{$port}.device:id,client_id,rack_id,code,divice_name";
            $relations[] = "{$port}.device.client:id,company_code,company_name";
            $relations[] = "{$port}.device.rack:id,room_id,code,name";

            if ($withLocation) {
                $relations[] = "{$port}.device.rack.room:id,location_data_center_id,name";
                $relations[] = "{$port}.device.rack.room.locationDataCenter:id,name";
            }
        }

        return $relations;
    }

    /**
     * Which side the client is on: 'source', 'destination' or null.
     */
    public static function sideOf($record, int $clientId): ?string
    {
        if ((int) $record->sourcePort?->device?->client_id === $clientId) {
            return 'source';
        }

        if ((int) $record->destinationPort?->device?->client_id === $clientId) {
            return 'destination';
        }

        return null;
    }
}
