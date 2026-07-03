
<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'; ?>
<Invoice
    xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
    xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
    xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
    xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
    xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
    xmlns:sts="urn:dian:gov:co:facturaelectronica:Structures-2-1"
    xmlns:xades="http://uri.etsi.org/01903/v1.3.2#"
    xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd">
    {{-- UBLExtensions --}}
    @include('xml._ubl_extensions')
    <cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>{{preg_replace("/[\r\n|\n|\r]+/", "", $typeoperation->code)}}</cbc:CustomizationID>
    <cbc:ProfileID>DIAN 2.1: Factura Electrónica de Venta</cbc:ProfileID>
    <cbc:ProfileExecutionID>{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_environment->code)}}</cbc:ProfileExecutionID>
    <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $resolution->next_consecutive)}}</cbc:ID>
    <cbc:UUID schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_environment->code)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $typeDocument->cufe_algorithm)}}"/>
    <cbc:IssueDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $date ?? Carbon\Carbon::now()->format('Y-m-d'))}}</cbc:IssueDate>
    <cbc:IssueTime>{{preg_replace("/[\r\n|\n|\r]+/", "", $time ?? Carbon\Carbon::now()->format('H:i:s'))}}-05:00</cbc:IssueTime>
    <cbc:InvoiceTypeCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $typeDocument->code)}}</cbc:InvoiceTypeCode>
    @isset($notes)
        <cbc:Note>{{preg_replace("/[\r\n|\n|\r]+/", "", $notes)}}</cbc:Note>
    @endisset
    @if(isset($idcurrency))
        <cbc:DocumentCurrencyCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}</cbc:DocumentCurrencyCode>
    @else
        <cbc:DocumentCurrencyCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}</cbc:DocumentCurrencyCode>
    @endif
    <cbc:LineCountNumeric>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLines->count())}}</cbc:LineCountNumeric>
    {{-- AdditionalDocumentReference --}}
    @include('xml._additional_document_reference')
    {{-- OrderReference --}}
    @isset($orderreference)
        @include('xml._order_reference', ['node' => 'OrderReference'])
    @endisset
    {{-- AccountingSupplierParty --}}
    @include('xml._accounting', ['node' => 'AccountingSupplierParty', 'supplier' => true])
    {{-- AccountingCustomerParty --}}
    @include('xml._accounting', ['node' => 'AccountingCustomerParty', 'user' => $customer])
    {{-- Delivery --}}
    @isset($delivery)
       @include('xml._delivery')
    @endisset
    {{-- PaymentMeans --}}
    @include('xml._payment_means')
    {{-- PaymentTerms --}}
    @include('xml._payment_terms')
    @isset($prepaidpayment)
        {{-- PrepaidPayment --}}
        @include('xml._prepaid_payment', ['node' => 'PrepaidPayment'])
    @endisset
    {{-- PrepaidPayments --}}
    @include('xml._prepaid_payments')
    {{-- AllowanceCharges --}}
    @include('xml._allowance_charges')
    {{-- PaymentExchangeRate --}}
    @if($idcurrency !== null && $calculationrate !== null && $calculationratedate !== null)
        @include('xml._payment_exchange_rate')
    @endif
    {{-- TaxTotals --}}
    @include('xml._tax_totals', ['generalView' => true])
    {{-- HoldingTaxTotals --}}
    @include('xml._with_holding_tax_totals', ['generalView' => true])
    {{-- LegalMonetaryTotal --}}
    @include('xml._legal_monetary_total', ['node' => 'LegalMonetaryTotal'])
    {{-- InvoiceLines --}}
    @include('xml._invoice_lines')
</Invoice>
