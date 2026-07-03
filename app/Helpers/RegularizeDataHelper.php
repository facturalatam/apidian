<?php

namespace App\Helpers;

use DB;

class RegularizeDataHelper
{
    
    /**
     * Regularizar data de las tablas con errores
     * Verifica si tiene registros (seeders con errores)
     * Elimina la data
     * Regulariza con la data actualizada del .csv
     *
     * @param  string $table_name
     * @param  array $table_columns
     * @return void
     */
    public static function regularizeDataFromTable($table_name, $table_columns)
    {

        $exist_records = self::countRecords($table_name);

        // si hay registros se eliminan para ejecutar los csv corregidos
        if($exist_records > 0)
        {
            self::deleteRecords($table_name);
            self::insertDataFromSeeder($table_name, $table_columns);
        }

    }


    public static function insertDataFromSeeder($table_name, $table_columns)
    {
        $prefix = 'csv';
        $key = $table_name;

        $path_file = str_replace('\\', '/', public_path($prefix.DIRECTORY_SEPARATOR."{$key}.{$prefix}"));

        DB::connection()
            ->getpdo()
            ->exec("LOAD DATA LOCAL INFILE '".$path_file."' INTO TABLE $key({$table_columns['columns']}) SET created_at = NOW(), updated_at = NOW()");
    }


    public static function deleteRecords($table)
    {
        DB::table($table)->delete();
    }


    public static function countRecords($table)
    {
        return DB::table($table)->count();
    }

}
