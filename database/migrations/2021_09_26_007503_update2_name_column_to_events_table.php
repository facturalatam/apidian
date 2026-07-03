<?php

use App\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class Update2NameColumnToEventsTable extends Migration
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
        Event::where('id', 3)->update(['name' => 'Recibo del bien y/o prestación del servicio']);
        Event::where('id', 4)->update(['name' => 'Aceptación expresa']);
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
