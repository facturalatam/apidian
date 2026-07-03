<cac:{{preg_replace("/[\r\n|\n|\r]+/", "", $node)}}>
    <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $orderreference->id_order)}}</cbc:ID>
    <cbc:IssueDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $orderreference->issue_date_order)}}</cbc:IssueDate>
</cac:{{$node}}>
