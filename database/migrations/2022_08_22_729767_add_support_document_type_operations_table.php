<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\TypeOperation;

class AddSupportDocumentTypeOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sdcn = TypeOperation::updateOrCreate(['id' => 23, 'name' => 'Residente', 'code' => 10]);
        $sdcn->id = 23;
        $sdcn->save();
        $sdcn = TypeOperation::updateOrCreate(['id' => 24, 'name' => 'No Residente', 'code' => 11]);
        $sdcn->id = 24;
        $sdcn->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sdcn = TypeOperation::where('id', 23)->get();
        if(count($sdcn) > 0)
            $sdcn[0]->delete();
        $sdcn = TypeOperation::where('id', 24)->get();
        if(count($sdcn) > 0)
            $sdcn[0]->delete();
    }
}
