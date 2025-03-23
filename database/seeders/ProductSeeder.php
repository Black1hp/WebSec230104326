<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get all image files from the images directory
        $images = glob(public_path('images') . '/*.*');

        // Create 10 fake products
        for ($i = 0; $i < 10; $i++) {
            // Get a random image from the directory
            $randomImage = $images ? basename($faker->randomElement($images)) : 'default.jpg';

            DB::table('products')->insert([
                'name' => $faker->words(3, true),
                'description' => $faker->paragraph(3),
                'price' => $faker->randomFloat(2, 10, 1000),
                'image' => 'images/' . $randomImage,
                'category' => $faker->randomElement(['Electronics', 'Clothing', 'Home', 'Books', 'Sports']),
                'stock' => $faker->numberBetween(0, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
