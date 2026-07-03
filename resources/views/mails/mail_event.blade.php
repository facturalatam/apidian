<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Notificacion de Comprobante de Evento Nro {{$sender->company['identification_number']}}{{$document[0]->number}}{{$event->code}}</title>
</head>
<body>
    <p>Señor(a),</p>
    <p>{{$company->name}}
    <p>Numero Id. {{$company->company['identification_number']}}
    <p><p>
    <p>Le informamos ha recibido un documento de evento electronico de {{$sender->name}}.</p>
    <p></p>
    <p>Número de documento: {{$sender->company['identification_number']}}{{$document[0]->number}}{{$event->code}}</p>
    <p>Fecha de emisión: {{$document[0]->created_at}}</p>
    <p></p>
    <p>---------------------------------------------------------------------------------------------------------------------------</p>
    <p>Este es un sistema automático de aviso, por favor no responda este mensaje al correo.</p>
    <p>---------------------------------------------------------------------------------------------------------------------------</p>
</body>
</html>
