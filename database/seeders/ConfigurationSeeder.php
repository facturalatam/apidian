<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigurationSeeder extends Seeder
{
    /**
     * Prefix.
     *
     * @var string
     */
    public $prefix = 'csv';

    /**
     * Tables.
     *
     * @var array
     */
    public $tables = [
        'type_organizations' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'events' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'countries' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'departments' => [
            'columns' => 'id, country_id, name, code, @created_at, @updated_at',
        ],
        'municipalities' => [
            'columns' => 'id, department_id, name, code, codefacturador, @created_at, @updated_at',
        ],
        'type_document_identifications' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'health_type_document_identifications' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'taxes' => [
            'columns' => 'id, name, description, code, @created_at, @updated_at',
        ],
        'type_regimes' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'type_liabilities' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'payment_forms' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'payment_methods' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'discounts' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'type_currencies' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'unit_measures' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'reference_prices' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'type_documents' => [
            'columns' => 'id, name, code, cufe_algorithm, prefix, @created_at, @updated_at',
        ],
        'type_item_identifications' => [
            'columns' => 'id, name, code, code_agency, @created_at, @updated_at',
        ],
        'type_operations' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'type_environments' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'credit_note_discrepancy_responses' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'debit_note_discrepancy_responses' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'type_discounts' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'languages' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'type_rejections' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'type_generation_transmitions' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'incoterms' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'credit_note_discrepancy_responses_sd' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'type_spds' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
        'prepaid_payment_types' => [
            'columns' => 'id, name, code, @created_at, @updated_at',
        ],
    ];

    /**
     * Run the database seeds.
     */
	public function run() {
        foreach ($this->tables as $key => $table) {
            $rutafile = public_path($this->prefix.DIRECTORY_SEPARATOR."{$key}.{$this->prefix}");
            $rutafile = str_replace('\\', '/', $rutafile);
            // IGNORE = idempotencia a NIVEL DE FILA (clave duplicada):
            //  - Instalacion fresca: convive con los registros que algunas migraciones
            //    insertan en estos catalogos ANTES del seeder (las migraciones corren
            //    primero). Ej: credit_note_discrepancy_responses (id=6) -> el CSV carga
            //    1..5 sin chocar y la tabla queda completa.
            //  - Upgrade (Community -> Enterprise): re-correr el seed omite las filas ya
            //    existentes (no revienta) y carga solo lo que falta (catalogos nuevos
            //    vacios de nomina/RIPS). Los IDs vienen explicitos en el CSV.
            DB::connection()
                ->getpdo()
                ->exec("LOAD DATA LOCAL INFILE '".$rutafile."' IGNORE INTO TABLE $key({$table['columns']}) SET created_at = NOW(), updated_at = NOW()");
        }
    }
}
