<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\LocationDataCenter;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $locationsQuery = LocationDataCenter::query();

        if ($search) {
            $locationsQuery->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $locations = $locationsQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('locations/Locations', [
            'title' => 'Location Management',

            'locations' => $locations,

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
        return Inertia::render('locations/LocationCreate', [
            'title' => 'Add Location',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:location_data_centers,code',
            'name' => 'required',
            'address' => 'required',
        ]);

        LocationDataCenter::create([
            'code' => $request->code,
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return redirect('admin/locations')->with('success', 'Location created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $location = LocationDataCenter::findOrFail($id);
        return Inertia::render('locations/LocationShow', [
            'title' => 'Location Details',
            'location' => $location,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $location = LocationDataCenter::findOrFail($id);
        return Inertia::render('locations/LocationEdit', [
            'title' => 'Edit Location',
            'location' => $location,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $location = LocationDataCenter::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:location_data_centers,code,' . $location->id,
            'name' => 'required',
            'address' => 'required',
        ]);

        $location->update([
            'code' => $request->code,
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return redirect('admin/locations')->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $location = LocationDataCenter::findOrFail($id);
        $location->delete();

        return redirect('admin/locations')->with('success', 'Location deleted successfully.');
    }
}
