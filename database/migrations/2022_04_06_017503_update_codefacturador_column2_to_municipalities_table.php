<?php

use App\Municipality;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class UpdateCodefacturadorColumn2ToMunicipalitiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Municipality::where('name', 'Bucarasica')->update(['codefacturador' => 48362]);
        Municipality::where('name', 'Buenaventura')->update(['codefacturador' => 48320]);
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
