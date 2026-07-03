<footer id="footer">
    <hr style="margin-bottom: 4px;">
    @if($request->type_document_id == 3)
        <p id='mi-texto'>Factura Electronica de Contingencia No: {{$resolution->prefix}} - {{$request->number}} - Fecha y Hora de Generación: {{$date}} - {{$time}}<br> CUDE: <strong>{{$cufecude}}</strong></p>
    @else
        <p id='mi-texto'>Factura Electronica de Venta No: {{$resolution->prefix}} - {{$request->number}} - Fecha y Hora de Generación: {{$date}} - {{$time}}<br> CUFE: <strong>{{$cufecude}}</strong></p>
    @endif
    @isset($request->foot_note)
        <p id='mi-texto-1'><strong>{{$request->foot_note}}</strong></p>
    @endisset
</footer>
