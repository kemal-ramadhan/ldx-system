<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Client;
use App\Models\ClientPic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        
        return DB::transaction(function () use ($input) {
            
            // Cari role Client
            $role = DB::table('roles')->where('slug', 'client')->first();
            
            // Buat user
            $user = User::create([
                'role_id' => $role->id,
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => bcrypt($input['password']),
            ]);

            // Generate company code 6 karakter
            do {
                $companyCode = strtoupper(Str::random(6));
            } while (
                Client::query()->where('company_code', $companyCode)->exists()
            );

            // Buat client
            $client = Client::create([
                'company_code' => $companyCode,
                'company_name' => $user->name,
            ]);

            // Buat client pic
            ClientPic::create([
                'user_id' => $user->id,
                'client_id' => $client->id,
                'status' => 'active',
            ]);

            return $user;
        });
    }
}
