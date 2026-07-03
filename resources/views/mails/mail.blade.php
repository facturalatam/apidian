<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Notificacion de Comprobante Electronico Nro {{$invoice[0]->prefix}}-{{$invoice[0]->number}}</title>
</head>
<body>
    <div style="border-radius: 5px; border: solid 1px gray; padding: 20px;">
        @if($request_in && $request_in->html_header)
            {!! $request_in->html_header !!}
        @else
            @if(isset($customer->identification_number))
                <p>Señor(es), {{$customer->name}} identificado con NIT {{$customer->identification_number}} </p>
            @else
                <p>Señor(es), {{$customer->name}} identificado con NIT {{$customer->company->identification_number}} </p>
            @endif
            <p style="margin-bottom: 5px !important; margin-top: 5px !important;">Le informamos ha recibido un documento electronico de {{$company->user->name}}.</p>
            <p></p>
        @endif

        <?php $showAcceptRejectButtons = false ?>
        @if($request_in && $request_in->html_body)
            {!! $request_in->html_body !!}
        @else
            <table style="width: 100%; text-align: center;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 8px; border-radius: 5px;">N&uacute;mero de documento</th>
                        <th style="border: 1px solid #ddd; padding: 8px; border-radius: 5px;">Fecha</th>
                        <th style="border: 1px solid #ddd; padding: 8px; border-radius: 5px;">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <p>{{$invoice[0]->prefix}}{{$invoice[0]->number}}</p>
                        </td>
                        <td>
                            <p>{{$invoice[0]->created_at}}</p>
                        </td>
                        <td>
                            <p>{{number_format($invoice[0]->total, 2)}}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
            @if($showAcceptRejectButtons)
                <p style="margin-bottom: 10px !important; padding-bottom: 10px !important"> Adjunto en este correo encontrará el <span style="color: red !important">PDF</span> y <span style="color: #0880e8 !important">XML</span> de su documento. Si requiere consultar el documento en linea haga click en el siguiente enlace.</p>
            @else
                <p style="margin-bottom: 10px !important; padding-bottom: 10px !important"> Adjunto en este correo encontrará el <span style="color: red !important">PDF</span> y <span style="color: #0880e8 !important">XML</span> de su documento.</p>
            @endif
            <p></p>
        @endif
        @if($request_in && $request_in->html_buttons)
            {!! $request_in->html_buttons !!}
        @else
            @if(isset($customer->identification_number))
                <div media="(min-width: 768px)" style="display: inline-grid;padding: 5px;">
                    <!-- Boton ver factura  -->
                    <a style="font-weight: bold; text-decoration: none; border: 0;padding: 10px 32px; color: #0880e8; border-radius: 50px; border: 2px solid #0880e8; background: #fff; margin-bottom: 10px !important; text-align: center;" href="{{config('app.url')}}/customerlogin/{{$company->identification_number}}/{{$customer->identification_number}}">Ver factura</a>
                    @if($showAcceptRejectButtons)
                        <!-- Boton aceptar o rechazar -->
                        <a style="font-weight: bold; text-decoration: none; border: 0;padding: 10px 32px; color: #0880e8; border-radius: 50px; border: 2px solid #0880e8; background: #fff; text-align: center;" href="{{config('app.url')}}/accept-reject-document/{{$company->identification_number}}/{{$customer->identification_number}}/{{$invoice[0]->prefix}}/{{$invoice[0]->number}}/{{date_format($invoice[0]->created_at, 'Y-m-d')}}">Aceptar y/o Rechazar documento</a>
                    @endif
                </div>
            @else
                <div media="(min-width: 768px)" style="display: inline-grid;padding: 5px;">
                    @if($showAcceptRejectButtons)
                        <!-- Boton ver factura -->
                        <a style="font-weight: bold; text-decoration: none; border: 0;padding: 10px 32px; color: #0880e8; border-radius: 50px; border: 2px solid #0880e8; background: #fff; margin-bottom: 10px !important; text-align: center;" href="{{config('app.url')}}/customerlogin/{{$company->identification_number}}/{{$customer->company->identification_number}}">Ver factura</a>
                    @endif
                    @if(false)
                        <!-- Boton aceptar o rechazar -->
                        <a style="font-weight: bold; text-decoration: none; border: 0;padding: 10px 32px; color: #0880e8; border-radius: 50px; border: 2px solid #0880e8; background: #fff; text-align: center;" href="{{config('app.url')}}/accept-reject-document/{{$company->identification_number}}/{{$customer->company->identification_number}}/{{$invoice[0]->prefix}}/{{$invoice[0]->number}}/{{date_format($invoice[0]->created_at, 'Y-m-d')}}">Aceptar y/o Rechazar documento</a>
                    @endif
                </div>
            @endif
            <p></p>
        @endif
        @if($invoice[0]->ambient_id == 1)
            <a style="font-weight: bold; text-decoration: none; border: 0;padding: 10px 32px; color: #0880e8; border-radius: 50px; border: 2px solid #0880e8; background: #fff; margin-bottom: 10px !important; text-align: center;" href="https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey={{$invoice[0]->cufe}}">Verificar En La DIAN</a>
        @else
            <a style="font-weight: bold; text-decoration: none; border: 0;padding: 10px 32px; color: #0880e8; border-radius: 50px; border: 2px solid #0880e8; background: #fff; margin-bottom: 10px !important; text-align: center;" href="https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey={{$invoice[0]->cufe}}">Verificar En La DIAN</a>
        @endif

        @if($request_in && $request_in->html_footer)
            {!! $request_in->html_footer !!}
        @else
            @if($showAcceptRejectButtons)
                <p>Previamente ha recibido un correo con las credenciales de ingreso a la plataforma.</p>
                <p></p>
                <p>---------------------------------------------------------------------------------------------------------------------------</p>
                <p>Este es un sistema automático de aviso, por favor no responda este mensaje al correo.</p>
                <p>---------------------------------------------------------------------------------------------------------------------------</p>
            @endif
        @endif
    </div>
</body>

</html>
