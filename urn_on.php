<?php

/**
 * Aplica las plantillas XML y clases de firma "urn" (parche regla ZE02 de la DIAN).
 *
 * Se ejecuta automáticamente en cada `composer install` / `composer update`
 * (hook post-autoload-dump en composer.json), por lo que ya no es necesario
 * correr urn_on.bat / urn_on.sh manualmente después de instalar o actualizar.
 *
 * Uso manual (cualquier sistema operativo): php urn_on.php
 */

$base = __DIR__;

$copies = [
    $base . '/resources/templates/xml/urn' => $base . '/resources/templates/xml',
    $base . '/vendor/ubl21dian/torresoftware/src/XAdES/urn' => $base . '/vendor/ubl21dian/torresoftware/src/XAdES',
];

$exit = 0;

foreach ($copies as $from => $to) {
    if (!is_dir($from) || !is_dir($to)) {
        echo "[urn_on] Omitido, carpeta no encontrada: {$from}" . PHP_EOL;
        continue;
    }

    $copied = 0;
    foreach (glob($from . '/*') as $file) {
        if (!is_file($file)) {
            continue;
        }
        $dest = $to . '/' . basename($file);
        if (@copy($file, $dest)) {
            $copied++;
        } else {
            echo "[urn_on] ERROR copiando " . basename($file) . " hacia {$to}" . PHP_EOL;
            $exit = 1;
        }
    }

    echo "[urn_on] {$copied} archivo(s) aplicados en {$to}" . PHP_EOL;
}

exit($exit);
