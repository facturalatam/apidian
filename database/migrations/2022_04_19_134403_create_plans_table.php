<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('type_plans', function(Blueprint $table) {
            $table->unsignedBigInteger('id')->unique();
            $table->string('name')->unique();
            $table->unsignedBigInteger('qty_docs_invoice')->default(0);
            $table->unsignedBigInteger('qty_docs_payroll')->default(0);
            $table->unsignedBigInteger('period')->default(0);  // 0 - Default, 1 - Monthly, 2 - Yearly, 3 - Package
            $table->boolean('state')->default(true);
            $table->string('observations')->nullable();
            $table->timestamps();
        });

        $table_name = 'type_plans';
        DB::table($table_name)->delete();
        $prefix = 'csv';
        $key = $table_name;
        $table = [
            'columns' => 'id, name, qty_docs_invoice, qty_docs_payroll, period, state, observations, @created_at, @updated_at',
        ];
        $rutafile = public_path($prefix.DIRECTORY_SEPARATOR."{$key}.{$prefix}");
        $rutafile = str_replace('\\', '/', $rutafile);
        DB::connection()
            ->getpdo()
            ->exec("LOAD DATA LOCAL INFILE '".$rutafile."' INTO TABLE $key({$table['columns']}) SET created_at = NOW(), updated_at = NOW()");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('type_plans');
    }
}
