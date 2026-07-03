<footer id="footer">
    <hr style="height: 2px; background-color: rgb(6, 103, 194) ; border: none;">
    <table width="100%">
        <tr>
            <td style="padding: 0; width: 25%;" class="text-center">
                @if(isset($logo_empresa_emisora) and !is_null($logo_empresa_emisora))
                    <table cellpadding="0" cellspacing="0" style="width:70px; height:35px; margin:0 auto; border-collapse:collapse;">
                        <tr>
                            <td style="width:70px; height:35px; overflow:hidden; vertical-align:middle; text-align:center; padding:0;">
                                <img style="display:inline-block; max-width:70px; max-height:35px; width:auto; height:auto;" src="{{$logo_empresa_emisora}}">
                            </td>
                        </tr>
                    </table>
                @endif
                <?php $app_name = env("APP_NAME", "API DIAN"); ?>
                    <p style="color: black; font-size: 8px; margin-bottom: 2px; padding: 0px 0px 0px 0px;">Modo de operación: Software Propio generada con Software {{$app_name}}</p>
            </td>
            <td style="padding: 0; width: 75%;" class="text-center">
                <p style="color: black; font-size: 8px; margin-bottom: 2px; padding: 0px 0px 0px 0px;">CUFE: {{$cufecude}}</p>
                <p style="color: black; font-size: 8px; margin-bottom: 2px; padding: 0px 0px 0px 0px;">Documento Soporte No: {{$resolution->prefix}} - {{$request->number}} - Fecha y Hora de Generación: {{$date}} - {{$time}}</p>
                <p style="color: black; font-size: 7px; margin-bottom: 2px; padding: 0px 0px 0px 0px;">El presente Documento soporte a no obligados, es un título valor de acuerdo con lo establecido en el Código de Comercio y en especial en los artículos 621,772 y 774. El Decreto 2242 del 24 de noviembre de 2015 y el Decreto Único 1074 de mayo de 2015. El presente título valor se asimila en todos sus efectos a una letra de cambio Art. 779 del Código de Comercio. Con esta el Comprador declara haber recibido real y materialmente las mercancías o prestación de servicios descritos en este título valor.</p>
                @isset($request->foot_note)
                    <p><strong>{{$request->foot_note}}</strong></p>
                @endisset
            </td>
        </tr>
    </table>
</footer>
