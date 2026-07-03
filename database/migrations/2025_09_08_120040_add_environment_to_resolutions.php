<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnvironmentToResolutions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resolutions', function (Blueprint $table) {
            $table->unsignedBigInteger('type_environment_id')->nullable()->after('type_document_id');
            $table->foreign('type_environment_id')->references('id')->on('type_environments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resolutions', function (Blueprint $table) {
            $table->dropForeign(['type_environment_id']);
            $table->dropColumn('type_environment_id');
        });
    }
}
