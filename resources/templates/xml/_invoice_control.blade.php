<sts:InvoiceControl>
    <sts:InvoiceAuthorization>{{preg_replace("/[\r\n|\n|\r]+/", "", $resolution->resolution)}}</sts:InvoiceAuthorization>
    <sts:AuthorizationPeriod>
        <cbc:StartDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $resolution->date_from)}}</cbc:StartDate>
        <cbc:EndDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $resolution->date_to)}}</cbc:EndDate>
    </sts:AuthorizationPeriod>
    <sts:AuthorizedInvoices>
        @if ($resolution->prefix)
            <sts:Prefix>{{preg_replace("/[\r\n|\n|\r]+/", "", $resolution->prefix)}}</sts:Prefix>
        @endif
        <sts:From>{{preg_replace("/[\r\n|\n|\r]+/", "", $resolution->from)}}</sts:From>
        <sts:To>{{preg_replace("/[\r\n|\n|\r]+/", "", $resolution->to)}}</sts:To>
    </sts:AuthorizedInvoices>
</sts:InvoiceControl>
