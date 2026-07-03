<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToItemsAndCustomers  extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('companies_id')->nullable();
            $table->foreign('companies_id')->references('id')->on('companies');
            $table->dropColumn(['stock', 'stock_min']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('companies_id')->nullable();
            $table->foreign('companies_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['companies_id']);
            $table->dropColumn('companies_id');
            $table->integer('stock')->nullable();
            $table->integer('stock_min')->nullable();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['companies_id']);
            $table->dropColumn('companies_id');
        });
    }
}