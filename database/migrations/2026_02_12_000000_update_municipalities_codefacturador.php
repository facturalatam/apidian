<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateMunicipalitiesCodefacturador extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $map = [
            1007 => '48315',
            1008 => '48316',
            1009 => '48317',
            1010 => '48318',
            1011 => '48319',
            1017 => '48326',
            1019 => '48328',
            1020 => '48329',
            1021 => '48330',
            1023 => '48332',
            1024 => '48333',
            1026 => '48335',
            1028 => '48337',
            1029 => '48338',
            1030 => '48339',
            1033 => '48342',
            1034 => '48343',
            1035 => '48344',
            1036 => '48345',
            1037 => '48346',
            1038 => '48347',
            1039 => '48348',
            1040 => '48349',
            1041 => '48350',
            1042 => '48351',
            1043 => '48352',
            1044 => '48353',
            1045 => '48354',
        ];

        DB::transaction(function () use ($map) {
            foreach ($map as $id => $code) {
                DB::table('municipalities')
                    ->where('id', $id)
                    ->update(['codefacturador' => $code]);
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
        $mapOld = [
            1007 => '13364',
            1008 => '13262',
            1009 => '12858',
            1010 => '12920',
            1011 => '13370',
            1017 => '12668',
            1019 => '12573',
            1020 => '12575',
            1021 => '12928',
            1023 => '13239',
            1024 => '13394',
            1026 => '12772',
            1028 => '13287',
            1029 => '13458',
            1030 => '12781',
            1033 => '13304',
            1034 => '13253',
            1035 => '13510',
            1036 => '12979',
            1037 => '13469',
            1038 => '13125',
            1039 => '12957',
            1040 => '12916',
            1041 => '12686',
            1042 => '13147',
            1043 => '13150',
            1044 => '12652',
            1045 => '12918',
        ];

        DB::transaction(function () use ($mapOld) {
            foreach ($mapOld as $id => $code) {
                DB::table('municipalities')
                    ->where('id', $id)
                    ->update(['codefacturador' => $code]);
            }
        });
    }
}
