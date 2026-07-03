<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'; ?>
<AttachedDocument
    xmlns="urn:oasis:names:specification:ubl:schema:xsd:AttachedDocument-2"
    xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
    xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
    xmlns:ccts="urn:un:unece:uncefact:data:specification:CoreComponentTypeSchemaModule:2"
    xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
    xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
    xmlns:xades="http://uri.etsi.org/01903/v1.3.2#"
    xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#">
    {{-- UBLExtensions --}}
    @include('xml._ubl_extensions_payroll')
    <cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>Documentos adjuntos</cbc:CustomizationID>
    <cbc:ProfileID>Factura Electrónica de Venta</cbc:ProfileID>
    <cbc:ProfileExecutionID>{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_environment->code)}}</cbc:ProfileExecutionID>
    <cbc:ID>{{$cufecude}}</cbc:ID>
    <cbc:IssueDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $date ?? Carbon\Carbon::now()->format('Y-m-d'))}}</cbc:IssueDate>
    <cbc:IssueTime>{{preg_replace("/[\r\n|\n|\r]+/", "", $time ?? Carbon\Carbon::now()->format('H:i:s'))}}-05:00</cbc:IssueTime>
    <cbc:DocumentType>Contenedor de Factura Electrónica</cbc:DocumentType>
    <cbc:ParentDocumentID>{{preg_replace("/[\r\n|\n|\r]+/", "", $document_number)}}</cbc:ParentDocumentID>
    <cac:SenderParty>
        <cac:PartyTaxScheme>
            <cbc:RegistrationName>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->name)}}</cbc:RegistrationName>
            <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->dv)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_document_identification->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->identification_number)}}</cbc:CompanyID>
            <cbc:TaxLevelCode listName="{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_regime->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->type_liability->code)}}</cbc:TaxLevelCode>
            <cac:TaxScheme>
                <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->tax->code)}}</cbc:ID>
                <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $user->company->tax->name)}}</cbc:Name>
            </cac:TaxScheme>
        </cac:PartyTaxScheme>
    </cac:SenderParty>
    @if(isset($customer))
        @include('xml._customer_attached', ['user' => $customer])
    @endif
    <cac:Attachment>
        <cac:ExternalReference>
          <cbc:MimeCode>text/xml</cbc:MimeCode>
          <cbc:EncodingCode>UTF-8</cbc:EncodingCode>
        </cac:ExternalReference>
    </cac:Attachment>
    <cac:ParentDocumentLineReference>
    <cbc:LineID>1</cbc:LineID>
    <cac:DocumentReference>
      @if(isset($resolution))
        <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $resolution->document_number ?? $resolution->next_consecutive)}}</cbc:ID>
      @endif
      <cbc:UUID schemeName="CUFE-SHA384">{{$cufecude}}</cbc:UUID>
      <cbc:IssueDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $date ?? Carbon\Carbon::now()->format('Y-m-d'))}}</cbc:IssueDate>
      <cbc:DocumentType>ApplicationResponse</cbc:DocumentType>
      <cac:Attachment>
        <cac:ExternalReference>
          <cbc:MimeCode>text/xml</cbc:MimeCode>
          <cbc:EncodingCode>UTF-8</cbc:EncodingCode>
        </cac:ExternalReference>
      </cac:Attachment>
      <cac:ResultOfVerification>
        <cbc:ValidatorID>Unidad Especial Dirección de Impuestos Y Aduanas Nacionales</cbc:ValidatorID>
        <cbc:ValidationResultCode>02</cbc:ValidationResultCode>
        <cbc:ValidationDate>{{$fechavalidacion}}</cbc:ValidationDate>
        <cbc:ValidationTime>{{$horavalidacion}}</cbc:ValidationTime>
      </cac:ResultOfVerification>
    </cac:DocumentReference>
    </cac:ParentDocumentLineReference>
  </AttachedDocument>
