@extends('layouts.app', ['is_seller' => true])
@section('title', 'Contact')
@section('content')
    <div class="container col-xs-8 col-xs-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>Documentos enviados por la empresa - {{$company_idnumber}}.</h2>
            </div>
            @if ($documents->isEmpty())
                <div>No hay documentos para mostrar.</div>
            @else
                <form method="GET" action="{{ url('/oksellerssearch/'.$company_idnumber) }}">
                    <table class="table">
                        <tr>
                            <td>
                                <div>
                                    <select id="searchfield" name="searchfield" class="browser-default custom-select">
                                        <option selected="">Seleccione campo para filtrar.</option>
                                        <option value="1">Factura electrónica de Venta: Numero</option>
                                        <option value="2">Factura electrónica de venta - exportación: Numero</option>
                                        <option value="3">Instrumento electrónico de transmisión - tipo 03: Numero</option>
                                        <option value="4">Nota Credito: Numero</option>
                                        <option value="5">Nota Debito: Numero</option>
                                        <option value="11">Documento Soporte Electrónico: Numero</option>
                                        <option value="6">Fecha</option>
                                        <option value="7">ID Cliente</option>
                                        <option value="8">Prefijo</option>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <input id="searchvalue" type="text" class="form-control" name="searchvalue" autofocus>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <button type="submit" id="btnsearch" name="btnsearch" class="btn btn-primary">
                                        Buscar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
                <table class="table table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col">Tipo Documento</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">ID Cliente</th>
                            <th scope="col">Prefijo</th>
                            <th scope="col">Numero</th>
                            <th scope="col">XML</th>
                            <th scope="col">PDF</th>
                            <th scope="col">Attached Document</th>
                            <th scope="col">ZipAtt</th>
                            <th scope="col">Aceptacion Tacita</th>
                            <th scope="col">Enviar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $document)
                            <tr class="table-light">
                                <td>{!! $document->type_document->name !!}</td>
                                <td>{!! $document->date_issue !!}</td>
                                <td>{!! $document->customer !!}</td>
                                <td>{!! $document->prefix !!}</td>
                                <td>{!! $document->number !!}</td>
                                @php
                                    $allow_public_downloads = env("ALLOW_PUBLIC_DOWNLOAD", true)
                                @endphp
                                @if($allow_public_downloads)
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/'.$document->xml) }}"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/'.$document->pdf) }}"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/Attachment-'.$document->prefix.$document->number.'.xml') }}"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/ZipAttachm-'.$document->prefix.$document->number.'.xml') }}"><i class="fa fa-download"></i></a></td>
                                    <td><form action="{{ route('send-email-customer') }}" method="POST">
                                            <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                                            <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                            <input type="hidden" name="number" value="{{$document->number}}">
                                            <button type="submit" class="fa fa-envelope"></button>
                                        </form>
                                    </td>
                                @else
                                    <td>
                                        <form action="{{ route('downloadfile') }}" method="POST">
                                            <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                            <input type="hidden" name="file" value="{{$document->xml}}">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="fa fa-download"></button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="{{ route('downloadfile') }}" method="POST">
                                            <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                            <input type="hidden" name="file" value="{{$document->pdf}}">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="fa fa-download"></button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="{{ route('downloadfile') }}" method="POST">
                                            <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                            <input type="hidden" name="file" value="Attachment-{{$document->prefix}}{{$document->number}}.xml">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="fa fa-download"></button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="{{ route('downloadfile') }}" method="POST">
                                            <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                            <input type="hidden" name="file" value="ZipAttachm-{{$document->prefix}}{{$document->number}}.xml">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="fa fa-download"></button>
                                        </form>
                                    </td>

                                    <td>
                                        @if($document->aceptacion == 0)
                                            @if($document->type_document_id == 1)
                                                <form method="POST" action="{{ route('acceptrejectdocument') }}">
                                                    <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                                                    <input type="hidden" name="customer_idnumber" value="{{$document->customer}}">
                                                    <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                                    <input type="hidden" name="docnumber" value="{{$document->number}}">
                                                    <input type="hidden" name="issuedate" value="{{$document->date_issue}}">
                                                    <input type="hidden" name="eventcode" value="5">
                                                    <button type="submit" class="fa fa-rss" title="Documento electrónico por el cual cuando el receptor no reclamare al emisor en contra de su contenido, dentro de los tres (3) días hábiles siguientes a la fecha de recepción de la mercancía o del servicio, dicho emisor podra registrar la factura en el sistema RADIAN." class="btn btn-primary"></button>
                                                </form>
                                            @endif
                                        @else
                                            <i class="fa fa-rss" style="color: green"></i>
                                        @endif
                                    </td>

                                    <td>
                                        <button type="button" href="#SendEmail{{$document->cufe}}" role="button" data-toggle="modal" class="fa fa-envelope" class="btn btn-primary"></button>

                                        <!-- Modal Correo Electronico Destino... -->
                                        <div class="modal" tabindex="-1" role="dialog" id="SendEmail{{$document->cufe}}">
                                            <div class="modal-dialog" role="document">
                                                <form method="POST" action="{{ route('send-email-customer') }}">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Enviar correo adquiriente, documento: {{$document->prefix}}{{$document->number}}</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="col-md-10 col-md-offset-2">
                                                                <label class="center-block">Escriba el correo electronico al cual desea enviar el Attached Document del documento electronico, el correo electronico por defecto es el correo registrado en la base de datos del aplicativo.</label>
                                                            </div>
                                                            <div class="col-md-10 col-md-offset-2">
                                                                <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                                                                <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                                                <input type="hidden" name="number" value="{{$document->number}}">
                                                                <div class="form-group">
                                                                    <label for="customerEmail">Correo Electronico</label>
                                                                    <input type="email" value="{{$document->customer_document->email}}" class="form-control" name="customerEmail" id="customerEmail" aria-describedby="emailHelp" placeholder="Escriba el correo electronico...">
                                                                    <small id="emailHelp" class="form-text text-muted">Escriba el correo electronico para enviar el attached document.</small>
                                                                </div>
                                                            </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Enviar Correo</button>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <div>
                        {!! $documents->appends(request()->query())->links() !!}
                    </div>
                </table>
            @endif
        </div>
    </div>
@endsection
