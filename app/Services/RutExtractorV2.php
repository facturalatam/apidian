<?php

namespace App\Services;

use App\Department;
use App\Municipality;
use App\TypeDocumentIdentification;
use App\TypeLiability;
use App\TypeOrganization;
use App\TypeRegime;
use Illuminate\Support\Facades\Log;

/**
 * Extractor de datos del RUT (formulario DIAN 001) - Version 2.
 *
 * A diferencia del metodo anterior (regexes sueltos sobre el texto plano),
 * este parser se ancla a las casillas numeradas del formulario oficial
 * (5, 6, 24, 25, 26, 31-35, 38-45, 53, 77), que son fijas en todos los RUT,
 * y corta las lineas de valores por las columnas donde inician las etiquetas
 * (pdftotext -layout conserva la alineacion visual del formulario).
 *
 * Mejoras frente a la V1:
 *  - Persona natural: arma el nombre desde las casillas 31-34 cuando la
 *    casilla 35 (Razon social) viene vacia (la V1 capturaba basura).
 *  - Departamentos/municipios con nombre compuesto ("Valle del Cauca",
 *    "Bogota D.C.", "Bogota, D.C.") se detectan correctamente.
 *  - Extrae los campos criticos que la V1 nunca llenaba:
 *    type_document_identification_id (casilla 25), type_regime_id y
 *    type_liability_id (responsabilidades de la casilla 53).
 *  - El DV se verifica con el algoritmo modulo 11 de la DIAN.
 */
class RutExtractorV2
{
    /**
     * Extrae los campos del RUT desde un PDF.
     *
     * @return array{fields: array, raw_text: string}
     */
    public static function extract(string $pdfPath): array
    {
        $text = self::pdfToText($pdfPath);

        if (!$text || trim($text) === '') {
            throw new \RuntimeException('No se pudo extraer el texto del PDF. Verifique que el RUT no sea un documento escaneado.');
        }

        return [
            'fields'   => self::parseText($text),
            'raw_text' => $text,
        ];
    }

    public static function pdfToText(string $pdfPath): string
    {
        $pdftotext = config('system_configuration.pdftotext_path') ?: env('PDFTOTEXT_PATH');

        if (!$pdftotext || !file_exists($pdftotext)) {
            throw new \RuntimeException('Poppler (pdftotext) no está configurado. Añade la ruta en .env como PDFTOTEXT_PATH=');
        }

        // opciones ANTES del archivo: poppler acepta ambos ordenes pero xpdf
        // (binario usado en hostings sin root) exige [options] <PDF-file>.
        // -enc UTF-8 porque xpdf emite Latin-1 por defecto
        $cmd = "\"$pdftotext\" -layout -enc UTF-8 \"$pdfPath\" -";

        return (string) shell_exec($cmd);
    }

    /**
     * Parsea el texto plano (-layout) de un RUT y devuelve los campos del formulario.
     */
    public static function parseText(string $text): array
    {
        $lines  = preg_split('/\r\n|\r|\n/u', $text);
        $fields = [];

        self::parseNitDv($lines, $fields);
        self::parseContribuyente($lines, $fields);
        self::parseUbicacion($lines, $fields);
        self::parseResponsabilidades($text, $fields);
        self::parseMatricula($text, $fields);

        return $fields;
    }

    /* ==================== Casillas 5 y 6: NIT y DV ==================== */

