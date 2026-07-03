<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('status')->default(false);
            $table->boolean('can_rips')->default(false);
            $table->boolean('can_health')->default(false);
            $table->string('code_service_provider')->nullable();
            $table->string('document_number')->nullable();
            $table->unsignedBigInteger('document_type_id')->nullable();

            $table->foreign('document_type_id')->references('id')->on('health_type_document_identifications');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_user');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('can_rips');
            $table->dropColumn('can_health');
            $table->dropColumn('code_service_provider');
            $table->dropColumn('document_number');
            $table->dropForeign(['document_type_id']);
            $table->dropColumn('document_type_id');
        });
    }
}
