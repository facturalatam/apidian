<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMailFromFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'mail_from_address')) {
                $table->string('mail_from_address')->nullable()->default('');
            }
            if (!Schema::hasColumn('users', 'mail_from_name')) {
                $table->string('mail_from_name')->nullable()->default('');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mail_from_address')) {
                $table->dropColumn('mail_from_address');
            }
            if (Schema::hasColumn('users', 'mail_from_name')) {
                $table->dropColumn('mail_from_name');
            }
        });
    }
}