    private static function parseNitDv(array $lines, array &$fields): void
    {
        $idx = self::findLine($lines, '/5\.\s*N[úu]mero de Identificaci[óo]n Tributaria/iu');
        if ($idx === null) {
            Log::info('RutExtractorV2: no se encontró la casilla 5 (NIT)');
            return;
        }

        $labelLine = $lines[$idx];
        $colDv  = self::charPos($labelLine, '/6\.\s*DV/u');
        $colSec = self::charPos($labelLine, '/12\.\s*Direcci[óo]n seccional/iu');

        $valueLine = self::nextValueLine($lines, $idx, 4, '/IDENTIFICACI[ÓO]N/u', '/\d/');
        if ($valueLine === null) {
            return;
        }

        $nit = null;
        $dv  = null;

        if ($colDv !== null) {
            $nit = preg_replace('/\D/', '', self::slice($valueLine, 0, $colDv - 1));
            $dvPart = self::slice($valueLine, $colDv - 2, ($colSec ?? $colDv + 12) + 2);
            if (preg_match('/\d/', $dvPart, $m)) {
                $dv = $m[0];
            }
        }

        // Verificacion / plan B: los digitos de la linea (NIT+DV concatenados)
        // se validan contra el modulo 11 de la DIAN
        $lineDigits = preg_replace('/\D/', '', self::slice($valueLine, 0, $colSec ?? null));
        if ((!$nit || $dv === null || self::computeDv($nit) !== intval($dv)) && strlen($lineDigits) >= 8) {
            $candNit = substr($lineDigits, 0, -1);
            $candDv  = substr($lineDigits, -1);
            if (self::computeDv($candNit) === intval($candDv)) {
                $nit = $candNit;
                $dv  = $candDv;
            }
        }

        if ($nit) {
            $fields['nit'] = $nit;
            if ($dv !== null) {
                $fields['dv'] = $dv;
            }
            if (self::computeDv($nit) !== intval($dv)) {
                Log::warning("RutExtractorV2: el DV extraído ($dv) no coincide con el calculado para el NIT $nit");
            }
        }
    }

    /**
     * Digito de verificacion DIAN (modulo 11).
     */
    public static function computeDv(string $nit): ?int
    {
        $nit = preg_replace('/\D/', '', $nit);
        if ($nit === '') {
            return null;
        }

        $weights = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        $sum = 0;
        $len = strlen($nit);
        for ($i = 0; $i < $len && $i < count($weights); $i++) {
            $sum += intval($nit[$len - 1 - $i]) * $weights[$i];
        }
        $r = $sum % 11;

        return $r > 1 ? 11 - $r : $r;
    }

    /* ========== Casillas 24-26 y 31-35: contribuyente, documento y nombre ========== */

    private static function parseContribuyente(array $lines, array &$fields): void
    {
        $idx = self::findLine($lines, '/24\.\s*Tipo de contribuyente/iu');

        $orgCode = null;
        $docCode = null;

        if ($idx !== null) {
            $labelLine = $lines[$idx];
            $col25 = self::charPos($labelLine, '/25\.\s*Tipo de documento/iu');
            $col26 = self::charPos($labelLine, '/26\.\s*N[úu]mero de Identificaci[óo]n/iu');

            $valueLine = self::nextValueLine($lines, $idx, 3, '/Lugar de expedici[óo]n|28\.\s*Pa[íi]s/iu');

            if ($valueLine !== null) {
                // 24. Tipo de contribuyente: "Persona jurídica  1" / "Persona natural o sucesión ilíquida  2"
                $orgPart = self::slice($valueLine, 0, $col25);
                if (preg_match('/(\S.*?)\s+(\d)\s*$/u', rtrim($orgPart), $m)) {
                    $fields['organization_text'] = self::collapse($m[1]);
                    $orgCode = intval($m[2]);
                    $fields['organization_code'] = $orgCode;

                    $typeOrg = self::lookupByCode(TypeOrganization::class, (string) $orgCode);
                    if ($typeOrg) {
                        $fields['type_organization_id'] = $typeOrg->id;
                    }
                }

                // 25. Tipo de documento: "Cédula de Ciudadanía   1 3" -> codigo DIAN 13
                if ($col25 !== null) {
                    $docPart = self::slice($valueLine, $col25 - 2, $col26 !== null ? $col26 - 2 : null);
                    if (preg_match('/(\S.*?)\s+((?:\d\s*){1,2})\s*$/u', rtrim($docPart), $m)) {
                        $docCode = preg_replace('/\D/', '', $m[2]);
                    }
                }
            }
        }

        // Persona juridica factura con NIT (codigo DIAN 31); persona natural por
        // defecto cedula de ciudadania (13) si la casilla 25 no fue legible
        if ($orgCode === 1) {
            $docCode = '31';
        } elseif ($orgCode === 2 && !$docCode) {
            $docCode = '13';
        }

        if ($docCode) {
            $typeDoc = self::lookupByCode(TypeDocumentIdentification::class, $docCode);
            if ($typeDoc) {
                $fields['type_document_identification_id'] = $typeDoc->id;
            }
        }

        // 35. Razon social (persona juridica)
        $businessName = null;
        $idx35 = self::findLine($lines, '/35\.\s*Raz[óo]n social/iu');
        if ($idx35 !== null) {
            $value = self::nextValueLine($lines, $idx35, 4, '/36\.\s*Nombre comercial/iu');
            if ($value !== null) {
                $businessName = self::collapse($value);
            }
        }

        // 31-34: nombres y apellidos (persona natural)
        if (!$businessName) {
            $idx31 = self::findLine($lines, '/31\.\s*Primer apellido/iu');
            if ($idx31 !== null) {
                $labelLine = $lines[$idx31];
                $col32 = self::charPos($labelLine, '/32\.\s*Segundo apellido/iu');
                $col33 = self::charPos($labelLine, '/33\.\s*Primer nombre/iu');
                $col34 = self::charPos($labelLine, '/34\.\s*Otros nombres/iu');

                $value = self::nextValueLine($lines, $idx31, 4, '/35\.\s*Raz[óo]n social/iu');
                if ($value !== null) {
                    $apellido1 = self::collapse(self::slice($value, 0, $col32));
                    $apellido2 = self::collapse(self::slice($value, $col32 - 2, $col33 !== null ? $col33 - 2 : null));
                    $nombre1   = self::collapse(self::slice($value, $col33 - 2, $col34 !== null ? $col34 - 2 : null));
                    $otros     = self::collapse(self::slice($value, $col34 - 2, null));

                    $businessName = self::collapse("$nombre1 $otros $apellido1 $apellido2");
                }
            }
        }

        if ($businessName) {
            $fields['business_name'] = $businessName;
        } else {
            Log::info('RutExtractorV2: no se detectó razón social ni nombres (casillas 31-35)');
        }
    }

