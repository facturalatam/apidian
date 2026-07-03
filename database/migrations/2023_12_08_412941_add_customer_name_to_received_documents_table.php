<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerNameToReceivedDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('received_documents', function (Blueprint $table) {
            $table->string('customer_name', 255)->after('customer')->nullable();
            $table->json('request_api')->nullable();
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
            $table->dropColumn('customer_name');
            $table->dropColumn('request_api');
        });
    }
}
