<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateMunicipalities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('municipalities')->insertOrIgnore([
            'department_id' => 21,
            'name' => 'Guamal',
            'code' => '50318',
            'codefacturador' => 13242
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('municipalities')
            ->where('department_id', 21)
            ->where('code', '50318')
            ->delete();
    }
}
