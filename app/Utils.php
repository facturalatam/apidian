<?php

namespace App;

use App\Traits\DocumentTrait;
use App\Services\StorageService;

class Utils
{
    use DocumentTrait;

    public function attacheddocumentname($identification, $file)
    {
        try{
            $namepart = substr($file, 11, strpos($file, '.') - 11);
            $relativeFE = "public/{$identification}/RptaFE-{$namepart}.xml";
            $relativeNC = "public/{$identification}/RptaNC-{$namepart}.xml";
            $relativeND = "public/{$identification}/RptaND-{$namepart}.xml";
            $relativeNI = "public/{$identification}/RptaNI-{$namepart}.xml";
            $relativeNA = "public/{$identification}/RptaNA-{$namepart}.xml";

            if(StorageService::existsAuto($relativeFE))
               $rptaxml = StorageService::getAuto($relativeFE);
            else if(StorageService::existsAuto($relativeNC))
                $rptaxml = StorageService::getAuto($relativeNC);
            else if(StorageService::existsAuto($relativeND))
                $rptaxml = StorageService::getAuto($relativeND);
            else if(StorageService::existsAuto($relativeNI))
                $rptaxml = StorageService::getAuto($relativeNI);
            else
                $rptaxml = StorageService::getAuto($relativeNA);

            $filename = str_replace('ads', 'ad', str_replace('dse', 'ad', str_replace('na', 'ad', str_replace('ni', 'ad', str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $this->getTag($rptaxml, "XmlFileName")->nodeValue)))))));
            return $filename;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
