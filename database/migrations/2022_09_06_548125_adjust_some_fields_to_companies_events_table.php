<?php

use App\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdjustSomeFieldsToCompaniesEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->bigInteger('absolut_plan_documents')->nullable()->default(0)->change();
        });

        Event::where('id', 1)->update(['name' => 'Acuse de recibo de Factura Electrónica de Venta']);
        Event::where('id', 2)->update(['name' => 'Reclamo de la Factura Electrónica de Venta']);
        Event::where('id', 3)->update(['name' => 'Recibo del bien y/o prestación del servicio']);
        Event::where('id', 4)->update(['name' => 'Aceptación expresa']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->bigInteger('absolut_plan_documents')->default(0)->change();
        });
    }
}
