<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Recuperacion de Credenciales de Ingreso Para Clientes a la plataforma {{env('APP_NAME')}}</title>
</head>
<body>
    <p>Señor(es),</p>
    <p>{{$customer->name}}
    <p><p>
    <p>Le informamos que su solicitud de recuperacion de password ha quedado registrado en nuestra</p>
    <p>base de datos, sin embargo, para hacerla efectiva debe realizar una confirmacion en nuestro</p>
    <p>sistema mediante dar click en el siguiente enlace:</p>
    <p></p>
    <a href={{config('app.url')}}/accept-retrieve-password/{{$customer->identification_number}}/{{$customer->newpassword}}">Haga click aqui para confirmar el cambio de password en su plataforma por el siguiente password:</a>
    <p></p>
    <p>Nuevo Password de Ingreso a la plataforma despues de confirmar: {{$password}}</p>
    <p></p>
    <p>Podra cambiar este password asignado automaticamente despues de ingresar a la plataforma</p>
    <p>mediante la opcion "Cambiar Password"</p>
    <p></p>
    <p>--------------------------------------------------------------------------------------------------------------------------------</p>
    <p>Este es un sistema automático de aviso, por favor no responda este mensaje al correo.</p>
    <p>--------------------------------------------------------------------------------------------------------------------------------</p>
</body>
</html>