    /* ========== Casillas 38-45: ubicacion, direccion, correo y telefonos ========== */

    private static function parseUbicacion(array $lines, array &$fields): void
    {
        $secIdx = self::findLine($lines, '/^\s*UBICACI[ÓO]N\s*$/u') ?? 0;

        // 39/40: departamento y municipio
        $idx = self::findLine($lines, '/39\.\s*Departamento/iu', $secIdx);
        if ($idx !== null && preg_match('/40\.\s*Ciudad/iu', $lines[$idx])) {
            $value = self::nextValueLine($lines, $idx, 3, '/41\.\s*Direcci[óo]n/iu');
            if ($value !== null) {
                // "COLOMBIA  1 6 9 Valle del Cauca   7 6 Cali   0 0 1"
                // pais, codigo pais (169), depto, codigo depto (2 dig), municipio, codigo mpio (3 dig)
                if (preg_match(
                    '/^\s*(\S.*?)\s+((?:\d\s?){3})\s*(\S.*?)\s{2,}(\d\s?\d)\s+(\S.*?)\s{2,}(\d\s?\d\s?\d)\s*$/u',
                    rtrim($value),
                    $m
                )) {
                    $depName = self::collapse($m[3]);
                    $depCode = str_pad(preg_replace('/\D/', '', $m[4]), 2, '0', STR_PAD_LEFT);
                    $munName = self::collapse($m[5]);
                    $munCode = $depCode . str_pad(preg_replace('/\D/', '', $m[6]), 3, '0', STR_PAD_LEFT);

                    $fields['department_code']   = $depCode;
                    $fields['department_text']   = $depName;
                    $fields['municipality_code'] = $munCode;
                    $fields['municipality_text'] = $munName;

                    $dep = Department::whereRaw("LPAD(CAST(code AS CHAR), 2, '0') = ?", [$depCode])->first();
                    if (!$dep) {
                        // la collation *_ci de MySQL ignora mayusculas y acentos
                        $dep = Department::whereRaw('UPPER(name) LIKE UPPER(?)', [$depName])->first();
                    }

                    if ($dep) {
                        $fields['department_id'] = $dep->id;

                        $mun = Municipality::where('department_id', $dep->id)
                            ->where(function ($q) use ($munCode) {
                                $q->whereRaw("LPAD(CAST(code AS CHAR), 5, '0') = ?", [$munCode])
                                    ->orWhere('code', $munCode)
                                    ->orWhere('code', ltrim($munCode, '0'))
                                    ->orWhere('code', substr($munCode, 2));
                            })
                            ->first();
                        if (!$mun) {
                            $mun = Municipality::where('department_id', $dep->id)
                                ->whereRaw('UPPER(name) LIKE UPPER(?)', [$munName])
                                ->first();
                        }

                        if ($mun) {
                            $fields['municipality_id'] = $mun->id;
                        } else {
                            Log::info("RutExtractorV2: municipio no encontrado en BD: $munCode ($munName)");
                        }
                    } else {
                        Log::info("RutExtractorV2: departamento no encontrado en BD: $depCode ($depName)");
                    }
                } else {
                    Log::info("RutExtractorV2: no se pudo interpretar la fila de ubicación: $value");
                }
            }
        }

        // 41. Direccion principal
        $idx41 = self::findLine($lines, '/41\.\s*Direcci[óo]n principal/iu', $secIdx);
        if ($idx41 !== null) {
            $value = self::nextValueLine($lines, $idx41, 3, '/42\.\s*Correo/iu');
            if ($value !== null) {
                $fields['address'] = self::collapse($value);
            }
        }

        // 42. Correo electronico (misma linea o siguiente; plan B: primer correo del PDF)
        $emailRegex = '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[A-Za-z]{2,}/u';
        $idx42 = self::findLine($lines, '/42\.\s*Correo electr[óo]nico/iu', $secIdx);
        if ($idx42 !== null) {
            $zone = $lines[$idx42] . "\n" . ($lines[$idx42 + 1] ?? '');
            if (preg_match($emailRegex, $zone, $m)) {
                $fields['email'] = trim($m[0]);
            }
        }
        if (empty($fields['email']) && preg_match($emailRegex, implode("\n", $lines), $m)) {
            $fields['email'] = trim($m[0]);
        }

        // 44/45. Telefonos: los digitos vienen espaciados en la misma linea y el
        // numero de la etiqueta "45." quedaria pegado al telefono 1 si se leyera
        // con un regex corrido, por eso la linea se parte por la etiqueta 45
        $idx44 = self::findLine($lines, '/44\.\s*Tel[ée]fono\s*1/iu', $secIdx);
        if ($idx44 !== null) {
            $parts = preg_split('/45\.\s*Tel[ée]fono\s*2/iu', $lines[$idx44]);

            $tel1 = null;
            if (preg_match('/44\.\s*Tel[ée]fono\s*1(.*)$/iu', $parts[0], $m)) {
                $tel1 = preg_replace('/\D/', '', $m[1]);
            }
            $tel2 = isset($parts[1]) ? preg_replace('/\D/', '', $parts[1]) : null;

            if (strlen((string) $tel1) >= 7) {
                $fields['phone'] = $tel1;
            } elseif (strlen((string) $tel2) >= 7) {
                $fields['phone'] = $tel2;
            }
        }
    }

