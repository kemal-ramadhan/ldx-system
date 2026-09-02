<?php
// app/Events/TicketMessageSent.php

namespace App\Events;

use App\Models\TicketMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TicketMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $ticketId;

    /**
     * Create a new event instance.
     */
    public function __construct(TicketMessage $message, $ticketId)
    {
        $this->message = $message;
        $this->ticketId = $ticketId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('ticket.' . $this->ticketId),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'message' => $this->message->message,
            'is_internal' => $this->message->is_internal,
            'sender_name' => $this->message->user?->name ?? 'System',
            'sender_email' => $this->message->user?->email ?? '',
            'sender_avatar' => $this->message->user?->avatar ?? null,
            'role' => $this->getUserRole($this->message->user),
            'attachments' => $this->message->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'filename' => $attachment->file_name,
                    'size' => $attachment->file_size,
                    'mime_type' => $attachment->file_type,
                    'url' => \asset('storage/' . $attachment->file_path),
                ];
            }),
            'created_at' => $this->message->created_at->toISOString(),
            'sender_type' => $this->isTechnician($this->message->user) ? 'technician' : 'client',
        ];
    }

    private function getUserRole($user)
    {
        if (!$user) return 'System';
        
        $roleSlug = $user->role?->slug;
        $roleMap = [
            'super-admin' => 'Super Admin',
            'teknisi' => 'Technician',
            'client' => 'Client',
            'marketing' => 'Marketing',
        ];
        
        return $roleMap[$roleSlug] ?? $user->role?->name ?? 'User';
    }

    private function isTechnician($user)
    {
        if (!$user) return false;
        return in_array($user->role?->slug, ['teknisi', 'super-admin']);
    }
}