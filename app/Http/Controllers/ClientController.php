<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Client;
use App\Models\ClientPic;
use App\Models\ClientRack;
use App\Models\RackDivice;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $users = Client::with('pics.user')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('company_code', 'like', "%{$search}%")
                        ->orWhere('company_email', 'like', "%{$search}%");
                });
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('clients/Clients', [
            'title' => 'Client Management',

            'clients' => $users,

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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function inviteMember(Request $request, Client $client)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // cek apakah sudah ada
        $exists = ClientPic::query()->where('client_id', $client->id)
            ->where('user_id', '=', $request->user_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'User already added');
        }

        ClientPic::create([
            'client_id' => $client->id,
            'user_id' => $request->user_id,
            'status' => 'active',
        ]);

        return back()->with('success', 'Member invited');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $client = Client::with('pics.user')->findOrFail($id);
        $users = User::select('id', 'name', 'email')->get();
        $racks = ClientRack::with('rack')
            ->where('client_id', $id)
            ->get()
            ->map(function ($rack) {

                $usedUnits = RackDivice::query()->where('client_id', $rack->client_id)
                    ->where('rack_id', $rack->rack_id)
                    ->sum('total_unit');

                $rack->used_units = $usedUnits;
                $rack->available_units = $rack->rented_units - $usedUnits;

                return $rack;
            });
        $devices = RackDivice::with('rack')
            ->where('client_id', $id)
            ->get();
        return Inertia::render('clients/ClientShow', [
            'title' => 'Client Details',
            'client' => $client,
            'users' => $users,
            'racks' => $racks,
            'devices' => $devices
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $client = Client::with('pics.user')->findOrFail($id);
        return Inertia::render('clients/ClientEdit', [
            'title' => 'Edit Client',
            'client' => $client,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $client = Client::findOrFail($id);

        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255|unique:clients,company_email,' . $client->id,
            'company_phone' => 'nullable|string|max:20',
            'company_npwp' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:255',
            'company_city' => 'nullable|string|max:100',
            'company_province' => 'nullable|string|max:100',
            'company_postal_code' => 'nullable|string|max:20',
            'contract_date' => 'nullable|date',
            'contract_done_date' => 'nullable|date',
            'status' => 'required|in:active,inactive',
        ]);

        $client->update($validatedData);

        return redirect('/admin/clients')->with('success', 'updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function deleteMember(ClientPic $pic)
    {
        $pic = ClientPic::findOrFail($pic->id);
        $pic->delete();

        return back()->with('success', 'Member deleted');
    }
}
