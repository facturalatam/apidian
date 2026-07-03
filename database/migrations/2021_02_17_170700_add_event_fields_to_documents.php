<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventFieldsToDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('acu_recibo')->default(false);
            $table->boolean('rec_bienes')->default(false);
            $table->boolean('aceptacion')->default(false);
            $table->boolean('rechazo')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('acu_recibo');
            $table->dropColumn('rec_bienes');
            $table->dropColumn('aceptacion');
            $table->dropColumn('rechazo');
        });
    }
}
