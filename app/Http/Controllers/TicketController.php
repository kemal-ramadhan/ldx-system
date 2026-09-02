<?php

namespace App\Http\Controllers;

use App\Events\TicketMessageSent;
use App\Events\TicketUpdated;
use App\Models\Ticket;
use App\Models\TicketAttachments;
use App\Models\TicketCategories;
use App\Models\TicketMessage;
use App\Models\TicketPriority;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $category = $request->category;
        $priority = $request->priority;

        $tickets = Ticket::with([
                'client',
                'category',
                'priority',
            ])
            ->when($search, function ($query) use ($search) {

                $query->where(function ($q) use ($search) {

                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")

                        ->orWhereHas('category', function ($category) use ($search) {
                            $category->where('name', 'like', "%{$search}%");
                        })

                        ->orWhereHas('priority', function ($priority) use ($search) {
                            $priority->where('name', 'like', "%{$search}%");
                        });
                });
            })

            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })

            ->when($category, function ($query) use ($category) {
                $query->where('category_id', $category);
            })

            ->when($priority, function ($query) use ($priority) {
                $query->where('priority_id', $priority);
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('support/tickets/Ticket', [
            'title' => 'Support Tickets',

            'tickets' => $tickets,

            'categories' => TicketCategories::query()->where('is_active', true)->get(),

            'priorities' => TicketPriority::query()->where('is_active', true)->get(),

            'filters' => [
                'search' => $search,
                'status' => $status,
                'category' => $category,
                'priority' => $priority,
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
        $ticket = Ticket::with([
            'category',
            'priority',
            'creator',
            'assignedUser',
            'client',
            'attachments' => function ($query) {
                $query
                    ->whereNull('ticket_message_id')
                    ->with('uploader')
                    ->latest();
            },
            'messages' => function ($query) {
                $query
                    ->with([
                        'user',
                        'attachments' => function ($q) {
                            $q->with('uploader');
                        }
                    ])
                    ->orderBy('created_at', 'asc');
            },
        ])->findOrFail($id);

        // Transform data (similar to client side but with all messages including internal)
        $messages = $ticket->messages->map(function ($message) {
            return [
                'id' => $message->id,
                'message' => $message->message,
                'is_internal' => $message->is_internal ?? false,
                'sender_type' => $message->user?->type === 'technician' ? 'technician' : 'client',
                'sender_name' => $message->user?->name ?? 'Unknown',
                'sender_email' => $message->user?->email ?? '',
                'sender_avatar' => $message->user?->avatar ?? null,
                'role' => $this->getUserRole($message->user),
                'attachments' => $message->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'filename' => $attachment->file_name,
                        'size' => $attachment->file_size,
                        'mime_type' => $attachment->file_type,
                        'url' => asset('storage/' . $attachment->file_path),
                    ];
                })->toArray(),
                'created_at' => $message->created_at->toISOString(),
            ];
        })->toArray();

        // Get technicians for assignment - FIXED QUERY
        $technicians = User::whereHas('role', function ($query) {
            $query->where('slug', 'teknisi');
        })
        ->orWhereHas('role', function ($query) {
            $query->where('slug', 'super-admin');
        })
        ->where('status', 'active')
        ->get(['id', 'name', 'email']);

        return Inertia::render('support/tickets/TicketDetail', [
            'title' => 'Ticket Detail',
            'ticket' => $ticket,
            'messages' => $messages,
            'all_attachments' => $ticket->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'filename' => $attachment->file_name,
                    'size' => $attachment->file_size,
                    'mime_type' => $attachment->file_type,
                    'url' => asset('storage/' . $attachment->file_path),
                    'uploaded_at' => $attachment->created_at->toISOString(),
                ];
            })->toArray(),
            'timeline' => $this->generateTimeline($ticket),
            'technicians' => $technicians,
            'statuses' => [
                ['value' => 'open', 'label' => 'Open'],
                ['value' => 'in_progress', 'label' => 'In Progress'],
                ['value' => 'waiting_customer', 'label' => 'Waiting Customer'],
                ['value' => 'waiting_technician', 'label' => 'Waiting Technician'],
                ['value' => 'resolved', 'label' => 'Resolved'],
                ['value' => 'closed', 'label' => 'Closed'],
            ],
            'priorities' => [
                ['value' => 'low', 'label' => 'Low'],
                ['value' => 'medium', 'label' => 'Medium'],
                ['value' => 'high', 'label' => 'High'],
                ['value' => 'critical', 'label' => 'Critical'],
            ],
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
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,waiting_customer,waiting_technician,resolved,closed',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $ticket->update($validated);

        return redirect()->back()->with('success', 'Ticket updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Other methods for ticket management can be added here, such as assigning technicians, changing status, etc.
     */

    public function reply(Request $request, $ticketId)
    {
        $request->validate([
            'message' => 'required_without:attachments|string|nullable',
            'attachments' => 'array|max:5',
            'attachments.*' => 'file|max:10240|mimes:png,jpg,jpeg,pdf,doc,docx',
            'is_internal' => 'boolean',
        ]);

        $ticket = Ticket::findOrFail($ticketId);

        DB::transaction(function () use ($request, $ticket) {
            $message = new TicketMessage();
            $message->ticket_id = $ticket->id;
            $message->user_id = Auth::id();
            $message->message = $request->input('message') ?? '';
            $message->is_internal = $request->boolean('is_internal', false);
            $message->save();

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                    $path = $file->storeAs('ticket-attachments/' . $ticket->id, $filename, 'public');
                    
                    $attachment = new TicketAttachments();
                    $attachment->ticket_id = $ticket->id;
                    $attachment->ticket_message_id = $message->id;
                    $attachment->file_name = $file->getClientOriginalName();
                    $attachment->file_path = $path;
                    $attachment->file_size = $file->getSize();
                    $attachment->file_type = $file->getMimeType();
                    $attachment->uploaded_by = Auth::id();
                    $attachment->save();
                }
            }

            // If not internal, update ticket status
            if (!$message->is_internal && $ticket->status === 'waiting_technician') {
                $ticket->status = 'in_progress';
                $ticket->save();
            }
            broadcast(new TicketMessageSent($message, $ticket->id))->toOthers();
        });


        return redirect()->back()->with('success', 'Reply sent successfully.');
    }

    public function resolve($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->status = 'resolved';
        $ticket->resolved_at = now();
        $ticket->save();

        broadcast(new TicketUpdated($ticket))->toOthers();

        return redirect()->back()->with('success', 'Ticket resolved successfully.');
    }

    public function close($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->status = 'closed';
        $ticket->closed_at = now();
        $ticket->save();

        return redirect()->back()->with('success', 'Ticket closed successfully.');
    }

    public function reopen($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->status = 'open';
        $ticket->resolved_at = null;
        $ticket->closed_at = null;
        $ticket->save();

        return redirect()->back()->with('success', 'Ticket reopened successfully.');
    }

    private function generateTimeline($ticket)
    {
        $timeline = [];

        // Created
        $timeline[] = [
            'id' => 'created_' . $ticket->id,
            'type' => 'created',
            'description' => 'Ticket created',
            'icon' => 'Zap',
            'user_name' => $ticket->creator?->name ?? 'System',
            'user_avatar' => $ticket->creator?->avatar ?? null,
            'created_at' => $ticket->created_at->toISOString(),
        ];

        // Assigned
        if ($ticket->assignedUser) {
            $timeline[] = [
                'id' => 'assigned_' . $ticket->id,
                'type' => 'assigned',
                'description' => "Assigned to {$ticket->assignedUser->name}",
                'icon' => 'User',
                'user_name' => 'System',
                'user_avatar' => null,
                'created_at' => $ticket->updated_at->toISOString(),
            ];
        }

        // Messages
        foreach ($ticket->messages as $message) {
            $senderType = $message->user?->type === 'technician' ? 'technician' : 'client';
            $type = $message->is_internal ? 'internal_note' : ($senderType === 'technician' ? 'technician_replied' : 'client_replied');
            $description = $message->is_internal 
                ? 'Internal note added' 
                : ($senderType === 'technician' ? 'Technician replied' : 'Client replied');
            
            $timeline[] = [
                'id' => 'msg_' . $message->id,
                'type' => $type,
                'description' => $description,
                'icon' => $message->is_internal ? 'Lock' : 'MessageSquare',
                'user_name' => $message->user?->name ?? 'Unknown',
                'user_avatar' => $message->user?->avatar ?? null,
                'created_at' => $message->created_at->toISOString(),
            ];
        }

        // Resolved
        if ($ticket->resolved_at) {
            $timeline[] = [
                'id' => 'resolved_' . $ticket->id,
                'type' => 'resolved',
                'description' => 'Ticket resolved',
                'icon' => 'CheckCircle',
                'user_name' => 'System',
                'user_avatar' => null,
                'created_at' => $ticket->resolved_at->toISOString(),
            ];
        }

        // Closed
        if ($ticket->closed_at) {
            $timeline[] = [
                'id' => 'closed_' . $ticket->id,
                'type' => 'closed',
                'description' => 'Ticket closed',
                'icon' => 'XCircle',
                'user_name' => 'System',
                'user_avatar' => null,
                'created_at' => $ticket->closed_at->toISOString(),
            ];
        }

        // Sort by date
        usort($timeline, function ($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });

        return $timeline;
    }

    private function getUserRole($user)
    {
        if (!$user) return 'Unknown';
        
        $roleSlug = $user->role?->slug;
        
        $roleMap = [
            'super-admin' => 'Super Admin',
            'teknisi' => 'Technician',
            'client' => 'Client',
            'marketing' => 'Marketing',
        ];
        
        return $roleMap[$roleSlug] ?? $user->role?->name ?? 'Unknown';
    }
}
