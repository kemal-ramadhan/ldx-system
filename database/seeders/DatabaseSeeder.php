<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Role::insert([
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Client',
                'slug' => 'client',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Teknisi',
                'slug' => 'teknisi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        User::factory()->create([
            'role_id' => 1,
            'name' => 'Kemal Ramadhan',
            'email' => 'km.kemal03@gmail.com',
            'password' => bcrypt('Kk1617102084'),
            'email_verified_at' => now(),
        ]);
    }
}
