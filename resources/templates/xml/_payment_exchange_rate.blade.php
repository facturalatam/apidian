<cac:PaymentExchangeRate>
    <cbc:SourceCurrencyCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}</cbc:SourceCurrencyCode>
    <cbc:SourceCurrencyBaseRate>1.00</cbc:SourceCurrencyBaseRate>
    <cbc:TargetCurrencyCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}</cbc:TargetCurrencyCode>
    <cbc:TargetCurrencyBaseRate>1.00</cbc:TargetCurrencyBaseRate>
    <cbc:CalculationRate>{{preg_replace("/[\r\n|\n|\r]+/", "", $calculationrate)}}</cbc:CalculationRate>
    <cbc:Date>{{preg_replace("/[\r\n|\n|\r]+/", "", $calculationratedate)}}</cbc:Date>
</cac:PaymentExchangeRate>
