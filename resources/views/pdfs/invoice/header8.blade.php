<table width="100%">
    <tr>
        <td style="width: 23%;" class="text-center vertical-align-top">
            <div id="reference">
                <p style="font-weight: 700;"><strong>ELECTRONIC SALES INVOICE</strong></p>
                <br>
                <p style="color: black;
                    font-weight: bold;
                    font-size: 14px;
                    margin-bottom: 8px;
                    border: 1px solid #000;
                    padding: 5px 8px;
                    line-height: 1;
                    display: inline-block;
                    border-radius: 6px;">{{$resolution->prefix}} - {{$request->number}}</p>
                    <br>
                <p style="color: black;
                    font-weight: bold;
                    font-size: 11px;
                    margin-bottom: 8px;
                    border: 1px solid #000;
                    padding: 5px 8px;
                    line-height: 1;
                    display: inline-block;
                    border-radius: 6px;">Issue Date: {{$date}}</p>
                    <br>
                <p>DIAN Validation Date: {{$date}}<br>
                    DIAN Validation Time: {{$time}}</p>
            </div>
        </td>
        <td style="width: 60%; padding: 0 1rem;" class="text-center vertical-align-top">
            <div id="empresa-header">
                <strong>{{$user->name}}</strong><br>
                @if(isset($request->establishment_name) && $request->establishment_name != 'Oficina Principal')
                    <strong>{{$request->establishment_name}}</strong><br>
                @endif
            </div>
            <div id="empresa-header1">
                @if(isset($request->ivaresponsable))
                    @if($request->ivaresponsable != $company->type_regime->name)
                        NIT: {{$company->identification_number}}-{{$company->dv}} - {{$company->type_regime->name}} - {{$request->ivaresponsable}} - Liability: {{$company->type_liability->name}}
                    @else
                        NIT: {{$company->identification_number}}-{{$company->dv}} - {{$company->type_regime->name}} - Liability: {{$company->type_liability->name}}
                    @endif
                @else
                    NIT: {{$company->identification_number}}-{{$company->dv}} - {{$company->type_regime->name}} - Liability: {{$company->type_liability->name}}
                @endif
                @if(isset($request->nombretipodocid))
                    Document ID Type: {{$request->nombretipodocid}}<br>
                @endif
                @if(isset($request->tarifaica) && $request->tarifaica != '100')
                    ICA RATE: {{$request->tarifaica}}‰
                @endif
                @if(isset($request->tarifaica) && isset($request->actividadeconomica))
                    -
                @endif
                @if(isset($request->actividadeconomica))
                    ECONOMIC ACTIVITY: {{$request->actividadeconomica}}<br>
                @else
                    <br>
                @endif
                @if(isset($request->seze))
                    <?php
                        $aseze = substr($request->seze, 0, strpos($request->seze, '-', 0));
                        $asociedad = substr($request->seze, strpos($request->seze, '-', 0) + 1);
                    ?>
                    ZESE Regime Year: {{$aseze}} Company Constitution Year: {{$asociedad}}<br>
                @endif
                Electronic Billing Resolution No. {{$resolution->resolution}} <br>
                of {{$resolution->resolution_date}}, Prefix: {{$resolution->prefix}}, Range {{$resolution->from}} To {{$resolution->to}} - Valid From: {{$resolution->date_from}} To: {{$resolution->date_to}}<br>
                GRAPHIC REPRESENTATION OF ELECTRONIC INVOICE<br>
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
                    Phone - {{$request->establishment_phone}}<br>
                @else
                    Phone - {{$company->phone}}<br>
                @endif
                @if(isset($request->establishment_email))
                    E-mail: {{$request->establishment_email}} <br>
                @else
                    E-mail: {{$user->email}} <br>
                @endif
                @if (isset($request->seze))
                    PLEASE REFRAIN FROM WITHHOLDING AT SOURCE SPECIAL REGIME DECREE 2112 OF 2019
               @endif
            </div>
        </td>
        <td style="width: 25%; text-align: right;" class="vertical-align-top">
            @if(!empty($imgLogo))
                <div style="width:150px; height:70px; margin:0 0 0 auto; overflow:hidden;">
                    <table cellpadding="0" cellspacing="0" style="width:150px; height:70px; border-collapse:collapse;">
                        <tr>
                            <td style="width:150px; height:70px; overflow:hidden; vertical-align:middle; text-align:right; padding:0;">
                                <img style="display:inline-block; max-width:150px; max-height:70px; width:auto; height:auto;" src="{{$imgLogo}}" alt="logo">
                            </td>
                        </tr>
                    </table>
                </div>
            @endif
        </td>
    </tr>
</table>
