@foreach ($prepaidpayments as $key => $prepaidpayment)
    <cac:PrepaidPayment>
        <cbc:ID schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $prepaidpayment->idpayment)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $prepaidpayment->idpayment)}}</cbc:ID>
        @if(isset($idcurrency))
            <cbc:PaidAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($prepaidpayment->paidamount, 2, '.', ''))}}</cbc:PaidAmount>
        @else
            <cbc:PaidAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($prepaidpayment->paidamount, 2, '.', ''))}}</cbc:PaidAmount>
        @endif  
        <cbc:ReceivedDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $prepaidpayment->receiveddate)}}</cbc:ReceivedDate>
        <cbc:PaidDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $prepaidpayment->paiddate)}}</cbc:PaidDate>
        @if(isset($prepaidpayment->instructionid))
            <cbc:InstructionID>{{preg_replace("/[\r\n|\n|\r]+/", "", $prepaidpayment->instructionid)}}</cbc:InstructionID>
        @endif
    </cac:PrepaidPayment>
@endforeach
