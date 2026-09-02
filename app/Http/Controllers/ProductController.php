<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategorie;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $category = $request->category;

        $products = Product::with('categories')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })

            ->when($category, function ($query) use ($category) {
                $query->where('product_category_id', $category);
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('services/products/Products', [
            'title' => 'Products Management',
            'categories' => ProductCategorie::select('id', 'categori')->get(),

            'products' => $products,

            'filters' => [
                'search' => $search,
                'categories' => $category
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('services/products/ProductCreate', [
            'title' => 'Add Product',
            'categories' => ProductCategorie::select('id', 'categori')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'code' => 'required|string|max:50|unique:products,code',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'base_price' => 'required|numeric|min:0',
            'billing_type' => 'required|in:recurring,one_time',
            'status' => 'required|in:active,inactive',
        ]);

        Product::create([
            'product_category_id' => $validated['product_category_id'],
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'unit' => $validated['unit'],
            'base_price' => $validated['base_price'],
            'billing_type' => $validated['billing_type'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('categories')->findOrFail($id);

        return Inertia::render('services/products/ProductShow', [
            'title' => 'Product Detail',
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        $categories = ProductCategorie::select('id', 'categori')->get();
        return Inertia::render('services/products/ProductEdit', [
            'title' => 'Edit Room',
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'code' => 'required|string|max:50|unique:products,code,' . $product->id,
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'base_price' => 'required|numeric|min:0',
            'billing_type' => 'required|in:recurring,one_time',
            'status' => 'required|in:active,inactive',
        ]);

        $product->update([
            'product_category_id' => $validated['product_category_id'],
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'unit' => $validated['unit'],
            'base_price' => $validated['base_price'],
            'billing_type' => $validated['billing_type'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
