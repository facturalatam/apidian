<?php

use App\TypeOperation;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;

class UpdateTypeOperationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        TypeOperation::where('code', '22')->update(['id' => 8, 'name' => 'Nota Crédito sin referencia a una factura electrónica', 'code' => '22']);
        TypeOperation::where('code', '32')->update(['id' => 5, 'name' => 'Nota Débito sin referencia a una factura electrónica', 'code' => '32']);
        TypeOperation::create(['name' => 'Notarios', 'code' => '14']);
        TypeOperation::where('code', '14')->update(['id' => 25, 'name' => 'Notarios', 'code' => '14']);
        TypeOperation::create(['name' => 'Compra Divisas', 'code' => '15']);
        TypeOperation::where('code', '15')->update(['id' => 26, 'name' => 'Compra Divisas', 'code' => '15']);
        TypeOperation::create(['name' => 'Venta Divisas', 'code' => '16']);
        TypeOperation::where('code', '16')->update(['id' => 27, 'name' => 'Venta Divisas', 'code' => '16']);
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        TypeOperation::where('code', '22')->update(['id' => 8, 'name' => 'Nota Crédito sin referencia a facturas', 'code' => '22']);
        TypeOperation::where('code', '32')->update(['id' => 5, 'name' => 'Nota Débito sin referencia a facturas', 'code' => '32']);
        TypeOperation::destroy(25);
        TypeOperation::destroy(26);
        TypeOperation::destroy(27);
	}
}
