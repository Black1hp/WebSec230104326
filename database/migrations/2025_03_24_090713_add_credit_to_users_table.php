<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'credit')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('credit', 10, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credit');
            // Don't drop role if it already exists
        });
    }
};
