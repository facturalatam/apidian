<?php

use App\Municipality;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class UpdateCodefacturadorColumn3ToMunicipalitiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Municipality::where('name', 'Yotoco')->update(['codefacturador' => 48354]);
        Municipality::where('name', 'Yumbo')->update(['codefacturador' => 48355]);
        Municipality::where('name', 'Yopal')->update(['codefacturador' => 12918]);
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}
}
