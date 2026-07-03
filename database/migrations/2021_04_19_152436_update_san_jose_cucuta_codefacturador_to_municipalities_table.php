<?php

use App\Municipality;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSanJoseCucutaCodefacturadorToMunicipalitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Municipality::where('id', 780)->update(['codefacturador' => 48390]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Municipality::where('id', 780)->update(['codefacturador' => 12879]);
    }
}
