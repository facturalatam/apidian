<footer id="footer" margin-top:100px>
    <hr style="margin-bottom: 4px;">
    <p id='mi-texto'>Comprobante de Evento No: {{$sender->company->identification_number}}-{{$documentReference->number}}-{{$event->code}} - Fecha y Hora de Generación: {{Carbon\Carbon::now()->format('Y:m:d')}} - {{Carbon\Carbon::now()->format('H:i:s')}}<br> CUDE: <strong>{{$cufecude}}</strong></p>
</footer>
