<?php
// app/Events/TicketUpdated.php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;
    public $ticketId;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
        $this->ticketId = $ticket->id;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('ticket.' . $this->ticketId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'status' => $this->ticket->status,
            'priority' => $this->ticket->priority,
            'assigned_to' => $this->ticket->assignedUser?->name,
            'resolved_at' => $this->ticket->resolved_at?->toISOString(),
            'closed_at' => $this->ticket->closed_at?->toISOString(),
            'updated_at' => $this->ticket->updated_at->toISOString(),
        ];
    }
}