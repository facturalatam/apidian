<?php

use App\TypeLiability;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class UpdateNameColumnToTypeLiabilitiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        TypeLiability::where('id', 14)->update(['name' => 'Agente de retención IVA']);
        TypeLiability::where('id', 112)->update(['name' => 'Régimen simple de tributación']);
        TypeLiability::where('id', 117)->update(['name' => 'No aplica – Otros']);
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
