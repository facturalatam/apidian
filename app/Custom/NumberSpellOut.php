<?php

namespace App\Custom;

use App\TypeCurrency;

class NumberSpellOut
{
    public function convertir($numero, $idcurrency = NULL, $lang = 'es'){
        $locale = $lang === 'en' ? 'en-US' : 'es-ES';
        $formatter = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
        $izquierda = intval(floor($numero));
        $derecha = round(($numero - floor($numero)) * 100, 2);
        if($idcurrency){
            $idcurrency = TypeCurrency::findOrFail($idcurrency);
            if($lang === 'en') {
                return strtoupper($formatter->format($izquierda)) . " " . strtoupper($idcurrency->name) . " AND " . strtoupper($formatter->format($derecha)) . " CENTS";
            } else {
                return strtoupper($formatter->format($izquierda)) . " " . strtoupper($idcurrency->name) . " CON " . strtoupper($formatter->format($derecha)) . " CENTAVOS";
            }
        }
        else {
            if($lang === 'en') {
                return strtoupper($formatter->format($izquierda)) . " PESOS AND " . strtoupper($formatter->format($derecha)) . " CENTS";
            } else {
                return strtoupper($formatter->format($izquierda)) . " PESOS CON " . strtoupper($formatter->format($derecha)) . " CENTAVOS";
            }
        }
    }
}
