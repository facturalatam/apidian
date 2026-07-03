<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Notificacion de Comprobante Electronico Nro {{$invoice[0]->prefix}}-{{$invoice[0]->number}}</title>
</head>
<body>
    <p>Señor(es),</p>
    <p>{{$customer->name}}
    @if(isset($customer->identification_number))
        <p>Numero Id. {{$customer->identification_number}}
    @else
        <p>Numero Id. {{$customer->company->identification_number}}
    @endif
    <p><p>
    <p>Le informamos ha recibido un documento de {{$company->user->name}}.</p>
    <p></p>
    <p>Número de documento: {{$invoice[0]->prefix}}{{$invoice[0]->number}}</p>
    <p>Fecha de emisión: {{$invoice[0]->created_at}}</p>
    <p>Valor: {{number_format($invoice[0]->total, 2)}}</p>
    <p>Si requiere consultar el documento en nuestro sitio por favor ingrese a: </p>
    @if(isset($customer->identification_number))
        <a href="{{config('app.url')}}/customerlogin/{{$company->identification_number}}/{{$customer->identification_number}}">{{env('APP_URL')}}/customerlogin/{{$company->identification_number}}/{{$customer->identification_number}}</a>
    @else
        <a href="{{config('app.url')}}/customerlogin/{{$company->identification_number}}/{{$customer->company->identification_number}}">{{env('APP_URL')}}/customerlogin/{{$company->identification_number}}/{{$customer->company->identification_number}}</a>
    @endif
    <p></p>
<!--    <p>Para ACEPTAR ó RECHAZAR este documento, haga click en el siguiente enlace: </p> -->
    @if(isset($customer->identification_number))
<!--        <a href="{{config('app.url')}}/accept-reject-document/{{$company->identification_number}}/{{$customer->identification_number}}/{{$invoice[0]->prefix}}/{{$invoice[0]->number}}/{{date_format($invoice[0]->created_at, 'Y-m-d')}}">ACEPTAR ó RECHAZAR documento</a>  -->
    @else
<!--        <a href="{{config('app.url')}}/accept-reject-document/{{$company->identification_number}}/{{$customer->company->identification_number}}/{{$invoice[0]->prefix}}/{{$invoice[0]->number}}/{{date_format($invoice[0]->created_at, 'Y-m-d')}}">ACEPTAR ó RECHAZAR documento</a>  -->
    @endif
    <p>---------------------------------------------------------------------------------------------------------------------------</p>
    <p>Este es un sistema automático de aviso, por favor no responda este mensaje al correo.</p>
    <p>---------------------------------------------------------------------------------------------------------------------------</p>
    @php
        $cadenahtml = "<b>Este Texto Esta En Negrilla</b>"
    @endphp
    {!! $cadenahtml !!}
</body>
</html>
