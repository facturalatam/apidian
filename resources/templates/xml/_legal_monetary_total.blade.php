<cac:{{preg_replace("/[\r\n|\n|\r]+/", "", $node)}}>
{{--    @if(isset($idcurrency))
        <cbc:LineExtensionAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->line_extension_amount, 2, '.', ''))}}</cbc:LineExtensionAmount>
    @else   --}}
        <cbc:LineExtensionAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->line_extension_amount, 2, '.', ''))}}</cbc:LineExtensionAmount>
{{--    @endif  --}}
{{--    @if(isset($idcurrency))
        <cbc:TaxExclusiveAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->tax_exclusive_amount, 2, '.', ''))}}</cbc:TaxExclusiveAmount>
    @else   --}}
        <cbc:TaxExclusiveAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->tax_exclusive_amount, 2, '.', ''))}}</cbc:TaxExclusiveAmount>
{{--    @endif  --}}
{{--    @if(isset($idcurrency))
        <cbc:TaxInclusiveAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->tax_inclusive_amount, 2, '.', ''))}}</cbc:TaxInclusiveAmount>
    @else   --}}
        <cbc:TaxInclusiveAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->tax_inclusive_amount, 2, '.', ''))}}</cbc:TaxInclusiveAmount>
{{--    @endif  --}}
    @if ($legalMonetaryTotals->allowance_total_amount)
{{--        @if(isset($idcurrency))
            <cbc:AllowanceTotalAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->allowance_total_amount, 2, '.', ''))}}</cbc:AllowanceTotalAmount>
        @else   --}}
            <cbc:AllowanceTotalAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->allowance_total_amount, 2, '.', ''))}}</cbc:AllowanceTotalAmount>
{{--        @endif  --}}
    @endif
    @if ($legalMonetaryTotals->charge_total_amount)
{{--        @if(isset($idcurrency))
            <cbc:ChargeTotalAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->charge_total_amount, 2, '.', ''))}}</cbc:ChargeTotalAmount>
        @else   --}}
            <cbc:ChargeTotalAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->charge_total_amount, 2, '.', ''))}}</cbc:ChargeTotalAmount>
{{--        @endif  --}}
    @endif
    @if ($legalMonetaryTotals->pre_paid_amount)
{{--        @if(isset($idcurrency))
            <cbc:PrepaidAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->pre_paid_amount, 2, '.', ''))}}</cbc:PrepaidAmount>
        @else   --}}
            <cbc:PrepaidAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->pre_paid_amount, 2, '.', ''))}}</cbc:PrepaidAmount>
{{--        @endif  --}}
    @endif
{{--    @if(isset($idcurrency))
        <cbc:PayableAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->payable_amount, 2, '.', ''))}}</cbc:PayableAmount>
    @else   --}}
        <cbc:PayableAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($legalMonetaryTotals->payable_amount, 2, '.', ''))}}</cbc:PayableAmount>
{{--    @endif  --}}
</cac:{{$node}}>
