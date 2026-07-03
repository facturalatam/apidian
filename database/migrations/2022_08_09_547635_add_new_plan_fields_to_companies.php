<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewPlanFieldsToCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('type_plan2_id')->after('type_plan_id');
            $table->foreign('type_plan2_id')->references('id')->on('type_plans')->onDelete('cascade');
            $table->unsignedBigInteger('type_plan3_id')->after('type_plan2_id');
            $table->foreign('type_plan3_id')->references('id')->on('type_plans')->onDelete('cascade');
            $table->unsignedBigInteger('type_plan4_id')->after('type_plan3_id');
            $table->foreign('type_plan4_id')->references('id')->on('type_plans')->onDelete('cascade');
            $table->bigInteger('absolut_plan_documents')->after('type_plan4_id')->default(0);
            $table->dateTime('start_plan_date2')->after('start_plan_date')->nullable();
            $table->dateTime('start_plan_date3')->after('start_plan_date2')->nullable();
            $table->dateTime('start_plan_date4')->after('start_plan_date3')->nullable();
            $table->dateTime('absolut_start_plan_date')->after('start_plan_date4')->nullable();
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
            $table->dropForeign(['type_plan2_id']);
            $table->dropColumn('type_plan2_id');
            $table->dropForeign(['type_plan3_id']);
            $table->dropColumn('type_plan3_id');
            $table->dropForeign(['type_plan4_id']);
            $table->dropColumn('type_plan4_id');
            $table->dropColumn('absolut_plan_documents');
            $table->dropColumn('start_plan_date2');
            $table->dropColumn('start_plan_date3');
            $table->dropColumn('start_plan_date4');
            $table->dropColumn('absolut_start_plan_date');
        });
    }
}
