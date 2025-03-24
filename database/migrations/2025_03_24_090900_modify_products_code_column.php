<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->dropColumn('code');
        });
    }
}; 