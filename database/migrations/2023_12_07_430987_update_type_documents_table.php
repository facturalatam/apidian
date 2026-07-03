<?php

use App\TypeDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;

class UpdateTypeDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        TypeDocument::create(['name' => 'Eventos (ApplicationResponse)', 'code' => '96']);
        TypeDocument::where('code', '96')->update(['id' => 14, 'name' => 'Eventos (ApplicationResponse)', 'code' => '96']);
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        TypeDocument::destroy(14);
	}
}
