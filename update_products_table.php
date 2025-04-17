<?php

// Load the Laravel application
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    // Check if the amount column already exists in the products table
    if (!Schema::hasColumn('products', 'amount')) {
        // Add the amount column to the products table
        Schema::table('products', function (Blueprint $table) {
            $table->integer('amount')->default(0)->after('price');
        });
        echo "Added amount column to products table.\n";
        
        // Update all existing products with a default amount of 10
        DB::table('products')->update(['amount' => 10]);
        echo "Set default amount of 10 for all existing products.\n";
    } else {
        echo "The amount column already exists in the products table.\n";
    }
    
    echo "Database update completed successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 