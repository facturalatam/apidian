<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Credenciales de Ingreso Empresas a la plataforma {{env('APP_NAME')}}</title>
</head>
<body>
    <p>Señor(es),</p>
    <p>{{$user->name}}
    <p><p>
    <p>Le informamos que ha quedado registrado en nuestra plataforma de consulta de documentos</p>
    <p>electronicos, podra acceder a ellos mediante los hipervinculos que recibira en los correos</p>
    <p>generados cada vez que se realiza un nuevo documento electronico.</p>
    <p></p>
    <p>Password de Ingreso a la plataforma: {{$password}}</p>
    <p></p>
    <p>--------------------------------------------------------------------------------------------------------------------------------</p>
    <p>Este es un sistema automático de aviso, por favor no responda este mensaje al correo.</p>
    <p>--------------------------------------------------------------------------------------------------------------------------------</p>
</body>
</html>
