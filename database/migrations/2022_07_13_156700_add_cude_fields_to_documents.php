<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCudeFieldsToDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('acu_recibo');
            $table->dropColumn('rec_bienes');
            $table->dropColumn('rechazo');
            $table->string('cude_aceptacion')->nullable();
            $table->json('payload_aceptacion')->nullable();
        });

        Schema::table('received_documents', function (Blueprint $table) {
            $table->string('cude_acu_recibo')->nullable();
            $table->json('payload_acu_recibo')->nullable();
            $table->string('cude_rec_bienes')->nullable();
            $table->json('payload_rec_bienes')->nullable();
            $table->string('cude_aceptacion')->nullable();
            $table->json('payload_aceptacion')->nullable();
            $table->string('cude_rechazo')->nullable();
            $table->json('payload_rechazo')->nullable();
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
            $table->boolean('acu_recibo')->default(false);
            $table->boolean('rec_bienes')->default(false);
            $table->boolean('rechazo')->default(false);
            $table->dropColumn('cude_aceptacion');
            $table->dropColumn('payload_aceptacion');
        });

        Schema::table('received_documents', function (Blueprint $table) {
            $table->dropColumn('cude_acu_recibo');
            $table->dropColumn('cude_rec_bienes');
            $table->dropColumn('cude_aceptacion');
            $table->dropColumn('cude_rechazo');
            $table->dropColumn('payload_acu_recibo');
            $table->dropColumn('payload_rec_bienes');
            $table->dropColumn('payload_aceptacion');
            $table->dropColumn('payload_rechazo');
        });
    }
}
