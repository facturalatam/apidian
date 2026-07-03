<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel='stylesheet' href='{{base_path()."/resources/css"}}/bootstrap.min.css'>
	<title>NOTA CREDITO ELECTRONICA Nro: {{$resolution->prefix}} - {{$request->number}}</title>
</head>

<body>
    <style type="text/css">
* {
    margin: 0; padding: 0;
    font-family: 'sans-serif'
  }

html, body{
    vertical-align: baseline
}

article, aside, details, figcaption, figure, footer, header, hgroup, menu, nav, section{
    display: block
}

body{
    line-height: 1
}

ol, ul{
    list-style: none
}

blockquote, q{
    quotes: none
}

blockquote:before, blockquote:after, q:before, q:after{
    content: '';
    content: none
}

table{
    border-collapse: collapse;
    border-spacing: 0
}

body{
    font-family: 'sans-serif';
    font-size: 14px
}

strong{
    font-weight: 700
}

.row{
    font-size: 11px;
}

#container{
    position: relative;
    padding: 4%;
    width: 750px;
    margin: 0 auto;
}

#header{
    height: 80px
}

#header > #reference{
    position: absolute;
    margin-top: 20px;
}

#reference{
    font-size: 10px;
}

#header > #reference h3{
    margin: 0
}

#header > #reference h4{
    margin: 0;
    font-size: 85%;
    font-weight: 600
}

#header > #reference p{
    margin: 0;
    margin-top: 2%;
    font-size: 11px;
    text-align: center;
}

#header > #logo{
    width: 100%;
    float: left
}

#fromto{
    height: 160px
}

#fromto > #from, #fromto > #to{
    width: 33%;
    min-height: 90px;
    margin-top: 30px;
    font-size: 85%;
    padding: 1.5%;
    line-height: 120%
}

#fromto > #from{
    float: left;
    width: 33%;
    background:#efefef;
    margin-top: 30px;
    font-size: 85%;
    padding: 1.5%
}

#fromto > #to{
    float: left;
    margin-left: 12px;
    width: 31%;
    border: solid grey 1px
}

.subheader{
    margin-top: 10px
}

.subheader > p{
    font-weight: 700;
    text-align: right;
    margin-bottom: 1%;
    font-size: 65%
}

.subheader > table{
    width: 100%;
    font-size: 85%;
    border: solid grey 1px
}

.subheader > table th:first-child{
    text-align: left
}

.subheader > table th{
    font-weight: 400;
    padding: 1px 4px
}

.subheader > table td{
    padding: 1px 4px
}

.subheader > table th:nth-child(2), .subheader > table th:nth-child(4){
    width: 45px
}

.subheader > table th:nth-child(3){
    width: 60px
}

.subheader > table th:nth-child(5){
    width: 80px
}

.subheader > table tr td:not(:first-child){
    text-align: right;
    padding-right: 1%
}

.subheader table td{
    border-right:solid grey 1px
}

.subheader table tr td{
    padding-top: 3px;
    padding-bottom: 3px;
    height: 10px
}

.subheader table tr:nth-child(1){
    border: solid grey 1px
}

.subheader table tr th{
    border-right: solid grey 1px;
    padding: 3px
}

.subheader table tr:nth-child(2) > td{
    padding-top: 8px
}

.items{
    margin-top: 10px
}

.items > p{
    font-weight: 700;
    text-align: right;
    margin-bottom: 1%;
    font-size: 65%
}

.items > table{
    width: 100%;
    font-size: 85%;
    border: solid grey 1px
}

.items > table th:first-child{
    text-align: left
}

.items > table th{
    font-weight: 400;
    padding: 1px 4px
}

.items > table td{
    padding: 1px 4px
}

.items > table th:nth-child(2), .items > table th:nth-child(4){
    width: 45px
}

.items > table th:nth-child(3){
    width: 60px
}
.items > table th:nth-child(5){
    width: 80px
}

.items > table tr td:not(:first-child){
    text-align: right;
    padding-right: 1%
}

