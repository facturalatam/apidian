<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSaleSubtotalTotalToReceivedDocuments2 extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'float');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('received_documents', function (Blueprint $table) {
            $table->float('sale', 15, 2)->change();
            $table->float('subtotal', 15, 2)->change();
            $table->float('total', 15, 2)->change();
            $table->float('total_discount', 15, 2)->change();
            $table->float('total_tax', 15, 2)->change();
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
            $table->float('sale', 10, 2)->change();
            $table->float('subtotal', 10, 2)->change();
            $table->float('total', 10, 2)->change();
            $table->float('total_discount', 10, 2)->change();
            $table->float('total_tax', 10, 2)->change();
        });
    }
}
