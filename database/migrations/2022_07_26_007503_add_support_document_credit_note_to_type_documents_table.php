<?php

use App\TypeDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class AddSupportDocumentCreditNoteToTypeDocumentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $sdcn = TypeDocument::updateOrCreate(['id' => 13, 'name' => 'Nota de Ajuste al Documento Soporte Electrónico', 'code' => 95, 'cufe_algorithm' => 'CUDS-SHA384', 'prefix' => 'nds']);
        $sdcn->id = 13;
        $sdcn->save();
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        $sdcn = TypeDocument::where('id', 13)->get();
        if(count($sdcn) > 0)
            $sdcn[0]->delete();
	}
}
