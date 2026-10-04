<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\Role;
use App\Models\Client;
use App\Models\ClientPic;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $role = $request->role;
        $type = $request->type;

        $users = User::with(['role', 'clientPic.client'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })

            ->when($role, function ($query) use ($role) {
                $query->whereHas('role', function ($q) use ($role) {
                    $q->where('slug', $role);
                });
            })
            
            ->when($type === 'client', function ($query) {
                $query->whereHas('role', function ($q) {
                    $q->where('slug', 'client');
                });
            })

            ->when($type === 'admin', function ($query) {
                $query->whereHas('role', function ($q) {
                    $q->where('slug', '!=', 'client');
                });
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('users/Users', [
            'title' => $type === 'client' ? 'Data User Client' : ($type === 'admin' ? 'Data User Admin' : 'User Management'),

            'users' => $users,

            'filters' => [
                'search' => $search,
                'role' => $role,
                'type' => $type,
            ],

            'roles' => Role::select('id', 'name', 'slug')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('users/Create', [
            'title' => 'Create User',
            'roles' => Role::all(),
        ]);
    }

    function createClient($userId)
    {
        $user = User::findOrFail($userId);
        return Inertia::render('users/ClientCreate', [
            'title' => 'Create Client Profile',
            'user' => $user,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed',
            'phone' => 'required',
            'role_id' => 'required',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // =========================
        // Check Role
        // =========================
        $role = Role::findOrFail($validated['role_id']);

        if (!$role) {
            return back()->withErrors([
                'role_id' => 'Invalid role selected'
            ]);
        }

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // =========================
        // Create User
        // =========================
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
            'role_id' => $validated['role_id'],
            'avatar' => $avatarPath,
            'email_verified_at' => now(),
        ]);

        // =========================
        // Redirect if Client
        // =========================
        if ($role->slug === 'client') {

            return redirect()->route('admin.clients.create', [
                'userId' => $user->id
            ]);
        }

        // =========================
        // Default Redirect
        // =========================
        return redirect('/admin/users')
            ->with('success', 'User created');
    }

    public function storeClient(Request $request, $userId)
    {
        $validated = $request->validate([
            'company_name' => 'required',
            'company_email' => 'required|email|unique:clients,company_email',
            'company_phone' => 'nullable',
            'company_npwp' => 'nullable',
            'company_address' => 'required',
            'company_city' => 'required',
            'company_province' => 'required',
            'company_postal_code' => 'required',
            'contract_date' => 'required|date',
            'contract_done_date' => 'nullable|date|after_or_equal:contract_date',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);


        do {
            $companyCode = strtoupper(Str::random(6));
        } while (
            Client::query()->where('company_code', $companyCode)->exists()
        );

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $client = Client::create([
            'company_code' => $companyCode,
            'company_name' => $validated['company_name'],
            'logo' => $logoPath,
            'company_email' => $validated['company_email'],
            'company_phone' => $validated['company_phone'],
            'company_npwp' => $validated['company_npwp'],
            'company_address' => $validated['company_address'],
            'company_city' => $validated['company_city'],
            'company_province' => $validated['company_province'],
            'company_postal_code' => $validated['company_postal_code'],
            'contract_date' => $validated['contract_date'],
            'contract_done_date' => $validated['contract_done_date'],
        ]);

        ClientPic::create([
            'user_id' => $userId,
            'client_id' => $client->id,
            'status' => 'active',
        ]);

        return redirect('/admin/users')
            ->with('success', 'Client profile created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('role')->findOrFail($id);
        
        $companies = [];
        if ($user->role && $user->role->slug === 'client') {
            $companies = \App\Models\ClientPic::where('user_id', $user->id)
                ->with(['client.pics.user'])
                ->get()
                ->map(function ($pic) {
                    return $pic->client;
                });
        }

        return Inertia::render('users/UserShow', [
            'title' => 'Detail User',
            'user' => $user,
            'companies' => $companies,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::with('role')->findOrFail($id);

        return Inertia::render('users/UserEdit', [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => ['required'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    }
}
