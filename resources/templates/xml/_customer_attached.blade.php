<cac:ReceiverParty>
        <cac:PartyTaxScheme>
            <cbc:RegistrationName>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->name)}}</cbc:RegistrationName>
            <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_document_identification->code)}}" schemeVersionID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_organization->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:CompanyID>
            @if(isset($event->id))
                @if($event->id != "5")
                    <cbc:TaxLevelCode listName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_regime->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_liability->code)}}</cbc:TaxLevelCode>
                @endif
            @else
                <cbc:TaxLevelCode listName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_regime->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_liability->code)}}</cbc:TaxLevelCode>
            @endif
            <cac:TaxScheme>
                <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->tax->code)}}</cbc:ID>
                <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->tax->name)}}</cbc:Name>
            </cac:TaxScheme>
        </cac:PartyTaxScheme>
</cac:ReceiverParty>
