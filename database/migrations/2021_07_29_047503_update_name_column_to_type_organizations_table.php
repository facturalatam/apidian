<?php

use App\TypeOrganization;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class UpdateNameColumnToTypeOrganizationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        TypeOrganization::where('id', 1)->update(['name' => 'Persona Jurídica y asimiladas']);
        TypeOrganization::where('id', 2)->update(['name' => 'Persona Natural y asimiladas']);
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
