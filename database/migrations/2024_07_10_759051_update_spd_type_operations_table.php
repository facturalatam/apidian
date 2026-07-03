<?php

use App\TypeOperation;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;

class UpdateSPDTypeOperationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        TypeOperation::create(['id' => 28, 'name' => 'facturación Normal', 'code' => '601']);
        TypeOperation::where('code', '601')->update(['id' => 28, 'name' => 'facturación Normal', 'code' => '601']);
        TypeOperation::create(['id' => 29, 'name' => 'facturación en Sitio', 'code' => '602']);
        TypeOperation::where('code', '602')->update(['id' => 29, 'name' => 'facturación en Sitio', 'code' => '602']);
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        TypeOperation::destroy(28);
        TypeOperation::destroy(29);
	}
}
