<cac:Delivery>
    <cbc:ActualDeliveryDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $delivery->actual_delivery_date ?? Carbon\Carbon::now()->format('Y-m-d'))}}</cbc:ActualDeliveryDate>
    <cac:DeliveryAddress>
        <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $delivery->company->municipality->code)}}</cbc:ID>
        <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", $delivery->company->municipality->name)}}</cbc:CityName>
        <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", $delivery->company->municipality->department->name)}}</cbc:CountrySubentity>
        <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $delivery->company->municipality->department->code)}}</cbc:CountrySubentityCode>
        <cac:AddressLine>
            <cbc:Line>{{preg_replace("/[\r\n|\n|\r]+/", "", $delivery->company->address)}}</cbc:Line>
        </cac:AddressLine>
        <cac:Country>
            <cbc:IdentificationCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $delivery->company->country->code)}}</cbc:IdentificationCode>
            <cbc:Name languageID="{{preg_replace("/[\r\n|\n|\r]+/", "", $delivery->company->language->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $delivery->company->country->name)}}</cbc:Name>
        </cac:Country>
    </cac:DeliveryAddress>
    <cac:DeliveryParty>
        @if ($deliveryparty->company->type_organization->code == 2)
            <cac:PartyIdentification>
               <cbc:ID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->identification_number)}}</cbc:ID>
            </cac:PartyIdentification>
        @endif
        <cac:PartyName>
            <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->name)}}</cbc:Name>
        </cac:PartyName>
            <cac:PhysicalLocation>
                <cac:Address>
                    <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->municipality->code)}}</cbc:ID>
                    <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->municipality->name)}}</cbc:CityName>
                    <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->municipality->department->name)}}</cbc:CountrySubentity>
                    <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->municipality->department->code)}}</cbc:CountrySubentityCode>
                    <cac:AddressLine>
                        <cbc:Line>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->address)}}</cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->country->code)}}</cbc:IdentificationCode>
                        <cbc:Name languageID="{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->language->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->country->name)}}</cbc:Name>
                    </cac:Country>
                </cac:Address>
            </cac:PhysicalLocation>
        <cac:PartyTaxScheme>
            <cbc:RegistrationName>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->name)}}</cbc:RegistrationName>
            <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->identification_number)}}</cbc:CompanyID>
            <cbc:TaxLevelCode listName="{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->type_regime->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->type_liability->code)}}</cbc:TaxLevelCode>
            <cac:RegistrationAddress>
                <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->municipality->code)}}</cbc:ID>
                <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->municipality->name)}}</cbc:CityName>
                <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->municipality->department->name)}}</cbc:CountrySubentity>
                <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->municipality->department->code)}}</cbc:CountrySubentityCode>
                <cac:AddressLine>
                    <cbc:Line>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->address)}}</cbc:Line>
                </cac:AddressLine>
                <cac:Country>
                    <cbc:IdentificationCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->country->code)}}</cbc:IdentificationCode>
                    <cbc:Name languageID="{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->language->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->country->name)}}</cbc:Name>
                </cac:Country>
            </cac:RegistrationAddress>
            <cac:TaxScheme>
                <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->tax->code)}}</cbc:ID>
                <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->tax->name)}}</cbc:Name>
            </cac:TaxScheme>
        </cac:PartyTaxScheme>
        <cac:PartyLegalEntity>
            <cbc:RegistrationName>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->name)}}</cbc:RegistrationName>
            <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->identification_number)}}</cbc:CompanyID>
            <cac:CorporateRegistrationScheme>
                <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->merchant_registration)}}</cbc:Name>
            </cac:CorporateRegistrationScheme>
        </cac:PartyLegalEntity>
        <cac:Contact>
            <cbc:Telephone>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->company->phone)}}</cbc:Telephone>
            <cbc:ElectronicMail>{{preg_replace("/[\r\n|\n|\r]+/", "", $deliveryparty->email)}}</cbc:ElectronicMail>
        </cac:Contact>
    </cac:DeliveryParty>
</cac:Delivery>
