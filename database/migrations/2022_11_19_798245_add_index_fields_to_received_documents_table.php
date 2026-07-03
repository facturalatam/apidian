<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexFieldsToReceivedDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('received_documents', function (Blueprint $table) {
            $table->index('identification_number');
            $table->index('customer');
            $table->index('prefix');
            $table->index('number');
            $table->index('cufe');
            $table->index('type_document_id');
            $table->index('date_issue');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('received_documents', function (Blueprint $table) {
            $table->dropIndex('identification_number');
            $table->dropIndex('customer');
            $table->dropIndex('prefix');
            $table->dropIndex('number');
            $table->dropIndex('cufe');
            $table->dropIndex('type_document_id');
            $table->dropIndex('date_issue');
        });
    }
}
