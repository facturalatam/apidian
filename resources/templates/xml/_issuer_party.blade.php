<cac:IssuerParty>
    <cac:Person>
        @if(isset($issuerparty))
            <cbc:ID schemeID="" schemeName="13">{{preg_replace("/[\r\n|\n|\r]+/", "", $issuerparty->identification_number)}}</cbc:ID>
            <cbc:FirstName>{{preg_replace("/[\r\n|\n|\r]+/", "", $issuerparty->first_name)}}</cbc:FirstName>
            <cbc:FamilyName>{{preg_replace("/[\r\n|\n|\r]+/", "", $issuerparty->last_name)}}</cbc:FamilyName>
            <cbc:JobTitle>{{preg_replace("/[\r\n|\n|\r]+/", "", $issuerparty->job_title)}}</cbc:JobTitle>
            <cbc:OrganizationDepartment>{{preg_replace("/[\r\n|\n|\r]+/", "", $issuerparty->organization_department)}}</cbc:OrganizationDepartment>
        @else
            <cbc:ID schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->identification_number)}}</cbc:ID>
            <cbc:FirstName>{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->name)}}</cbc:FirstName>
            <cbc:FamilyName>{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->name)}}</cbc:FamilyName>
            <cbc:JobTitle>ADMINISTRADOR</cbc:JobTitle>
            <cbc:OrganizationDepartment>ADMINISTRATIVA</cbc:OrganizationDepartment>
        @endif
    </cac:Person>
</cac:IssuerParty>
