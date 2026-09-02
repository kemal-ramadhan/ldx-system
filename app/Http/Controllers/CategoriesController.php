<?php

namespace App\Http\Controllers;

use App\Models\ProductCategorie;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $categoriesQuery = ProductCategorie::query();

        if ($search) {
            $categoriesQuery->where(function ($q) use ($search) {
                $q->where('categori', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $categoriesQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('services/categories/Categories', [
            'title' => 'Categories Management',

            'categories' => $categories,

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
        return Inertia::render('services/categories/CategoriesCreate', [
            'title' => 'Add Category',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'categori' => 'required',
            'description' => 'required',
        ]);

        ProductCategorie::create([
            'categori' => $request->categori,
            'slug' => Str::slug($request->categori . '-' . uniqid()),
            'description' => $request->description,
        ]);

        return redirect('admin/categories')->with('success', 'Category created successfully.');
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
        $category = ProductCategorie::findOrFail($id);
        return Inertia::render('services/categories/CategoriesEdit', [
            'title' => 'Edit Category',
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'categori' => 'required',
            'description' => 'required',
        ]);

        $category = ProductCategorie::findOrFail($id);

        $slug = Str::slug($request->categori);

        $count = ProductCategorie::query()->where('slug', 'LIKE', "{$slug}%")
            ->where('id', '!=', $category->id)
            ->count('id');

        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }

        $category->update([
            'categori' => $request->categori,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return redirect('admin/categories')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = ProductCategorie::findOrFail($id);

        $category->delete();

        return redirect('admin/categories')->with('success', 'Category deleted successfully.');
    }
}
