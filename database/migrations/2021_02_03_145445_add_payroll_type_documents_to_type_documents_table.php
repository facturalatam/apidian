<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\TypeDocument;

class AddPayrollTypeDocumentsToTypeDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        TypeDocument::updateOrCreate(
//            ['id' => 9],
//            ['name' => 'Nomina Individual',
//             'code' => '1',
//             'cufe_algorithm' => 'CUNE-SHA384',
//             'prefix' => 'ni']
//        );
//
//        TypeDocument::updateOrCreate(
//            ['id' => 10],
//            ['name' => 'Nomina Individual de Ajuste',
//             'code' => '2',
//             'cufe_algorithm' => 'CUNE-SHA384',
//             'prefix' => 'na']
//        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('type_documents', function (Blueprint $table) {
            //
        });
    }
}
