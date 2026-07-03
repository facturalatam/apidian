<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSdFieldsToSoftware extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('software', function (Blueprint $table) {
            $table->string('identifier_sd')->after('pin_payroll');
            $table->string('pin_sd')->after('identifier_sd');
            $table->string('url_sd')->after('url_payroll');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('software', function (Blueprint $table) {
            $table->dropColumn('identifier_sd');
            $table->dropColumn('pin_sd');
            $table->dropColumn('url_sd');
        });
    }
}
