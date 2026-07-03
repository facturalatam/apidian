@extends('layouts.app', ['is_owner' => true])
@section('title', 'Contact')
@section('content')
    <div class="container col-xs-8 col-xs-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>Documentos enviados por todas las empresas.</h2>
            </div>
            @if ($documents->isEmpty())
                <div>No hay documentos para mostrar.</div>
            @else
                <form method="GET" action="{{ url('/okownersearch') }}">
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
                                        <option value="7">Nit Empresa</option>
                                        <option value="8">ID Cliente</option>
                                        <option value="9">Prefijo</option>
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
                            <th scope="col">Nit Empresa</th>
                            <th scope="col">ID Cliente</th>
                            <th scope="col">Prefijo</th>
                            <th scope="col">Numero</th>
                            <th scope="col">XML</th>
                            <th scope="col">PDF</th>
                            <th scope="col">AttachedDocument</th>
                            <th scope="col">ZipAtt</th>
                            <th scope="col">Enviar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $document)
                            <tr class="table-light">
                                <td>{!! $document->type_document->name !!}</td>
                                <td>{!! $document->date_issue !!}</td>
                                <td>{!! $document->identification_number !!}</td>
                                <td>{!! $document->customer !!}</td>
                                <td>{!! $document->prefix !!}</td>
                                <td>{!! $document->number !!}</td>
                                @php
                                    $allow_public_downloads = env("ALLOW_PUBLIC_DOWNLOAD", true)
                                @endphp
                                @if($allow_public_downloads)
                                    <td><a href="{{ url('/api/download/'.$document->identification_number.'/'.$document->xml) }}"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$document->identification_number.'/'.$document->pdf) }}"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$document->identification_number.'/Attachment-'.$document->prefix.$document->number.'.xml') }}"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$document->identification_number.'/ZipAttachm-'.$document->prefix.$document->number.'.xml') }}"><i class="fa fa-download"></i></a></td>
                                    <td><form action="{{ route('send-email-customer') }}" method="POST">
                                            <input type="hidden" name="company_idnumber" value="{{$document->identification_number}}">
                                            <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                            <input type="hidden" name="number" value="{{$document->number}}">
                                            <button type="submit" class="fa fa-envelope"></button>
                                        </form>
                                    </td>
                                @else
                                    <td><form action="{{ route('downloadfile') }}" method="POST">
                                            <input type="hidden" name="identification" value="{{$document->identification_number}}">
                                            <input type="hidden" name="file" value="{{$document->xml}}">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="fa fa-download"></button>
                                        </form>
                                    </td>
                                    <td><form action="{{ route('downloadfile') }}" method="POST">
                                            <input type="hidden" name="identification" value="{{$document->identification_number}}">
                                            <input type="hidden" name="file" value="{{$document->pdf}}">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="fa fa-download"></button>
                                        </form>
                                    </td>
                                    <td><form action="{{ route('downloadfile') }}" method="POST">
                                            <input type="hidden" name="identification" value="{{$document->identification_number}}">
                                            <input type="hidden" name="file" value="Attachment-{{$document->prefix}}{{$document->number}}.xml">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="fa fa-download"></button>
                                        </form>
                                    </td>
                                    <td><form action="{{ route('downloadfile') }}" method="POST">
                                            <input type="hidden" name="identification" value="{{$document->identification_number}}">
                                            <input type="hidden" name="file" value="ZipAttachm-{{$document->prefix}}{{$document->number}}.xml">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="fa fa-download"></button>
                                        </form>
                                    </td>
                                    <td><form action="{{ route('send-email-customer') }}" method="POST">
                                            <input type="hidden" name="company_idnumber" value="{{$document->identification_number}}">
                                            <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                            <input type="hidden" name="number" value="{{$document->number}}">
                                            <button type="submit" class="fa fa-envelope"></button>
                                        </form>
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
