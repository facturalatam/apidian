<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixExistingTypeDiscountsMissingDescuentos extends Migration
{
    /**
     * Run the migrations.
     *
     * En instalaciones anteriores la migración 2025_09_04 hizo INSERT sin id explícito.
     * Si la tabla estaba vacía, MySQL asignó id=1 (code 02) e id=2 (code 03), ocupando
     * los ids que el CSV del ConfigurationSeeder reserva para los descuentos (00, 01).
     * El LOAD DATA INFILE del seeder falló silenciosamente por duplicate key (PDO en
     * modo silencioso) y los códigos 00 y 01 nunca quedaron registrados.
     *
     * Esta migración detecta ese estado roto y deja la tabla en el orden canónico:
     *   id=1 → Descuento no condicionado (00)
     *   id=2 → Descuento condicionado    (01)
     *   id=3 → Recargo no condicionado   (02)
     *   id=4 → Recargo condicionado      (03)
     *
     * En instalaciones nuevas (donde la migración 2025_09_04 ya inserta con id=3 e
     * id=4) no hace nada.
     *
     * @return void
     */
    public function up()
    {
        $brokenLegacyState = DB::table('type_discounts')->where('id', 1)->where('code', '02')->exists()
            && DB::table('type_discounts')->where('id', 2)->where('code', '03')->exists()
            && ! DB::table('type_discounts')->whereIn('code', ['00', '01'])->exists();

        if (! $brokenLegacyState) {
            return;
        }

        DB::transaction(function () {
            // Mover los recargos a sus ids canónicos (4 primero para no chocar con 2).
            DB::table('type_discounts')->where('id', 2)->update(['id' => 4]);
            DB::table('type_discounts')->where('id', 1)->update(['id' => 3]);

            // Insertar los descuentos faltantes en los ids que quedan libres.
            DB::table('type_discounts')->insert([
                [
                    'id' => 1,
                    'name' => 'Descuento no condicionado',
                    'code' => '00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'name' => 'Descuento condicionado',
                    'code' => '01',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Solo revierte si la tabla quedó exactamente en el estado canónico que dejó esta
     * migración. Borra los descuentos y devuelve los recargos a id=1 e id=2.
     *
     * @return void
     */
    public function down()
    {
        $canonicalState = DB::table('type_discounts')->where('id', 1)->where('code', '00')->exists()
            && DB::table('type_discounts')->where('id', 2)->where('code', '01')->exists()
            && DB::table('type_discounts')->where('id', 3)->where('code', '02')->exists()
            && DB::table('type_discounts')->where('id', 4)->where('code', '03')->exists();

        if (! $canonicalState) {
            return;
        }

        DB::transaction(function () {
            DB::table('type_discounts')->whereIn('id', [1, 2])->delete();
            DB::table('type_discounts')->where('id', 3)->update(['id' => 1]);
            DB::table('type_discounts')->where('id', 4)->update(['id' => 2]);
        });
    }
}
