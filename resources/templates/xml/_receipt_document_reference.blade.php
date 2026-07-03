@foreach($request['receipt_document_references'] as $receiptDocRef)
<cac:ReceiptDocumentReference>
    <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $receiptDocRef['id'])}}</cbc:ID>
    @if(isset($receiptDocRef['issue_date']))
    <cbc:IssueDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $receiptDocRef['issue_date'])}}</cbc:IssueDate>
    @endif
</cac:ReceiptDocumentReference>
@endforeach
