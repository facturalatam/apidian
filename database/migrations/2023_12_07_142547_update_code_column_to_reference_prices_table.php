<?php

use App\ReferencePrice;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;

class UpdateCodeColumnToReferencePricesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        ReferencePrice::where('id', '1')->update(['code' => '1']);
        ReferencePrice::where('id', '2')->update(['code' => '2']);
        ReferencePrice::where('id', '3')->update(['code' => '3']);
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        ReferencePrice::where('id', '1')->update(['code' => '01']);
        ReferencePrice::where('id', '2')->update(['code' => '02']);
        ReferencePrice::where('id', '3')->update(['code' => '03']);
	}
}
