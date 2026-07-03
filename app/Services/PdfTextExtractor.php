<?php

namespace App\Services;

class PdfTextExtractor
{
    public static function extract($pdfPath)
    {
        $pdftotext = env('PDFTOTEXT_PATH');

        if (!$pdftotext || !file_exists($pdftotext)) {
            throw new \Exception("No se encontró pdftotext. Configure PDFTOTEXT_PATH en .env");
        }

        // Opciones ANTES del archivo: el binario Xpdf (usado en algunos hostings) las
        // ignora si van después. -enc UTF-8 evita salida Latin-1 que rompe los regex.
        // Compatible también con poppler-utils.
        $cmd = "\"$pdftotext\" -layout -enc UTF-8 \"$pdfPath\" -";
        return shell_exec($cmd);
    }
}
