<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Administrator;

class CreateAdministratorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('administrators', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('identification_number')->unique();
            $table->char('dv', 1)->nullable();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('contact_name')->nullable();
            $table->string('password')->nullable();
            $table->string('plan')->nullable();
            $table->boolean('state')->default(true);
            $table->string('observation')->nullable();
            $table->timestamps();
        });
        Administrator::create([
            'identification_number' => '9999999999',
            'dv' => '9',
            'name' => 'DEFAULT ADMINISTRATOR',
            'address' => 'ZZZZZZZZZZZZZZZZZZZZZZZZ',
            'phone' => '9999999',
            'email' => 'default@default.com',
            'contact_name' => 'ZZZZZZZZZZZZZZZZZZZZZZZZ',
            'password' => '',
            'plan' => '',
            'state' => true
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('administrators');
    }
}
