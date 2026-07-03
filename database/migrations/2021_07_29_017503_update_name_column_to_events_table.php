<?php

use App\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class UpdateNameColumnToEventsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Event::where('id', 1)->update(['name' => 'Acuse de recibo de Factura Electrónica de Venta']);
        Event::where('id', 2)->update(['name' => 'Reclamo de la Factura Electrónica de Venta']);
        Event::where('id', 3)->update(['name' => 'Recibo del bien o prestación del servicio']);
        Event::where('id', 4)->update(['name' => 'Aceptación expresa']);
        DB::table('events')->updateOrInsert(['id' => '5', 'name' => 'Aceptación Tácita', 'code' => '034', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('events')->updateOrInsert(['id' => '6', 'name' => 'Documento validado por la DIAN', 'code' => '02', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        DB::table('events')->updateOrInsert(['id' => '7', 'name' => 'Documento rechazado por la DIAN', 'code' => '04', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
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
