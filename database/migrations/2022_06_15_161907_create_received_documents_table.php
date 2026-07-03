<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceivedDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('received_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('identification_number');
            $table->char('dv', 1);
            $table->string('name_seller', 255);
            $table->unsignedInteger('state_document_id')->default(0);
            $table->unsignedBigInteger('type_document_id');
            $table->foreign('type_document_id')->references('id')->on('type_documents');
            $table->string('customer', 15);
            $table->char('prefix')->nullable();
            $table->string('number');
            $table->string('xml')->nullable();
            $table->string('cufe')->nullable();
            $table->dateTime('date_issue');
            $table->float('sale', 10, 2);
            $table->float('subtotal', 10, 2);
            $table->float('total', 10, 2);
            $table->float('total_discount', 10, 2);
            $table->float('total_tax', 10, 2);
            $table->unsignedInteger('ambient_id');
            $table->string('pdf')->nullable();
            $table->boolean('acu_recibo')->default(false);
            $table->boolean('rec_bienes')->default(false);
            $table->boolean('aceptacion')->default(false);
            $table->boolean('rechazo')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('received_documents');
    }
}
