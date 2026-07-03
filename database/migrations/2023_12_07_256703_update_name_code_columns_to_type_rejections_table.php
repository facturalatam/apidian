<?php

use App\TypeRejection;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;

class UpdateNameCodeColumnsToTypeRejectionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        TypeRejection::where('id', '1')->update(['name' => 'Documento con inconsistencias', 'code' => '1']);
        TypeRejection::where('id', '2')->update(['name' => 'Mercancía no entregada', 'code' => '2']);
        TypeRejection::where('id', '3')->update(['name' => 'Mercancía  entregada parcialmente', 'code' => '3']);
        TypeRejection::where('id', '4')->update(['name' => 'Servicio no prestado', 'code' => '4']);
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        TypeRejection::where('id', '1')->update(['name' => 'Documento con inconsistencias', 'code' => '01']);
        TypeRejection::where('id', '2')->update(['name' => 'Mercancía no entregada totalmente', 'code' => '02']);
        TypeRejection::where('id', '3')->update(['name' => 'Mercancía  entregada parcialmente', 'code' => '03']);
        TypeRejection::where('id', '4')->update(['name' => 'Servicio no prestado', 'code' => '04']);
	}
}
