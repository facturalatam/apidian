<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Change2SaleSubtotalTotalToDocuments extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'decimal');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->decimal('sale', 18, 2)->change();
            $table->decimal('subtotal', 18, 2)->change();
            $table->decimal('total', 18, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->float('sale', 10, 2)->change();
            $table->float('subtotal', 10, 2)->change();
            $table->float('total', 10, 2)->change();
        });
    }
}
