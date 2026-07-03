<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveAsteriskFromTaxNameId15 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            if (!Schema::hasTable('taxes')) {
                return;
            }

            DB::table('taxes')
                ->where('id', 15)
                ->where(function ($query) {
                    $query->where('code', 'ZZ')
                        ->orWhere('code', "ZZ\r");
                })
                ->update([
                    'name' => 'No aplica',
                    'code' => 'ZZ',
                ]);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            if (!Schema::hasTable('taxes')) {
                return;
            }

            DB::table('taxes')
                ->where('id', 15)
                ->where(function ($query) {
                    $query->where('code', 'ZZ')
                        ->orWhere('code', "ZZ\r");
                })
                ->update([
                    'name' => 'No aplica *',
                    'code' => 'ZZ',
                ]);
        } catch (\Exception $e) {
            return;
        }
    }
}
