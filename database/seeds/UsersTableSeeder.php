<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ensure the admin user exists
        $user = User::where('email', 'admin@kslang.com')->first();

        if (!$user) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@kslang.com',
                'password' => Hash::make('R@W9tn!232323'),
            ]);
        } else {
            $user->update([
                'password' => Hash::make('R@W9tn!232323'),
            ]);
        }
    }
}