.items table td{
    border-right: solid grey 1px
}

.items table tr td{
    padding-top: 3px;
    padding-bottom: 3px;
    height: 10px
}

.items table tr:nth-child(1){
    border: solid grey 1px
}

.items table tr th{
    border-right: solid grey 1px;
    padding: 3px
}

.items table tr:nth-child(2) > td{
    padding-top: 8px
}

.summary{
    height: 130px;
    margin-top: 30px
}

.summary #note{
    float: left
}

.summary #note h4{
    font-size: 10px;
    font-weight: 600;
    font-style: italic;
    margin-bottom: 4px
}

.summary #note p{
    font-size: 10px;
    font-style: italic
}

.summary #total table{
    font-size: 85%;
    width: 260px;
    float: right;
    margin-top: 40px;
}

.summary #total table td{
    padding: 3px 4px
}

.summary #total table tr td:last-child{
    text-align: right
}

.summary #total table tr:nth-child(3){
    background:#efefef;
    font-weight: 600
}

#footer{
    margin: auto;
    margin-top: 20px;
    left: 4%;
    bottom: 4%;
    right: 4%;
    border-top: solid grey 1px
}

#footer p{
    margin-top: 1%;
    font-size: 65%;
    line-height: 140%;
    text-align: center
}

.sinbode tr td, .sinbode tr, .sinbode tr th{
    border: none!important;
}

.summarys{
    border: solid grey 1px;
    margin-top: 10px;
    padding: 10px;
}

.summaryss{
    border: solid grey 1px;
    margin-top: 10px;
    padding: 3px;
}

#fromto > #from, #fromto > #qr {
    width: 11%;
    min-height: 90px;
    margin-top: 30px;
    font-size: 85%;
    padding: 1.5%;
    line-height: 120%;
}

#fromto > #from {
    width: 46%;
    min-height: 90px;
    margin-top: 30px;
    font-size: 85%;
    padding: 1.5%;
    line-height: 120%;
}

#fromto > #qr {
    float: right;
}

.summary #firma table td, .summary #firma table tr{
    border: none !important;
}

#empresa-header{
    text-align: center;
    font-size: 14px;
}

