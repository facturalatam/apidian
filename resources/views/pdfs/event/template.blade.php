<!DOCTYPE html>
<html lang="es">
{{-- <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>FACTURA ELECTRONICA Nro: {{$resolution->prefix}} - {{$request->number}}</title>
</head> --}}

<body margin-top:20px margin-bottom:100px>
    <table style="font-size: 12px">
        <tr>
            <td class="vertical-align-top" style="width: 50%;">
                <table>
                    <tr>
                        <td>EMISOR</td>
                    </tr>
                    <tr>
                        <td>Documento ID:</td>
                        <td>{{$sender->company->identification_number}}</td>
                    </tr>
                    <tr>
                        <td>Nombre:</td>
                        <td>{{$sender->name}}</td>
                    </tr>
                    <tr>
                        <td>Codigo de Evento:</td>
                        <td>{{$event->code}}</td>
                    </tr>
                    <tr>
                        <td>Nombre Evento:</td>
                        <td>{{$event->name}}</td>
                    </tr>
                </table>
            </td>
            <td class="vertical-align-top" style="width: 35%; padding-left: 1rem">
                <table>
                    <tr>
                        <td>RECEPTOR</td>
                    </tr>
                    <tr>
                        <td>Documento ID:</td>
                        <td>{{$user->company->identification_number}}</td>
                    </tr>
                    <tr>
                        <td>Nombre:</td>
                        <td>{{$user->name}}</td>
                    </tr>
                </table>
            </td>
            <td class="vertical-align-top" style="width: 15%; text-align: right">
                <img style="width: 150px;" src="{{$imageQr}}">
            </td>
        </tr>
    </table>
    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th class="text-center">
                    <p><strong>Referencia: {{$documentReference->number}}<br/>CUDE: {{$documentReference->uuid}}</strong></p>
                </th>
            </tr>
        </thead>
    </table>
    <br>
    <div class="summarys">
        <div class="text-word" id="note">
            <p><strong>NOTAS:</strong></p>
            <p style="font-style: italic; font-size: 9px"> {{$notes}} </p>
            <br>
        </div>
    </div>
</body>
</html>