    /* ========== Casilla 53: responsabilidades -> regimen y responsabilidad ========== */

    private static function parseResponsabilidades(string $text, array &$fields): void
    {
        $section = $text;
        if (preg_match('/Responsabilidades,\s*Calidades\s*y\s*Atributos([\s\S]{0,2500}?)(?:Usuarios aduaneros|IMPORTANTE:)/iu', $text, $m)) {
            $section = $m[1];
        }

        // Lineas detalle "05- Impto. renta..." (pueden venir en dos columnas por fila)
        preg_match_all('/(?:^|\s{2,})(\d{2})\s?-\s?\p{L}/mu', $section, $mm);
        $codes = array_map('intval', $mm[1]);

        if (!$codes) {
            // Sin codigos no se asume regimen ni responsabilidad: el formulario
            // los marcara como "no detectado" para que el usuario los elija
            Log::info('RutExtractorV2: no se detectaron responsabilidades (casilla 53)');
            return;
        }
        Log::info('RutExtractorV2: responsabilidades detectadas: ' . implode(',', $codes));

        // Regimen: 48 = Responsable de IVA, si no aparece se asume 49 (No responsable)
        $regimeCode = in_array(48, $codes) ? '48' : '49';
        $regime = self::lookupByCode(TypeRegime::class, $regimeCode);
        if ($regime) {
            $fields['type_regime_id'] = $regime->id;
        }

        // Responsabilidad fiscal (prioridad segun anexo DIAN)
        $liabilityCode = 'R-99-PN';
        foreach ([13 => 'O-13', 15 => 'O-15', 23 => 'O-23', 47 => 'O-47'] as $rutCode => $dianCode) {
            if (in_array($rutCode, $codes)) {
                $liabilityCode = $dianCode;
                break;
            }
        }
        $liability = self::lookupByCode(TypeLiability::class, $liabilityCode);
        if ($liability) {
            $fields['type_liability_id'] = $liability->id;
        }
    }

