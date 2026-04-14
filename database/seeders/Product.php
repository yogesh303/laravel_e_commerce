<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Products;

class Product extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         Products::create([
                'name' => 'ipone 15',
                'price' => 47000,
                'description' => 'abcadasdas dsadsadasdsa dsadsadsadsadasd dasdasdasdasdasd',
                'stock' => 10,
                'image' => 0,
            ]);
        Products::create([
                'name' => 'ipone 16',
                'price' => 56000,
                'description' => 'abcadasdas dsadsadasdsa dsadsadsadsadasd dasdasdasdasdasd',
                'stock' => 7,
                'image' => 0,
            ]);
    }
}
