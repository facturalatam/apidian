@foreach ($paymentForm as $key => $paymentF)
    <cac:PaymentMeans>
        <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $paymentF['code'])}}</cbc:ID>
        <cbc:PaymentMeansCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $paymentF['payment_method_code'])}}</cbc:PaymentMeansCode>
        @if(isset($paymentF['payment_due_date']))
            <cbc:PaymentDueDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $paymentF['payment_due_date'])}}</cbc:PaymentDueDate>
        @else
            <cbc:PaymentDueDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $date ?? Carbon\Carbon::now()->format('Y-m-d'))}}</cbc:PaymentDueDate>
        @endif
        <cbc:PaymentID>{{preg_replace("/[\r\n|\n|\r]+/", "", $key)}}</cbc:PaymentID>
    </cac:PaymentMeans>
@endforeach
