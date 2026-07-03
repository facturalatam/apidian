<table width="100%">
    <tr>
        <td style="width: 25%;" class="text-center vertical-align-top">
            <div id="reference">
                <p style="font-weight: 700;"><strong>EVENTO ELECTRONICO No</strong></p>
                <br>
                <p style="color: red;
                    font-weight: bold;
                    font-size: 14px;
                    margin-bottom: 8px;
                    border: 1px solid #000;
                    padding: 5px 8px;
                    line-height: 1;
                    display: inline-block;
                    border-radius: 6px;">{{$sender->company->identification_number}}-{{$documentReference->number}}-{{$event->code}}</p>
                    <br>
                <p style="color: red;
                    font-weight: bold;
                    font-size: 11px;
                    margin-bottom: 8px;
                    border: 1px solid #000;
                    padding: 5px 8px;
                    line-height: 1;
                    display: inline-block;
                    border-radius: 6px;">Fecha Emisión: {{Carbon\Carbon::now()->format('Y:m:d')}}</p>
                    <br>
                <p>Fecha Validación DIAN: {{Carbon\Carbon::now()->format('Y:m:d')}}<br>
                    Hora Validación DIAN: {{Carbon\Carbon::now()->format('H:i:s')}}</p>
            </div>
        </td>
        <td style="width: 50%; padding: 0 1rem;" class="text-center vertical-align-top">
            <div id="empresa-header">
                <strong>{{$sender->name}}</strong><br>
                @if(isset($request->establishment_name))
                    <strong>{{$request->establishment_name}}</strong><br>
                @endif
            </div>
            <div id="empresa-header1">
                @if(isset($request->ivaresponsable))
                    @if($request->ivaresponsable != $company->type_regime->name)
                        NIT: {{$company->identification_number}}-{{$company->dv}} - {{$company->type_regime->name}} - {{$request->ivaresponsable}} - Obligación: {{$company->type_liability->name}}
                    @else
                        NIT: {{$company->identification_number}}-{{$company->dv}} - {{$company->type_regime->name}} - Obligación: {{$company->type_liability->name}}
                    @endif
                @else
                    NIT: {{$company->identification_number}}-{{$company->dv}} - {{$company->type_regime->name}} - Obligación: {{$company->type_liability->name}}
                @endif
                @if(isset($request->nombretipodocid))
                    Tipo Documento ID: {{$request->nombretipodocid}}<br>
                @endif
                @if(isset($request->tarifaica) && $request->tarifaica != '100')
                    TARIFA ICA: {{$request->tarifaica}}‰
                @endif
                @if(isset($request->tarifaica) && isset($request->actividadeconomica))
                    -
                @endif
                @if(isset($request->actividadeconomica))
                    ACTIVIDAD ECONOMICA: {{$request->actividadeconomica}}<br>
                @else
                    <br>
                @endif
                REPRESENTACION GRAFICA DEL EVENTO ELECTRONICO<br>
                @if(isset($request->establishment_address))
                    {{$request->establishment_address}} -
                @else
                    {{$company->address}} -
                @endif
                @inject('municipality', 'App\Municipality')
                @if(isset($request->establishment_municipality))
                    {{$municipality->findOrFail($request->establishment_municipality)['name']}} - {{$municipality->findOrFail($request->establishment_municipality)['department']['name']}} -
                @else
                    {{$company->municipality->name}} - {{$municipality->findOrFail($company->municipality->id)['department']['name']}} -
                @endif
                {{$company->country->name}}
                @if(isset($request->establishment_phone))
                    Teléfono - {{$request->establishment_phone}}<br>
                @else
                    Teléfono - {{$company->phone}}<br>
                @endif
                @if(isset($request->establishment_email))
                    E-mail: {{$request->establishment_email}} <br>
                @else
                    E-mail: {{$sender->email}} <br>
                @endif
            </div>
        </td>
        <td style="width: 25%; text-align: right;" class="vertical-align-top">
            @if(!empty($imgLogo))
                <img  style="width: 136px; height: auto;" src="{{$imgLogo}}" alt="logo">
            @endif
        </td>
    </tr>
</table>
