<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, update any products with empty codes
        $products = DB::table('products')->whereNull('code')->orWhere('code', '')->get();
        foreach ($products as $product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update(['code' => 'PROD-' . strtoupper(substr(md5(uniqid() . $product->id), 0, 8))]);
        }
        
        // Then, find any duplicate codes and update them
        $duplicates = DB::table('products')
            ->select('code', DB::raw('COUNT(*) as count'))
            ->whereNotNull('code')
            ->groupBy('code')
            ->having('count', '>', 1)
            ->get();
            
        foreach ($duplicates as $duplicate) {
            $dupeProducts = DB::table('products')
                ->where('code', $duplicate->code)
                ->orderBy('id')
                ->get();
                
            // Skip the first one (keep original code)
            for ($i = 1; $i < count($dupeProducts); $i++) {
                DB::table('products')
                    ->where('id', $dupeProducts[$i]->id)
                    ->update(['code' => 'PROD-' . strtoupper(substr(md5(uniqid() . $dupeProducts[$i]->id), 0, 8))]);
            }
        }

        Schema::table('products', function (Blueprint $table) {
            // First drop the existing code column if it exists
            if (Schema::hasColumn('products', 'code')) {
                $table->dropColumn('code');
            }
            
            // Add the code column with proper constraints
            $table->string('code', 64)->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_code_unique');
            $table->dropColumn('code');
        });
    }
}; 