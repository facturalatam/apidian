<?php

use Illuminate\Database\Migrations\Migration;
use App\TypeLiability;

class UpdateTypeLiability117Name extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        TypeLiability::where('id', 117)->update(['name' => 'No aplica – Otros']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        TypeLiability::where('id', 117)->update(['name' => 'No responsable']);
    }
}
