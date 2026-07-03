<cac:{{preg_replace("/[\r\n|\n|\r]+/", "", $node)}}>
    <cbc:StartDate>{{preg_replace("/[\r\n|\n|\r]+/", "", isset($healthfields->invoice_period_start_date) ? $healthfields->invoice_period_start_date : $invoice_period->start_date)}}</cbc:StartDate>
    <cbc:EndDate>{{preg_replace("/[\r\n|\n|\r]+/", "", isset($healthfields->invoice_period_end_date) ? $healthfields->invoice_period_end_date : $invoice_period->end_date)}}</cbc:EndDate>
</cac:{{$node}}>
