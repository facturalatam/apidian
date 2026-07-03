<?php

namespace App\Custom;

use ubl21dian\Templates\Template;
use ubl21dian\Templates\CreateTemplate;

class GetAdquirerRequest extends Template implements CreateTemplate
{
    /**
     * Action.
     *
     * @var string
     */
    public $Action = 'http://wcf.dian.colombia/IWcfDianCustomerServices/GetAcquirer';

    /**
     * Required properties.
     *
     * @var array
     */
    protected $requiredProperties = [
        'identificationType',
        'identificationNumber',
    ];

    /**
     * Construct.
     *
     * @param string $pathCertificate
     * @param string $passwors
     */
    public function __construct($pathCertificate, $passwors)
    {
        parent::__construct($pathCertificate, $passwors);
    }

    /**
     * Create template.
     *
     * @return string
     */
    public function createTemplate()
    {
        return $this->templateXMLSOAP = <<<XML
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:wcf="http://wcf.dian.colombia">
    <soap:Body>
        <wcf:GetAcquirer>
            <!--Optional:-->
            <wcf:identificationType>{$this->identificationType}</wcf:identificationType>
            <!--Optional:-->
            <wcf:identificationNumber>{$this->identificationNumber}</wcf:identificationNumber>
        </wcf:GetAcquirer>
    </soap:Body>
</soap:Envelope>
XML;
    }
}
