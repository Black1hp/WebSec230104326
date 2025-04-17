<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateManageSalesPermission extends Migration
{
    public function up()
    {
        DB::table('permissions')->where('name', 'manage_sales')
            ->update(['guard_name' => 'web']);
    }

    public function down()
    {
        //
    }
};
