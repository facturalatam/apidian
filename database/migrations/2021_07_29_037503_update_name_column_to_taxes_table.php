<?php

use App\Tax;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class UpdateNameColumnToTaxesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Tax::where('id', 10)->update(['name' => 'INC Bolsas', 'description' => 'Impuesto Nacional al Consumo de Bolsa Plástica']);
        Tax::where('id', 2)->update(['description' => 'Impuesto al Consumo Departamental Nominal']);
        Tax::where('id', 10)->update(['description' => 'Impuesto Nacional al Consumo de Bolsa Plástica']);
        Tax::where('id', 11)->update(['description' => 'Impuesto Nacional del Carbono']);
        DB::table('taxes')->updateOrInsert(['id' => '16', 'name' => 'IC Porcentual', 'description' => 'Impuesto al Consumo de Datos', 'code' => '30', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('taxes')->updateOrInsert(['id' => '17', 'name' => 'IC Datos', 'description' => 'Impuesto al Consumo Departamental Porcentual', 'code' => '08', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('taxes')->updateOrInsert(['id' => '18', 'name' => 'IVA e INC', 'description' => 'IVA e INC', 'code' => 'ZA', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
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
