<?php

use App\TypeDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class UpdateAlgorithmValueCUDSToTypeDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        TypeDocument::where('id', 11)->update(['cufe_algorithm' => 'CUDS-SHA384']);
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        TypeDocument::where('id', 11)->update(['cufe_algorithm' => 'CUDE-SHA384']);
	}
}
