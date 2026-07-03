<cac:AdditionalDocumentReference>
    @if(isset($request['additional_document_reference']['id']))
        <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $request['additional_document_reference']['id'])}}</cbc:ID>
    @endif
    @if(isset($request['additional_document_reference']['date']))
        <cbc:IssueDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $request['additional_document_reference']['date'])}}</cbc:IssueDate>
    @endif
    @if(isset($request['additional_document_reference']['type_document_id']))
        @inject('type_document', 'App\TypeDocument')
        <cbc:DocumentTypeCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $type_document->findOrFail($request['additional_document_reference']['type_document_id'])['code'])}}</cbc:DocumentTypeCode>
    @endif
</cac:AdditionalDocumentReference>