#empresa-header1{
    text-align: center;
    font-size: 10px;
}
    </style>
    <div id="container">
        <div id="header">
            <div class="row">
                <div class="col-sm-3">
                    <div id="reference" style="text-align: center;">
                        <p style="text-align: center;"><strong>NOTA CREDITO DE LA FACTURA ELECTRONICA DE VENTA No</strong></p>
                        <p style="color: red;
                            font-weight: bold;
                            font-size: 14px;
                            text-align: center;
                            margin-top: 3px;
                            margin-bottom: 3px;
                            border: 1px solid #000;
                            padding: 5px;
                            line-height: 10px;
                            border-radius: 6px;">{{$resolution->prefix}} - {{$request->number}}</p>
                        <p>Fecha Validacion DIAN: {{$date}}<br>
                           Hora Validacion DIAN: {{$time}}</p>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div id="empresa-header">
                        <strong>{{$user->name}}</strong><br>
                        @if(isset($request->establishment_name))
                            <strong>{{$request->establishment_name}}</strong><br>
                        @endif
                    </div>
                    <div id="empresa-header1">
                        @if(isset($request->ivaresponsable))
                            @if($request->ivaresponsable != $company->type_regime->name)
                                NIT: {{$company->identification_number}}-{{$company->dv}} - {{$company->type_regime->name}} - {{$request->ivaresponsable}} - {{$company->type_liability->name}}<br>
                            @else
                                NIT: {{$company->identification_number}}-{{$company->dv}} - {{$company->type_regime->name}} - {{$company->type_liability->name}}<br>
                            @endif
                        @else
                            NIT: {{$company->identification_number}} - {{$company->type_regime->name}} - {{$company->type_liability->name}}<br>
                        @endif
                        @if(isset($request->nombretipodocid))
                            Tipo Documento ID: {{$request->nombretipodocid}}<br>
                        @endif
                        @if(isset($request->tarifaica)  && $request->tarifaica != '100')
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
                        REPRESENTACION GRAFICA DE NOTA CREDITO ELECTRONICA<br>
                        {{$company->address}} - {{$company->municipality->name}} - {{$company->country->name}} Telefono - {{$company->phone}}<br>
                        E-mail: {{$user->email}} <br>
                    </div>
                </div>
                <div class="col-sm-3">
                    <img src="{{$imgLogo}}" width="100%" alt="logo">
                </div>
        </div>
        <br/><br/>
        <div class="row">
            <div class="col-sm-5">
                <table>
                    <tr>
                        <td>CC o NIT:</td>
                        <td>{{$customer->company->identification_number}}-{{$request->customer['dv'] ?? NULL}} </td>
                    </tr>
                    <tr>
                        <td>Cliente:</td>
                        <td> {{$customer->name}}</td>
                    </tr>
                    <tr>
                        <td>Regimen:</td>
                        <td> {{$customer->company->type_regime->name}}</td>
                    </tr>
                    <tr>
                        <td>Obligación:</td>
                        <td> {{$customer->company->type_liability_names ?? $customer->company->type_liability->name}}</td>
                    </tr>
                    <tr>
                        <td>Dirección:</td>
                        <td>{{$customer->company->address}}</td>
                    </tr>
                    <tr>
                        <td>Ciudad:</td>
                        <td> {{$customer->company->municipality->name}} - {{$customer->company->country->name}} </td>
                    </tr>
                    <tr>
                        <td>Telefono:</td>
                        <td>{{$customer->company->phone}}</td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td>{{$customer->email}}</td>
                    </tr>
                </table>
            </div>

            <div class="col-sm-2">
                <img style="width: 95px;" src="{{$imageQr}}">
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <?php $billing_reference = json_decode(json_encode($request->billing_reference)) ?>
                <table class="table table-bordered table-condensed table-striped table-responsive">
                    <thead>
                        <tr>
                            <th class="text-center"><p><strong>Referencia: {{$billing_reference->number}} - Fecha: {{$billing_reference->issue_date}}<br/>
                                                               CUFE: {{$billing_reference->uuid}}</strong></p></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-condensed table-striped table-responsive">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Código</th>
                            <th class="text-center">Descripcion</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">UM</th>
                            <th class="text-center">Val. Unit</th>
                            <th class="text-center">IVA/IC</th>
                            <th class="text-center">Dcto</th>
                            <th class="text-center">Val. Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $ItemNro = 0; ?>
                        @foreach($request['credit_note_lines'] as $item)
                            <?php $ItemNro = $ItemNro + 1; ?>
                            <tr>
                                @inject('um', 'App\UnitMeasure')
                                <td>{{$ItemNro}}</td>
                                <td>{{$item['code']}}</td>
                                <td>{{$item['description']}}</td>
                                <td class="text-right">{{number_format($item['invoiced_quantity'], 2)}}</td>
                                <td class="text-right">{{$um->findOrFail($item['unit_measure_id'])['name']}}</td>
                                <td class="text-right">{{number_format($item['price_amount'], 2)}}</td>
                                @if(isset($item['tax_totals'][0]['tax_amount']))
                                    <td class="text-right">{{number_format($item['tax_totals'][0]['tax_amount'], 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format(0, 2)}}</td>
                                @endif
                                @if(isset($item['allowance_charges']))
                                    <td class="text-right">{{number_format($item['allowance_charges'][0]['amount'], 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format("0", 2)}}</td>
                                @endif
                                <td class="text-right">{{number_format($item['invoiced_quantity'] * $item['price_amount'], 2)}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-condensed table-striped table-responsive">
                    <thead>
                        <tr>
                            <th class="text-center">Impuestos</th>
                            <th class="text-center">Retenciones</th>
                            <th class="text-center">Totales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <table class="table table-bordered table-condensed table-striped table-responsive">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Tipo</th>
                                            <th class="text-center">Base</th>
                                            <th class="text-center">Porcentaje</th>
                                            <th class="text-center">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($request->tax_totals))
                                            <?php $TotalImpuestos = 0; ?>
                                            @foreach($request->tax_totals as $item)
                                                <tr>
                                                    <?php $TotalImpuestos = $TotalImpuestos + $item['tax_amount'] ?>
                                                    @inject('tax', 'App\Tax')
                                                    <td>{{$tax->findOrFail($item['tax_id'])['name']}}</td>
                                                    <td>{{number_format($item['taxable_amount'], 2)}}</td>
                                                    <td>{{number_format($item['percent'], 2)}}%</td>
                                                    <td>{{number_format($item['tax_amount'], 2)}}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <?php $TotalImpuestos = 0; ?>
                                        @endif
                                    </tbody>
                                </table>
                            </td>
                            <td>
                                <table class="table table-bordered table-condensed table-striped table-responsive">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Tipo</th>
                                            <th class="text-center">Base</th>
                                            <th class="text-center">Porcentaje</th>
                                            <th class="text-center">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $TotalRetenciones = 0; ?>
                                        @if(isset($withHoldingTaxTotal))
                                            @foreach($withHoldingTaxTotal as $item)
                                                <tr>
                                                    <?php $TotalRetenciones = $TotalRetenciones + $item['tax_amount'] ?>
                                                    @inject('tax', 'App\Tax')
                                                    <td>{{$tax->findOrFail($item['tax_id'])['name']}}</td>
                                                    <td>{{number_format($item['taxable_amount'], 2)}}</td>
                                                    <td>{{number_format($item['percent'], 2)}}%</td>
                                                    <td>{{number_format($item['tax_amount'], 2)}}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </td>
                            <td>
                                <table class="table table-bordered table-condensed table-striped table-responsive">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Concepto</th>
                                            <th class="text-center">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Nro Lineas:</td>
                                            <td>{{$ItemNro}}</td>
                                        </tr>
                                        <tr>
                                            <td>Base:</td>
                                            <td>{{number_format($request->legal_monetary_totals['line_extension_amount'], 2)}}</td>
                                        </tr>
                                        <tr>
                                            <td>Impuestos:</td>
                                            <td>{{number_format($TotalImpuestos, 2)}}</td>
                                        </tr>
                                        <tr>
                                            <td>Retenciones:</td>
                                            <td>{{number_format($TotalRetenciones, 2)}}</td>
                                        </tr>
                                        <tr>
                                            <td>Descuentos:</td>
                                            <td>{{number_format($request->legal_monetary_totals['allowance_total_amount'], 2)}}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Nota:</td>
                                            <td>{{number_format($request->legal_monetary_totals['payable_amount'] + $request->legal_monetary_totals['allowance_total_amount'], 2)}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="summarys">
            <div id="note">
                @inject('Varios', 'App\Custom\NumberSpellOut')
                <p><strong>NOTAS:</strong></p>
                <br>{{$notes}}
                <p><br>
                <strong>SON</strong>: {{$Varios->convertir(round($request->legal_monetary_totals['payable_amount'] + $request->legal_monetary_totals['allowance_total_amount'], 2))}} M/CTE*********.</p>
            </div>
        </div>

        <div class="summary" >
            <div id="note">
                <p>CUALQUIER INQUIETUD SOBRE ESTE DOCUMENTO AL TELEFONO {{$company->phone}} o al e-mail {{$user->email}}<br>
                    <br>
                    <div id="firma">
                        <p><strong>FIRMA ACEPTACIÓN:</strong></p><br>
                        <p><strong>CC:</strong></p><br>
                        <p><strong>FECHA:</strong></p><br>
                    </div>
                </p>
            </div>
        </div>
        <br/>

        <div id="footer">
            <p id='mi-texto'>Nota Credito No: {{$resolution->prefix}} - {{$request->number}} - Fecha y Hora de Generacion: {{$date}} - {{$time}}<br> CUDE: <strong>{{$cufecude}}</strong></p>
        </div>
    </div>
</body>
</html>
