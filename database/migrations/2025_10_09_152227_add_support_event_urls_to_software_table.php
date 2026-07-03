<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupportEventUrlsToSoftwareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('software', function (Blueprint $table) {
            $table->string('url_support_document')->nullable()->after('url_eqdocs')->default('https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc');
            $table->string('identifier_support_document')->nullable()->after('url_support_document');
            $table->string('pin_support_document')->nullable()->after('identifier_support_document');
            $table->string('url_event')->nullable()->after('pin_support_document')->default('https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc');
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
            $table->dropColumn([
                'url_support_document',
                'identifier_support_document', 
                'pin_support_document',
                'url_event'
            ]);
        });
    }
}
