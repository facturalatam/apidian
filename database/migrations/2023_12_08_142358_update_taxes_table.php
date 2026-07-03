<?php

use App\Tax;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;

class UpdateTaxesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Tax::where('code', '02')->update(['id' => 2, 'description' => 'Impuesto al Consumo Departamental Nominal', 'code' => '02']);
        Tax::where('code', 'ZZ')->update(['id' => 15, 'name' => 'No aplica *', 'description' => 'Otros tributos, tasas, contribuciones, y similares', 'code' => 'ZZ']);
        Tax::create(['name' => 'ICL', 'description' => 'Impuesto al Consumo de Licores','code' => '32']);
        Tax::where('code', '32')->update(['id' => 19, 'name' => 'ICL', 'description' => 'Impuesto al Consumo de Licores','code' => '32']);
        Tax::create(['name' => 'INPP', 'description' => 'Impuesto nacional productos plásticos','code' => '33']);
        Tax::where('code', '33')->update(['id' => 20, 'name' => 'INPP', 'description' => 'Impuesto nacional productos plásticos','code' => '33']);
        Tax::create(['name' => 'IBUA', 'description' => 'Impuesto a las bebidas ultraprocesadas azucaradas','code' => '34']);
        Tax::where('code', '34')->update(['id' => 21, 'name' => 'IBUA', 'description' => 'Impuesto a las bebidas ultraprocesadas azucaradas','code' => '34']);
        Tax::create(['name' => 'ICUI', 'description' => 'Impuesto a los productos comestibles ultraprocesados industrialmente y/o con alto contenido de azúcares añadidos, sodio o grasas saturadas','code' => '35']);
        Tax::where('code', '35')->update(['id' => 22, 'name' => 'ICUI', 'description' => 'Impuesto a los productos comestibles ultraprocesados industrialmente y/o con alto contenido de azúcares añadidos, sodio o grasas saturadas','code' => '35']);
        Tax::create(['name' => 'ADV', 'description' => 'AD VALOREM','code' => '36']);
        Tax::where('code', '36')->update(['id' => 23, 'name' => 'ADV', 'description' => 'AD VALOREM','code' => '36']);
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Tax::where('code', '02')->update(['id' => 2, 'description' => 'Impuesto al Consumo Departamental', 'code' => '02']);
        Tax::where('code', 'ZZ')->update(['id' => 15, 'name' => 'No aplica', 'description' => 'No aplica', 'code' => 'ZZ']);
        Tax::destroy(19);
        Tax::destroy(20);
        Tax::destroy(21);
        Tax::destroy(22);
        Tax::destroy(23);
        Tax::create(['name' => 'IVA e INC', 'description' => 'IVA e INC','code' => 'ZA']);
        Tax::where('code', 'ZA')->update(['id' => 18, 'name' => 'IVA e INC', 'description' => 'IVA e INC','code' => 'ZA']);
	}
}
