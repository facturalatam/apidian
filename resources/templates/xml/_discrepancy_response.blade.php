<cac:DiscrepancyResponse>
    @isset($billingReference)
        <cbc:ReferenceID>{{preg_replace("/[\r\n|\n|\r]+/", "", $billingReference->number)}}</cbc:ReferenceID>
    @endisset
    <cbc:ResponseCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $discrepancycode)}}</cbc:ResponseCode>
    @isset($discrepancycode)
        @if(in_array($request['type_document_id'], [4, 13, 26]))
            @if($request['type_document_id'] == 13)
                @inject('Discrepancy', 'App\CreditNoteDiscrepancyResponseSD')
            @else
                @inject('Discrepancy', 'App\CreditNoteDiscrepancyResponse')
            @endif
        @else
            @inject('Discrepancy', 'App\DebitNoteDiscrepancyResponse')
        @endif
        <cbc:Description>{{$Discrepancy->findOrFail($discrepancycode)['name']}}</cbc:Description>
    @endisset
</cac:DiscrepancyResponse>
