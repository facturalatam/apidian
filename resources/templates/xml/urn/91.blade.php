<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'; ?>
<CreditNote
    xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2"
    xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
    xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
    xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
    xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
    xmlns:sts="urn:dian:gov:co:facturaelectronica:Structures-2-1"
    xmlns:xades="http://uri.etsi.org/01903/v1.3.2#"
    xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-CreditNote-2.1.xsd">
    {{-- UBLExtensions --}}
    @isset($healthfields)
        @include('xml._ubl_extensions_health')
    @else
        @include('xml._ubl_extensions')
    @endisset
    <cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>{{preg_replace("/[\r\n|\n|\r]+/", "", $typeoperation->code)}}</cbc:CustomizationID>
    @if($typeDocument->code == 93 or $typeDocument->code == 94)
        <cbc:ProfileID>DIAN 2.1: Nota de ajuste crédito al documento equivalente</cbc:ProfileID>
        <cbc:ProfileExecutionID>{{preg_replace("/[\r\n|\n|\r]+/", "", $company->eqdocs_type_environment->code)}}</cbc:ProfileExecutionID>
    @else
        <cbc:ProfileID>DIAN 2.1: Nota Crédito de Factura Electrónica de Venta</cbc:ProfileID>
        <cbc:ProfileExecutionID>{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_environment->code)}}</cbc:ProfileExecutionID>
    @endif
    <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $resolution->next_consecutive)}}</cbc:ID>
    @if($request['is_eqdoc'] == true)
        <cbc:UUID schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->eqdocs_type_environment->code)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $typeDocument->cufe_algorithm)}}"/>
    @else
        <cbc:UUID schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_environment->code)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $typeDocument->cufe_algorithm)}}"/>
    @endif
    <cbc:IssueDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $date ?? Carbon\Carbon::now()->format('Y-m-d'))}}</cbc:IssueDate>
    <cbc:IssueTime>{{preg_replace("/[\r\n|\n|\r]+/", "", $time ?? Carbon\Carbon::now()->format('H:i:s'))}}-05:00</cbc:IssueTime>
    <cbc:CreditNoteTypeCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $typeDocument->code)}}</cbc:CreditNoteTypeCode>
    @isset($notes)
        <cbc:Note>{{preg_replace("/[\r\n|\n|\r]+/", "", $notes)}}</cbc:Note>
    @endisset
    @if(isset($idcurrency))
        <cbc:DocumentCurrencyCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}</cbc:DocumentCurrencyCode>
    @else
        <cbc:DocumentCurrencyCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}</cbc:DocumentCurrencyCode>
    @endif
    <cbc:LineCountNumeric>{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLines->count())}}</cbc:LineCountNumeric>
    {{-- HealthFields --}}
    @isset($healthfields)
        @include('xml._invoice_period', ['node' => 'InvoicePeriod'])
    @endisset
    {{-- InvoicePeriod --}}
    @isset($invoice_period)
        @include('xml._invoice_period', ['node' => 'InvoicePeriod'])
    @endisset
    {{-- DiscrepancyResponse --}}
    @if($typeDocument->id != 26)
        @isset($discrepancycode)
            @include('xml._discrepancy_response')
        @endisset
    @endif
    {{-- OrderReference --}}
    @isset($orderreference)
        @include('xml._order_reference', ['node' => 'OrderReference'])
    @endisset
    @isset($request->additional_document_reference)
        @include('xml._additional_document_reference_general', ['node' => 'request'])
    @endisset
    {{-- BillingReference --}}
    @isset($request['billing_reference'])
        @include('xml._billing_reference')
    @endisset
    {{-- AccountingSupplierParty --}}
    @include('xml._accounting', ['node' => 'AccountingSupplierParty', 'supplier' => true])
    {{-- AccountingCustomerParty --}}
    @include('xml._accounting', ['node' => 'AccountingCustomerParty', 'user' => $customer])
    {{-- PaymentMeans --}}
    @include('xml._payment_means')
    {{-- PaymentExchangeRate --}}
    @if($idcurrency !== null && $calculationrate !== null && $calculationratedate !== null)
        @include('xml._payment_exchange_rate')
    @endif
    {{-- AllowanceCharges --}}
    @include('xml._allowance_charges')
    {{-- TaxTotals --}}
    @include('xml._tax_totals', ['generalView' => true])
    {{-- HoldingTaxTotals --}}
    @include('xml._with_holding_tax_totals', ['generalView' => true])
    {{-- LegalMonetaryTotal --}}
    @include('xml._legal_monetary_total', ['node' => 'LegalMonetaryTotal'])
    {{-- CreditNoteLine --}}
    @include('xml._credit_note_lines')
</CreditNote>
