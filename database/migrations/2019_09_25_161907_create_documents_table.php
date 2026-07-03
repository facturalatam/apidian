<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('state_document_id')->default(1);
           // $table->foreign('state_document_id')->references('id')->on('state_documents');
            $table->unsignedBigInteger('type_document_id');
            $table->foreign('type_document_id')->references('id')->on('type_documents');
            $table->char('prefix')->nullable();
            $table->string('number');
            $table->string('xml')->nullable();
            $table->string('cufe')->nullable();
//            $table->unique(['prefix', 'number']);
            $table->unsignedInteger('type_invoice_id')->nullable();
           // $table->foreign('type_invoice_id')->references('id')->on('type_invoices');
            $table->unsignedInteger('client_id');
          //  $table->foreign('client_id')->references('id')->on('clients');
            $table->json('client');
            $table->unsignedInteger('currency_id');
           // $table->foreign('currency_id')->references('id')->on('currencies');
            $table->dateTime('date_issue');
            $table->unsignedInteger('reference_id')->nullable();
            //$table->foreign('reference_id')->references('id')->on('documents');
            $table->unsignedInteger('note_concept_id')->nullable();
           // $table->foreign('note_concept_id')->references('id')->on('note_concepts');
            $table->double('sale', 10, 2);
            $table->decimal('total_discount', 10, 2);
            $table->json('taxes')->nullable();
            $table->decimal('total_tax', 10, 2);
            $table->double('subtotal', 10, 2);
            $table->double('total', 10, 2);
            $table->unsignedInteger('version_ubl_id');
           // $table->foreign('version_ubl_id')->references('id')->on('version_ubls');
            $table->unsignedInteger('ambient_id');
           // $table->foreign('ambient_id')->references('id')->on('ambients');

            $table->json('request_api')->nullable();
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
        Schema::dropIfExists('documents');
    }
}
