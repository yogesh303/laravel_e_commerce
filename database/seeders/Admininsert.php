<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class Admininsert extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        if (!User::where('email', 'yogeshkanzariya71@gmail.com')->exists()) {
            User::create([
                'name' => 'Yogesh Kanzariya',
                'email' => 'yogeshkanzariya71@gmail.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]);
        }
        
    }
}
