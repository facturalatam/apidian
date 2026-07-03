@extends('layouts.backtemplate')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Aceptar ó Rechazar Documento Electronico</div>

                <div class="panel-body">
                    {{ csrf_field() }}

                    <form method="POST" action="{{ route('acceptrejectdocument') }}">
                        <div class="col-md-10 col-md-offset-2">
                            <label class="center-block">Documento electrónico por el cual el Adquiriente manifiesta que ha recibido la factura electrónica, de conformidad con el artículo 774 del Código de Comercio.</label>
                        </div>
                        <div class="col-md-10 col-md-offset-2">
                            <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                            <input type="hidden" name="customer_idnumber" value="{{$customer_idnumber}}">
                            <input type="hidden" name="prefix" value="{{$prefix}}">
                            <input type="hidden" name="docnumber" value="{{$docnumber}}">
                            <input type="hidden" name="issuedate" value="{{$issuedate}}">
                            <input type="hidden" name="eventcode" value="1">
                            <button type="submit" class="btn btn-primary">Acuse de recibo de Factura Electrónica de Venta</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('acceptrejectdocument') }}">
                        <div class="col-md-10 col-md-offset-2">
                            <label class="center-block">Documento electrónico por el cual el Adquiriente informa del recibo de los bienes o servicios adquiridos, de conformidad con el artículo 773 del Código de Comercio y en concordancia con el parágrafo 1 del artículo 2.2.2.53.4. del Decreto 1074 de 2015 Único Reglamentario del Sector Comercio, Industria y Turismo</label>
                        </div>
                        <div class="col-md-10 col-md-offset-2">
                            <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                            <input type="hidden" name="customer_idnumber" value="{{$customer_idnumber}}">
                            <input type="hidden" name="prefix" value="{{$prefix}}">
                            <input type="hidden" name="docnumber" value="{{$docnumber}}">
                            <input type="hidden" name="issuedate" value="{{$issuedate}}">
                            <input type="hidden" name="eventcode" value="3">
                            <button type="submit" class="btn btn-primary">Recibo del bien y/o prestación del servicio</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('acceptrejectdocument') }}">
                        <div class="col-md-10 col-md-offset-2">
                            <label class="center-block">Documento electrónico por el cual el Adquiriente informa al Emisor que acepta expresamente el Documento Electrónico que origina este tipo de ApplicationResponse de conformidad con el artículo 773 del Código de Comercio y en concordancia con el numeral 1 del artículo 2.2.2.53.4. del Decreto 1074 de 2015, Único Reglamentario del Sector Comercio, Industria y Turismo.</label>
                        </div>
                        <div class="col-md-10 col-md-offset-2">
                            <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                            <input type="hidden" name="customer_idnumber" value="{{$customer_idnumber}}">
                            <input type="hidden" name="prefix" value="{{$prefix}}">
                            <input type="hidden" name="docnumber" value="{{$docnumber}}">
                            <input type="hidden" name="issuedate" value="{{$issuedate}}">
                            <input type="hidden" name="eventcode" value="4">
                            <button type="submit" class="btn btn-primary">Aceptación Expresa</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('acceptrejectdocument') }}">
                        <div class="col-md-10 col-md-offset-2">
                            <label class="center-block">Documento electrónico mediante el cual el Adquiriente manifiesta que no acepta el documento de conformidad con el artículo 773 del Código de Comercio y en concordancia con el artículo 2.2.2.53.4. del Decreto 1074 de 2015, Único Reglamentario del Sector Comercio, Industria y Turismo. Este documento es para desaveniencias de tipo comercial, dado que el documento sobre el cual manifiesta el desacuerdo fue efectivamente Validado por la DIAN, en el sistema de Validación Previa, Nota: Se debe solicitar una nota contable al emisor.</label>
                        </div>
                        <div class="col-md-10 col-md-offset-2">
                            <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                            <input type="hidden" name="customer_idnumber" value="{{$customer_idnumber}}">
                            <input type="hidden" name="prefix" value="{{$prefix}}">
                            <input type="hidden" name="docnumber" value="{{$docnumber}}">
                            <input type="hidden" name="issuedate" value="{{$issuedate}}">
                            <input type="hidden" name="eventcode" value="2">
                            <label class="center-block">Motivo de Rechazo.</label>
                            <input type="radio" name="rejection_id" value="1" checked> Documento con inconsistencias.
                            <input type="radio" name="rejection_id" value="2"> Mercancía no entregada totalmente.<br>
                            <input type="radio" name="rejection_id" value="3"> Mercancía no entregada parcialmente.
                            <input type="radio" name="rejection_id" value="4"> Servicio no prestado.<br>
                            <button type="submit" class="btn btn-primary">Rechazo/Reclamo de la Factura Electrónica de Venta</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
