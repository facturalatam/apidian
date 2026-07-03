<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Company;
use App\Customer;
use App\User;

class AddDefaultCustomerToExistingCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Primero modificar la estructura de la tabla
        // Algunos clientes tienen FK documents_customer_foreign -> customers.identification_number
        // MySQL bloquea DROP PRIMARY KEY si existe esa FK, por eso la eliminamos antes si existe
        $fkExists = DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = 'documents'
              AND CONSTRAINT_NAME = 'documents_customer_foreign'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");

        if ($fkExists[0]->cnt > 0) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropForeign('documents_customer_foreign');
            });
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropPrimary();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
            $table->unique(['identification_number', 'companies_id']);
        });

        // Obtener todas las companies para crear o verificar customer por defecto
        $companies = Company::with('user')->get();

        foreach ($companies as $company) {
            // Buscar si ya existe el customer por defecto para esta company
            $existingCustomer = Customer::where('identification_number', '2222222222')
                                      ->where('companies_id', $company->id)
                                      ->first();

            // Si no existe, crearlo
            if (!$existingCustomer) {
                Customer::create([
                    'identification_number' => '2222222222',
                    'companies_id' => $company->id,
                    'name' => 'Cliente por Defecto',
                    'email' => $company->user->email ?? '',
                    'password' => bcrypt('2222222222'),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar todos los customers por defecto
        Customer::where('identification_number', '2222222222')->delete();

        // Revertir cambios de estructura
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['identification_number', 'companies_id']);
            $table->dropColumn('id');
            $table->primary('identification_number');
        });
    }
}