    /* ==================== Casilla 77: matricula mercantil ==================== */

    private static function parseMatricula(string $text, array &$fields): void
    {
        // Solo digitos en la MISMA linea de la etiqueta: si la casilla 77 viene
        // vacia no debe capturarse el valor de la fila siguiente (78. Departamento)
        if (preg_match('/77\.\s*No\.?\s*Matr[íi]cula\s+mercantil[^\S\r\n]+((?:\d[^\S\r\n]?){4,30})/iu', $text, $m)) {
            $fields['merchant_registration'] = preg_replace('/\D/', '', $m[1]);
        } elseif (preg_match('/Matr[íi]cula\s+mercantil[^\S\r\n:]*:?[^\S\r\n]*((?:\d[^\S\r\n]?){6,30})/iu', $text, $m)) {
            $fields['merchant_registration'] = preg_replace('/\D/', '', $m[1]);
        }
    }

    /* ==================== Utilidades ==================== */

    private static function findLine(array $lines, string $regex, int $from = 0): ?int
    {
        $n = count($lines);
        for ($i = $from; $i < $n; $i++) {
            if (preg_match($regex, $lines[$i])) {
                return $i;
            }
        }
        return null;
    }

    /**
     * Primera linea no vacia despues de la etiqueta; se detiene si aparece
     * otra etiqueta ($stopRegex), p. ej. cuando la casilla esta vacia.
     */
    private static function nextValueLine(array $lines, int $labelIdx, int $maxLookahead, ?string $stopRegex = null, ?string $mustMatch = null): ?string
    {
        for ($i = $labelIdx + 1; $i <= $labelIdx + $maxLookahead && $i < count($lines); $i++) {
            $line = $lines[$i];
            if ($stopRegex && preg_match($stopRegex, $line)) {
                return null;
            }
            if (trim($line) === '') {
                continue;
            }
            if ($mustMatch && !preg_match($mustMatch, $line)) {
                continue;
            }
            return $line;
        }
        return null;
    }

    /**
     * Posicion (en caracteres, no bytes) donde inicia una etiqueta en la linea.
     */
    private static function charPos(string $line, string $regex): ?int
    {
        if (!preg_match($regex, $line, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        return mb_strlen(substr($line, 0, $m[0][1]), 'UTF-8');
    }

    /**
     * Subcadena por columnas de caracteres [start, end).
     */
    private static function slice(string $line, ?int $start, ?int $end): string
    {
        if ($start === null) {
            return '';
        }
        $start = max(0, $start);
        if ($end !== null && $end <= $start) {
            return '';
        }
        return mb_substr($line, $start, $end !== null ? $end - $start : null, 'UTF-8');
    }

    private static function collapse(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    /**
     * Busqueda por codigo tolerante a \r y espacios finales que traen los
     * catalogos sembrados desde CSV.
     */
    private static function lookupByCode(string $model, string $code)
    {
        return $model::whereRaw(
            "TRIM(REPLACE(REPLACE(CAST(code AS CHAR), CHAR(13), ''), CHAR(10), '')) = ?",
            [$code]
        )->first();
    }
}
