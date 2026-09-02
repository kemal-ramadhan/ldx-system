<?php

namespace App\Http\Controllers;

use App\Models\TicketCategories;
use App\Models\TicketPriority;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Str;

class TicketCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $ticketCategoriesQuery = TicketCategories::query();

        if ($search) {
            $ticketCategoriesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $ticketCategories = $ticketCategoriesQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('support/setting/ticket-categories/Categories', [
            'title' => 'Ticket Categories',
            'ticketCategories' => $ticketCategories,
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
        return Inertia::render('support/setting/ticket-categories/CategoryCreate', [
            'title' => 'Add Ticket Categories',
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
        ]);

        TicketCategories::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name . '-' . uniqid()),
            'color' => $request->color,
            'description' => $request->description,
        ]);

        return redirect('admin/tickets-category')->with('success', 'Category created successfully.');
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
        $ticketCategories = TicketCategories::findOrFail($id);
        return Inertia::render('support/setting/ticket-categories/CategoryEdit', [
            'title' => 'Edit Ticket Categories',
            'categories' => $ticketCategories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $categoriy = TicketCategories::findOrFail($id);

        $request->validate([
            'name' => 'nullable',
            'color' => 'nullable',
            'description' => 'nullable',
        ]);

        $slug = Str::slug($request->name);

        $categoriy->update([
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color,
            'description' => $request->description,
        ]);

        return redirect('admin/tickets-category')->with('success', 'Updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function toggleActive(TicketCategories $ticketCategory)
    {
        $ticketCategory->update([
            'is_active' => ! $ticketCategory->is_active,
        ]);

        return back()->with('success', 'Category status updated.');
    }
}
