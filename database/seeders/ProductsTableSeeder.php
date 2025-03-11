<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'code' => 'TV01',
                'name' => 'LG TV 50"',
                'model' => 'LG8768787',
                'photo' => 'lgtv50.jpg',
                'description' => 'lorem ipsum..',
                'price' => 1000, // Add this line
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'RF01',
                'name' => 'Toshiba Refrigerator 14"',
                'model' => 'TS76634',
                'photo' => 'tsrf50.jpg',
                'description' => 'lorem ipsum..',
                'price' => 800, // Add this line
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
