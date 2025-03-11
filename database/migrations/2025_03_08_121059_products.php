<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('code', 64);
            $table->string('name', 256);
            $table->unsignedInteger('price');
            $table->string('model', 128);
            $table->text('description')->nullable();
            $table->string('photo', 128)->nullable();
            $table->timestamps(); // Includes created_at & updated_at
            $table->softDeletes(); // Adds deleted_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};

