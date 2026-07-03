<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFieldsToTypeDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // Ids 1 y 2 quedan reservados para los descuentos (00, 01) que carga el CSV en ConfigurationSeeder.
        // Si esos ids ya están ocupados (instalación previa rota), se delega el arreglo a la migración
        // 2026_05_26_120000_fix_existing_type_discounts_missing_descuentos.
        DB::table('type_discounts')->insert([
            [
                'id' => 3,
                'name' => 'Recargo no condicionado',
                'code' => '02',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Recargo condicionado',
                'code' => '03',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('type_discounts')->whereIn('id', [3, 4])->delete();
    }
}
