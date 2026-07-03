<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'; ?>
<ApplicationResponse
    xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
    xmlns:xades="http://uri.etsi.org/01903/v1.3.2#"
    xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
    xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
    xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#"
    xmlns:sts="urn:dian:gov:co:facturaelectronica:Structures-2-1"
    xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="urn:oasis:names:specification:ubl:schema:xsd:ApplicationResponse-2">
    {{-- UBLExtensions --}}
    @include('xml._ubl_extensions_appresponse')
    <cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>1</cbc:CustomizationID>
    <cbc:ProfileID>DIAN 2.1: ApplicationResponse de la Factura Electrónica de Venta</cbc:ProfileID>
    <cbc:ProfileExecutionID>{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_environment->code)}}</cbc:ProfileExecutionID>
    @if(isset($request->resend_consecutive) && ($request->resend_consecutive == true))
        <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}{{preg_replace("/[\r\n|\n|\r]+/", "", $documentReference->number)}}{{preg_replace("/[\r\n|\n|\r]+/", "", $event->code)}}-{{rand(0, 9)}}</cbc:ID>
    @else
        <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}{{preg_replace("/[\r\n|\n|\r]+/", "", $documentReference->number)}}{{preg_replace("/[\r\n|\n|\r]+/", "", $event->code)}}</cbc:ID>
    @endif
    <cbc:UUID schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_environment->code)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $typeDocument->cufe_algorithm)}}"/>
    <cbc:IssueDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $date ?? Carbon\Carbon::now()->format('Y-m-d'))}}</cbc:IssueDate>
    <cbc:IssueTime>{{preg_replace("/[\r\n|\n|\r]+/", "", $time ?? Carbon\Carbon::now()->format('H:i:s'))}}-05:00</cbc:IssueTime>
    @if(isset($notes))
        <cbc:Note>{{preg_replace("/[\r\n|\n|\r]+/", "", $notes)}}</cbc:Note>
    @endif
    <cac:SenderParty>
        <cac:PartyTaxScheme>
            <cbc:RegistrationName>{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->name)}}</cbc:RegistrationName>
        {{--    <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->dv)}}" schemeVersionID="{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->type_organization->code)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->identification_number)}}</cbc:CompanyID>    --}}
            <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->dv)}}" schemeVersionID="{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->type_organization->code)}}" schemeName="31">{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->identification_number)}}</cbc:CompanyID>
            <cbc:TaxLevelCode listName="{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->type_regime->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->type_liability->code)}}</cbc:TaxLevelCode>
            <cac:TaxScheme>
                <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->tax->code)}}</cbc:ID>
                <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $sender->company->tax->name)}}</cbc:Name>
            </cac:TaxScheme>
        </cac:PartyTaxScheme>
    </cac:SenderParty>
    @include('xml._customer_attached', ['user' => $user])
    <cac:DocumentResponse>
        <cac:Response>
          @if(isset($typerejection))
              <cbc:ResponseCode listID="{{preg_replace("/[\r\n|\n|\r]+/", "", $typerejection->code)}}" name="{{preg_replace("/[\r\n|\n|\r]+/", "", $typerejection->name)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $event->code)}}</cbc:ResponseCode>
          @else
              <cbc:ResponseCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $event->code)}}</cbc:ResponseCode>
          @endif
          <cbc:Description>{{preg_replace("/[\r\n|\n|\r]+/", "", $event->name)}}</cbc:Description>
        </cac:Response>
        <cac:DocumentReference>
          <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $documentReference->number)}}</cbc:ID>
          <cbc:UUID schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $documentReference->scheme_name)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $documentReference->uuid)}}</cbc:UUID>
          <cbc:DocumentTypeCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $typeDocumentReference->code)}}</cbc:DocumentTypeCode>
        </cac:DocumentReference>
        @include('xml._issuer_party')
    </cac:DocumentResponse>
  </ApplicationResponse>
