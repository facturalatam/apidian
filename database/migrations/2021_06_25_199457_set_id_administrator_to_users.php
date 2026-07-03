<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;

class SetIdAdministratorToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $u = User::where('id', '>', 0)->get();
        foreach($u as $user){
            $user->id_administrator = 1;
            $user->save();
        }
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_administrator')->references('id')->on('administrators');
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
            //
        });
    }
}
