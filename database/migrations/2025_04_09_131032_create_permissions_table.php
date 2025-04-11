<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    public function up()
    {
        // Add other roles if necessary
        DB::table('roles')->insert([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        DB::table('roles')->insert([
            'name' => 'Employee',
            'guard_name' => 'web',
        ]);

        // Add the manage_sales permission to the Employee role
        DB::table('model_has_permissions')->insert([
            'model_id' => App\Models\Role::whereName('Employee')->value('id'),
            'permission_name' => 'manage_sales',
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
};
