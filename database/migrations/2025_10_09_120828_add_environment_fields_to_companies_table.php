<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnvironmentFieldsToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('support_document_type_environment_id')->nullable()->after('eqdocs_type_environment_id');
            $table->unsignedBigInteger('event_type_environment_id')->nullable()->after('support_document_type_environment_id');
            
            $table->foreign('support_document_type_environment_id')->references('id')->on('type_environments');
            $table->foreign('event_type_environment_id')->references('id')->on('type_environments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['support_document_type_environment_id']);
            $table->dropForeign(['event_type_environment_id']);
            
            $table->dropColumn(['support_document_type_environment_id', 'event_type_environment_id']);
        });
    }
}
