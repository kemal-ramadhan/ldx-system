<?php

namespace App\Http\Controllers;

use App\Models\LocationDataCenter;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $location = $request->location;

        $rooms = Room::with('locationDataCenter')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })

            ->when($location, function ($query) use ($location) {
                $query->where('location_data_center_id', $location);
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('rooms/Rooms', [
            'title' => 'Room Management',
            'locations' => LocationDataCenter::select('id', 'name')->get(),

            'rooms' => $rooms,

            'filters' => [
                'search' => $search,
                'location' => $location
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('rooms/RoomCreate', [
            'title' => 'Add Room',
            'locations' => LocationDataCenter::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:rooms,code',
            'name' => 'required',
            'location_id' => 'required|exists:location_data_centers,id',
            'description' => 'nullable',
        ]);

        Room::create([
            'code' => $request->code,
            'name' => $request->name,
            'location_data_center_id' => $request->location_id,
            'description' => $request->description,
        ]);

        return redirect('admin/rooms')->with('success', 'Room created successfully.');
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
        $room = Room::findOrFail($id);
        $locations = LocationDataCenter::select('id', 'name')->get();
        return Inertia::render('rooms/RoomEdit', [
            'title' => 'Edit Room',
            'room' => $room,
            'locations' => $locations,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'code' => 'required|unique:rooms,code,' . $id,
            'name' => 'required',
            'location_id' => 'required|exists:location_data_centers,id',
            'description' => 'nullable',
        ]);

        $room = Room::findOrFail($id);
        $room->update([
            'code' => $request->code,
            'name' => $request->name,
            'location_data_center_id' => $request->location_id,
            'description' => $request->description,
        ]);

        return redirect('admin/rooms')->with('success', 'Room updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $room = Room::findOrFail($id);
            $room->delete();
            return redirect('admin/rooms')->with('success', 'Room deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return back()->with('error', 'Cannot delete this room because it has active associated resources (e.g. racks or interconnections).');
            }
            throw $e;
        }
    }
}
