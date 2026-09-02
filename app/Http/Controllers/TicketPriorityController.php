<?php

namespace App\Http\Controllers;

use App\Models\TicketPriority;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Str;

class TicketPriorityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $ticketPrioritesQuery = TicketPriority::query();

        if ($search) {
            $ticketPrioritesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('level', 'like', "%{$search}%");
            });
        }

        $ticketPriorites = $ticketPrioritesQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('support/setting/ticket-priorities/Priorities', [
            'title' => 'Ticket Priorites',
            'ticketPriorites' => $ticketPriorites,
            'filters' => [
                'search' => $search
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('support/setting/ticket-priorities/PriorityCreate', [
            'title' => 'Add Ticket Prioties',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'color' => 'nullable',
            'description' => 'nullable',
            'level' => 'nullable',
            'response_minutes' => 'nullable',
            'resolution_minutes' => 'nullable',
        ]);

        TicketPriority::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name . '-' . uniqid()),
            'color' => $request->color,
            'description' => $request->description,
            'level' => $request->level,
            'response_minutes' => $request->response_minutes,
            'resolution_minutes' => $request->resolution_minutes,
        ]);

        return redirect('admin/tickets-priority')->with('success', 'Created successfully.');
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
        $ticketPriorites = TicketPriority::findOrFail($id);
        return Inertia::render('support/setting/ticket-priorities/PriorityEdit', [
            'title' => 'Edit Ticket Priorities',
            'priorities' => $ticketPriorites,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $priority = TicketPriority::findOrFail($id);

        $request->validate([
            'name' => 'nullable',
            'color' => 'nullable',
            'description' => 'nullable',
            'level' => 'nullable',
            'response_minutes' => 'nullable',
            'resolution_minutes' => 'nullable',
        ]);

        $slug = Str::slug($request->name);

        $priority->update([
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color,
            'description' => $request->description,
            'level' => $request->level,
            'response_minutes' => $request->response_minutes,
            'resolution_minutes' => $request->resolution_minutes,
        ]);

        return redirect('admin/tickets-priority')->with('success', 'Updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function toggleActive(TicketPriority $ticketPriority)
    {
        $ticketPriority->update([
            'is_active' => ! $ticketPriority->is_active,
        ]);

        return back()->with('success', 'Priority status updated.');
    }
}
