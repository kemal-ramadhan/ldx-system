<?php

namespace App\Http\Controllers;

use App\Events\TicketMessageSent;
use App\Models\Ticket;
use App\Models\TicketAttachments;
use App\Models\TicketCategories;
use App\Models\TicketMessage;
use App\Models\TicketPriority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TicketClientController extends Controller
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

        $clientId = Auth::user()->clientPic?->client_id;

        $tickets = Ticket::with([
                'client',
                'category',
                'priority',
            ])
            ->where('client_id', $clientId)

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

        return Inertia::render('support/tickets/TicketClient', [
            'title' => 'My Tickets',

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
        return Inertia::render('support/tickets/TicketClientCreate', [
            'title' => 'Add Ticket',
            'categories' => TicketCategories::all(),
            'priorities' => TicketPriority::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:ticket_categories,id'],
            'priority_id' => ['required', 'exists:ticket_priorities,id'],
            'description' => ['required', 'string'],

            'attachments' => ['nullable', 'array'],
            'attachments.*' => [
                'file',
                'max:10240', //10MB
                'mimes:jpg,jpeg,png,pdf,doc,docx'
            ],
        ]);

        DB::transaction(function () use ($request) {

            $priority = TicketPriority::findOrFail($request->priority_id);

            $ticket = Ticket::create([
                'code' => $this->generateTicketCode(),
                'subject' => $request->subject,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'priority_id' => $request->priority_id,

                // sesuaikan dengan aplikasi kamu
                'client_id' => Auth::user()->clientPic?->client_id,
                'status' => 'open',
                'sla_due_at' => now()->addMinutes(
                    $priority->resolution_minutes
                ),
            ]);

            /**
             * Upload Multiple Attachments
             */
            if ($request->hasFile('attachments')) {

                foreach ($request->file('attachments') as $file) {

                    $path = $file->store(
                        "tickets/{$ticket->id}",
                        'public'
                    );

                    TicketAttachments::create([
                        'ticket_id' => $ticket->id,
                        'ticket_message_id' => null,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }
        });

        return redirect('client/tickets')->with('success', 'Ticket created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $clientId = Auth::user()->clientPic?->client_id;
        abort_if(!$clientId, 403);
        
        $ticket = Ticket::with([
            'category',
            'priority',
            'creator',
            'assignedUser',
            /**
             * Ticket Attachments
             */
            'attachments' => function ($query) {
                $query
                    ->whereNull('ticket_message_id')
                    ->with('uploader')
                    ->latest();
            },
            /**
             * Conversation
             */
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
        ])
        ->where('client_id', $clientId)
        ->findOrFail($id);
        
        // Transform messages to include sender_type
        $messages = $ticket->messages->map(function ($message) {
            return [
                'id' => $message->id,
                'message' => $message->message,
                'is_internal' => $message->is_internal ?? false,
                'sender_type' => $message->user?->type === 'technician' ? 'technician' : 'client',
                'sender_name' => $message->user?->name ?? 'Unknown',
                'sender_email' => $message->user?->email ?? '',
                'sender_avatar' => $message->user?->avatar ?? null,
                'role' => $message->user?->type === 'technician' ? 'Technician' : 'Client',
                'attachments' => $message->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'filename' => $attachment->file_name, // Changed from 'filename' to 'file_name'
                        'size' => $attachment->file_size, // Changed from 'size' to 'file_size'
                        'mime_type' => $attachment->file_type, // Changed from 'mime_type' to 'file_type'
                        'url' => asset('storage/' . $attachment->file_path), // Changed from 'path' to 'file_path'
                    ];
                })->toArray(),
                'created_at' => $message->created_at->toISOString(),
            ];
        })->toArray();
        
        // Transform ticket attachments
        $allAttachments = $ticket->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'filename' => $attachment->file_name,
                'size' => $attachment->file_size,
                'mime_type' => $attachment->file_type,
                'url' => asset('storage/' . $attachment->file_path),
                'uploaded_at' => $attachment->created_at->toISOString(),
            ];
        })->toArray();
        
        // Generate timeline
        $timeline = [];
        
        // Ticket created
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
        
        // Add message events to timeline
        foreach ($ticket->messages as $message) {
            $senderType = $message->user?->type === 'technician' ? 'technician' : 'client';
            $timeline[] = [
                'id' => 'msg_' . $message->id,
                'type' => $senderType === 'technician' ? 'technician_replied' : 'client_replied',
                'description' => ($senderType === 'technician' ? 'Technician' : 'Client') . ' replied',
                'icon' => 'MessageSquare',
                'user_name' => $message->user?->name ?? 'Unknown',
                'user_avatar' => $message->user?->avatar ?? null,
                'created_at' => $message->created_at->toISOString(),
            ];
        }
        
        // Sort timeline by date
        usort($timeline, function ($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
        
        // Transform ticket data
        $ticketData = [
            'id' => $ticket->id,
            'code' => $ticket->code,
            'subject' => $ticket->subject,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'category' => [
                'id' => $ticket->category->id,
                'name' => $ticket->category->name,
            ],
            'client' => [
                'id' => $ticket->client->id,
                'name' => $ticket->client->name,
                'email' => $ticket->client->email,
                'avatar' => $ticket->client->avatar ?? null,
            ],
            'assigned_technician' => $ticket->assignedUser ? [
                'id' => $ticket->assignedUser->id,
                'name' => $ticket->assignedUser->name,
                'email' => $ticket->assignedUser->email,
                'avatar' => $ticket->assignedUser->avatar ?? null,
            ] : null,
            'created_at' => $ticket->created_at->toISOString(),
            'sla_due' => $ticket->sla_due?->toISOString(),
            'resolved_at' => $ticket->resolved_at?->toISOString(),
            'closed_at' => $ticket->closed_at?->toISOString(),
        ];
        
        return Inertia::render('support/tickets/TicketClientDetail', [
            'title' => 'Ticket Detail',
            'ticket' => $ticketData,
            'messages' => $messages,
            'all_attachments' => $allAttachments,
            'timeline' => $timeline,
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

    /**
     * Other Functions
     */
    private function generateTicketCode(): string
    {
        $lastTicket = Ticket::latest('id')->first();

        $next = $lastTicket ? $lastTicket->id + 1 : 1;

        return sprintf(
            'TCK-%s-%05d',
            now()->format('Ymd'),
            $next
        );
    }

    public function storeReply(Request $request, $ticketId)
    {
        $request->validate([
            'message' => 'required_without:attachments|string|nullable',
            'attachments' => 'array|max:5',
            'attachments.*' => 'file|max:10240|mimes:png,jpg,jpeg,pdf,doc,docx',
        ], [
            'message.required_without' => 'Please enter a message or attach a file.',
            'attachments.*.max' => 'Each file must not exceed 10MB.',
            'attachments.*.mimes' => 'Only PNG, JPG, PDF, DOC, and DOCX files are allowed.',
            'attachments.max' => 'You can upload a maximum of 5 files.',
        ]);

        // Get the ticket
        $ticket = Ticket::query()->where('client_id', Auth::user()->clientPic?->client_id)
            ->findOrFail($ticketId);

        // Check if ticket is closed
        if ($ticket->status === 'closed' || $ticket->status === 'resolved') {
            return back()->withErrors([
                'message' => 'This ticket is already closed or resolved. Please create a new ticket if you need further assistance.'
            ]);
        }

        // Create message
        $message = new TicketMessage();
        $message->ticket_id = $ticket->id;
        $message->user_id = Auth::id();
        $message->message = $request->input('message') ?? '';
        $message->is_internal = false;
        $message->save();

        // Handle attachments
        $uploadedAttachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Generate unique filename
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $path = $file->storeAs('ticket-attachments/' . $ticket->id, $filename, 'public');
                
                $attachment = new TicketAttachments();
                $attachment->ticket_id = $ticket->id;
                $attachment->ticket_message_id = $message->id;
                $attachment->file_name = $file->getClientOriginalName();
                $attachment->file_path = $path;
                $attachment->file_size = $file->getSize();
                $attachment->file_type = $file->getMimeType();
                $attachment->uploaded_by = Auth::id(); // Using uploaded_by instead of uploader_id
                $attachment->save();
                
                $uploadedAttachments[] = $attachment;
            }
        }

        // Update ticket status if waiting customer
        if ($ticket->status === 'waiting_customer') {
            $ticket->status = 'in_progress';
            $ticket->save();
        }

        // Load relationships for response
        $message->load(['user', 'attachments']);

        broadcast(new TicketMessageSent($message, $ticket->id))->toOthers();

        return redirect()->back()->with([
            'success' => 'Your reply has been sent successfully.',
        ]);
    }
}
