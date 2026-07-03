@foreach ($allowanceCharges as $key => $allowanceCharge)
    <cac:AllowanceCharge>
        @if($typeDocument->id == '24')
            <cbc:ID schemeName="1">{{preg_replace("/[\r\n|\n|\r]+/", "", ($key + 1))}}</cbc:ID>
        @else
            <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", ($key + 1))}}</cbc:ID>
        @endif
        <cbc:ChargeIndicator>{{preg_replace("/[\r\n|\n|\r]+/", "", $allowanceCharge->charge_indicator)}}</cbc:ChargeIndicator>
        @if($allowanceCharge->type_discount)
            <cbc:AllowanceChargeReasonCode>{{ preg_replace("/[\r\n|\n|\r]+/", "", $allowanceCharge->type_discount->code) }}</cbc:AllowanceChargeReasonCode>
        @elseif($allowanceCharge->discount)
            @if (($allowanceCharge->charge_indicator === 'false') && ($allowanceCharge->discount))
                @if($request['is_eqdoc'] == true or $typeDocument->id == 15)
                    <cbc:AllowanceChargeReasonCode>00</cbc:AllowanceChargeReasonCode>
                @else
                    <cbc:AllowanceChargeReasonCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $allowanceCharge->discount->code)}}</cbc:AllowanceChargeReasonCode>
                @endif
            @endif
        @endif
        <cbc:AllowanceChargeReason>{{preg_replace("/[\r\n|\n|\r]+/", "", $allowanceCharge->allowance_charge_reason)}}</cbc:AllowanceChargeReason>
        <cbc:MultiplierFactorNumeric>{{preg_replace("/[\r\n|\n|\r]+/", "", $allowanceCharge->multiplier_factor_numeric)}}</cbc:MultiplierFactorNumeric>
{{--        @if(isset($idcurrency))
            <cbc:Amount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($allowanceCharge->amount, 2, '.', ''))}}</cbc:Amount>
        @else   --}}
            <cbc:Amount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($allowanceCharge->amount, 2, '.', ''))}}</cbc:Amount>
{{--        @endif  --}}
        @if ($allowanceCharge->base_amount)
{{--            @if(isset($idcurrency))
                <cbc:BaseAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($allowanceCharge->base_amount, 2, '.', ''))}}</cbc:BaseAmount>
            @else   --}}
                <cbc:BaseAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($allowanceCharge->base_amount, 2, '.', ''))}}</cbc:BaseAmount>
{{--            @endif  --}}
        @endif
    </cac:AllowanceCharge>
@endforeach
