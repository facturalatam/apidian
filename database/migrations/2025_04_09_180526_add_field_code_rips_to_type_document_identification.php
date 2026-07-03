<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldCodeRipsToTypeDocumentIdentification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('type_document_identifications', function (Blueprint $table) {
            $table->string('code_rips', 2)->nullable()->after('code');
        });

        $data = [
            ['id' => 1, 'name' => 'Registro civil', 'code' => '11', 'code_rips' => 'RC'],
            ['id' => 2, 'name' => 'Tarjeta de identidad', 'code' => '12', 'code_rips' => 'TI'],
            ['id' => 3, 'name' => 'Cédula de ciudadanía', 'code' => '13', 'code_rips' => 'CC'],
            ['id' => 4, 'name' => 'Tarjeta de extranjería', 'code' => '21', 'code_rips' => 'CN'],
            ['id' => 5, 'name' => 'Cédula de extranjería', 'code' => '22', 'code_rips' => 'CE'],
            ['id' => 6, 'name' => 'NIT', 'code' => '31', 'code_rips' => 'NI'],
            ['id' => 7, 'name' => 'Pasaporte', 'code' => '41', 'code_rips' => 'PA'],
            ['id' => 8, 'name' => 'Documento de identificación extranjero', 'code' => '42', 'code_rips' => 'DE'],
            ['id' => 9, 'name' => 'NIT de otro país', 'code' => '50', 'code_rips' => 'NI'],
            ['id' => 11, 'name' => 'PEP (Permiso Especial de Permanencia)', 'code' => '47', 'code_rips' => 'PE'],
            ['id' => 12, 'name' => 'PPT (Permiso Protección Temporal)', 'code' => '48', 'code_rips' => 'PT'],
        ];

        foreach ($data as $item) {
            DB::table('type_document_identifications')->updateOrInsert(
                ['id' => $item['id']],
                $item
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('type_document_identification', function (Blueprint $table) {
            $table->dropColumn('code_rips');
        });
    }
}
