<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Check if the developer admin account already exists
        // to prevent duplicate entries on re-seeding
        $exists = DB::table('admins')
            ->where('email', 'your_email@example.com')
            ->exists();

        if (!$exists) {
            DB::table('admins')->insert([
                'surname'        => 'Prime',
                'given_name'     => 'Optimus',
                'middle_initial' => 'X',
                'username'       => 'wra_a_A_a_argh',
                'email'          => 'optimus_prime@gmail.com',
                'contact_number' => '09786756432',
                'backup_email'   => 'bumbleb33@gmail.com.com',
                'password'       => Hash::make('0pt1mum_Pr1d3'),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}
