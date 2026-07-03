<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRadianDSPlanFieldsToPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('type_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('qty_docs_radian')->after('qty_docs_payroll')->default(0);
            $table->unsignedBigInteger('qty_docs_ds')->after('qty_docs_radian')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('type_plans', function (Blueprint $table) {
            $table->dropColumn('qty_docs_radian');
            $table->dropColumn('qty_docs_ds');
        });
    }
}
