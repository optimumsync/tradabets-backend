<?php

use Illuminate\Database\Seeder;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        User::create([
            'first_name' => 'super',
            'last_name' => 'admin',
            'date_of_birth' => '1999-1-1',
            'email' => 'superadmin@tradabet.com',
            'password' => bcrypt('superadmin@tradabet.com'),
            'phone' => '9876543210',
            'city' => 'Angel',
            'state' => 'Lagos',
            'country' => 'Nigeria',
            'role' => 'admin',
            'google2fa_secret' => null,
        ]);
    }
}
