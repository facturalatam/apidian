<footer id="footer" style="font-size:7px;">
    <hr style="margin-bottom: 2px;">
    @if($request->type_document_id == 3)
        <p id='mi-texto'>Factura Electronica de Contingencia No: {{$resolution->prefix}} - {{$request->number}} - Fecha y Hora de Generación: {{$date}} - {{$time}}<br> CUDE: <strong>{{$cufecude}}</strong></p>
    @else
        <p id='mi-texto'>
            Factura Electronica de Venta No: {{$resolution->prefix}} - {{$request->number}} - Fecha y Hora de Generación: {{$date}} - {{$time}}<br>
            CUFE: <strong>{{$cufecude}}</strong><br>
            Modo de operación: Software Propio - by FACTURADOR<br>
            La presente Factura Electrónica de Venta, es un título valor de acuerdo con lo establecido en el Código de Comercio y en especial en los artículos 621,772 y 774. El Decreto 2242 del 24 de noviembre de 2015 y el Decreto Único 1074 de mayo de 2015. El presente título valor se asimila en todos sus efectos a una letra de cambio Art. 779 del Código de Comercio. Con esta el Comprador declara haber recibido real y materialmente las mercancías o prestación de servicios descritos en este título valor.
        </p>
    @endif
    @isset($request->foot_note)
        <p id='mi-texto-1'><strong>{{$request->foot_note}}</strong></p>
    @endisset
</footer>
