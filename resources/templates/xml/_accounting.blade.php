<cac:{{$node}}>
    <cbc:AdditionalAccountID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_organization->code)}}</cbc:AdditionalAccountID>
    <cac:Party>
        @if(isset($request['actividadeconomica']))
            <cbc:IndustryClassificationCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $request['actividadeconomica'])}}</cbc:IndustryClassificationCode>
        @endif
        @if ($user->company->type_organization->code == 2)
            <cac:PartyIdentification>
                <cbc:ID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:ID>
            </cac:PartyIdentification>
        @endif
        <cac:PartyName>
            @if(!isset($supplier) && !empty($request['establishment_name']))
                <cbc:Name>{{ preg_replace("/[\r\n|\n|\r]+/", "", $user->name) }}</cbc:Name>
            @else
                <cbc:Name>{{ preg_replace("/[\r\n|\n|\r]+/", "", $request['establishment_name'] ?? $user->name) }}</cbc:Name>
            @endif
        </cac:PartyName>
        @php
            $hasAddressData =
                !empty($user->company->address) &&
                !empty($user->company->municipality) &&
                !empty($user->company->municipality->code) &&
                !empty($user->company->municipality->name) &&
                !empty($user->company->municipality->department) &&
                !empty($user->company->municipality->department->name) &&
                !empty($user->company->municipality->department->code) &&
                !empty($user->company->country) &&
                !empty($user->company->country->code) &&
                !empty($user->company->country->name);
        @endphp
        @if($hasAddressData || isset($supplier))
        @inject('municipality', 'App\Municipality')
            <cac:PhysicalLocation>
                @if(!isset($supplier) && $typeDocument->id == '24')
                    <cbc:LocationTypeCode listID="01">{{preg_replace("/[\r\n|\n|\r]+/", "", $request['stratum_id'])}}</cbc:LocationTypeCode>
                @endif
                <cac:Address>
                    @if(isset($supplier))
                        <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $municipality->find($request['establishment_municipality'])->code ?? $user->company->municipality->code)}}</cbc:ID>
                        <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", $municipality->find($request['establishment_municipality'])->name ?? $user->company->municipality->name)}}</cbc:CityName>
                        @if(isset($user->postal_zone_code))
                            <cbc:PostalZone>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->postal_zone_code)}}</cbc:PostalZone>
                        @endif
                        @if($typeDocument->id == 15)
                            <cbc:PostalZone>{{preg_replace("/[\r\n|\n|\r]+/", "", $request['postal_zone_code'])}}</cbc:PostalZone>
                        @endif
                        <cbc:CountrySubentity>{{ preg_replace("/[\r\n|\n|\r]+/", "", trim(isset($request['establishment_municipality']) && $municipality->find($request['establishment_municipality']) 
                                                ? $municipality->find($request['establishment_municipality'])->department->name 
                                                : $user->company->municipality->department->name))}}</cbc:CountrySubentity>
                        <cbc:CountrySubentityCode>{{ preg_replace("/[\r\n|\n|\r]+/", "", isset($request['establishment_municipality']) && $municipality->find($request['establishment_municipality']) 
                                                ? $municipality->find($request['establishment_municipality'])->department->code 
                                                : $user->company->municipality->department->code)}}</cbc:CountrySubentityCode>
                    @else
                        @if($user->company->country->id == 46)
                            <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->code)}}</cbc:ID>
                            <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->name))}}</cbc:CityName>
                            @if(isset($user->postal_zone_code))
                                <cbc:PostalZone>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->postal_zone_code)}}</cbc:PostalZone>
                            @endif
                            <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->department->name))}}</cbc:CountrySubentity>
                            <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->department->code)}}</cbc:CountrySubentityCode>
                        @else
                            <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", "00001")}}</cbc:ID>
                            <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality_name))}}</cbc:CityName>
                            @if(isset($user->postal_zone_code))
                                <cbc:PostalZone>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->postal_zone_code)}}</cbc:PostalZone>
                            @endif
                            <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->state_name))}}</cbc:CountrySubentity>
                            <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", "01")}}</cbc:CountrySubentityCode>
                        @endif
                    @endif
                    <cac:AddressLine>
                        <cbc:Line>
                            @if($node === 'AccountingCustomerParty' && isset($customer))
                                {{ preg_replace("/[\r\n|\n|\r]+/", "", $customer->company->address) }}
                            @else
                                {{ preg_replace("/[\r\n|\n|\r]+/", "", $request['establishment_address'] ?? $user->company->address) }}
                            @endif
                        </cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->country->code)}}</cbc:IdentificationCode>
                        <cbc:Name languageID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->language->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->country->name)}}</cbc:Name>
                    </cac:Country>
                </cac:Address>
            </cac:PhysicalLocation>
        @endif
        <cac:PartyTaxScheme>
            <cbc:RegistrationName>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->name)}}</cbc:RegistrationName>
            @if($typeDocument->id == '11' || $typeDocument->id == '13' || $typeDocument->id == '26'|| $typeDocument->id == '16')
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="31">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:CompanyID>
            @else
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:CompanyID>
            @endif
            <cbc:TaxLevelCode listName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_regime->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_liability_codes ?? $user->company->type_liability->code)}}</cbc:TaxLevelCode>
            @if($hasAddressData || isset($supplier))
                <cac:RegistrationAddress>
                    @if(isset($supplier))
                        <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->code)}}</cbc:ID>
                        <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->name))}}</cbc:CityName>
                        <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->department->name))}}</cbc:CountrySubentity>
                        <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->department->code)}}</cbc:CountrySubentityCode>
                    @else
                        @if($user->company->country->id == 46)
                            <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->code)}}</cbc:ID>
                            <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->name))}}</cbc:CityName>
                            <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality->department->name))}}</cbc:CountrySubentity>
                            <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->municipality->department->code)}}</cbc:CountrySubentityCode>
                        @else
                            <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", "00001")}}</cbc:ID>
                            <cbc:CityName>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->municipality_name))}}</cbc:CityName>
                            <cbc:CountrySubentity>{{preg_replace("/[\r\n|\n|\r]+/", "", trim($user->company->state_name))}}</cbc:CountrySubentity>
                            <cbc:CountrySubentityCode>{{preg_replace("/[\r\n|\n|\r]+/", "", "01")}}</cbc:CountrySubentityCode>
                        @endif
                    @endif
                    <cac:AddressLine>
                        <cbc:Line>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->address)}}</cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->country->code)}}</cbc:IdentificationCode>
                        <cbc:Name languageID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->language->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->country->name)}}</cbc:Name>
                    </cac:Country>
                </cac:RegistrationAddress>
            @endif
            <cac:TaxScheme>
                <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->tax->code)}}</cbc:ID>
                <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->tax->name)}}</cbc:Name>
            </cac:TaxScheme>
        </cac:PartyTaxScheme>
        <cac:PartyLegalEntity>
            <cbc:RegistrationName>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->name)}}</cbc:RegistrationName>
            @if(in_array($typeDocument->id, ['15', '26', '19', '24', '16']))
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="31">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:CompanyID>
            @else
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:CompanyID>
            @endif
            <cac:CorporateRegistrationScheme>
                @if(isset($supplier) || ($typeDocument->id == '11' || $typeDocument->id == '13'))
                    <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $resolution->prefix)}}</cbc:ID>
                @endif
                <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->merchant_registration)}}</cbc:Name>
            </cac:CorporateRegistrationScheme>
        </cac:PartyLegalEntity>
        @if($user->company->identification_number != "222222222222")
            @if(!isset($supplier))
                @php
                    $hasPhone = isset($request['customer']['phone']) && !empty($request['customer']['phone']);
                    $hasEmail = isset($request['customer']['email']) && !empty($request['customer']['email']);
                @endphp
                @if($hasPhone || $hasEmail)
                    <cac:Contact>
                        @if($hasPhone)
                            <cbc:Telephone>{{ preg_replace("/[\r\n|\n|\r]+/", "", $request['customer']['phone']) }}</cbc:Telephone>
                        @endif
                        @if($hasEmail)
                            <cbc:ElectronicMail>{{ preg_replace("/[\r\n|\n|\r]+/", "", $request['customer']['email']) }}</cbc:ElectronicMail>
                        @endif
                    </cac:Contact>
                @endif
            @else
                <cac:Contact>
                    <cbc:Telephone>{{preg_replace("/[\r\n|\n|\r]+/", "", $request['establishment_phone'] ?? $user->company->phone)}}</cbc:Telephone>
                    <cbc:ElectronicMail>{{preg_replace("/[\r\n|\n|\r]+/", "", $request['establishment_email'] ?? $user->email)}}</cbc:ElectronicMail>
                </cac:Contact>
            @endif
        @endif
    </cac:Party>
</cac:{{$node}}>