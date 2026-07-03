@extends('layouts.app')
@section('title', 'Contact')
@section('content')
<header class="page-header">
    <h2>Documentos del adquiriente</h2>
    <div class="right-wrapper text-end">
        <span class="text-muted">{{ $customer_idnumber }}</span>
    </div>
</header>

<div class="card">
    <div class="card-body">
        @if ($documents->isEmpty())
            <div class="alert alert-info mb-0">No hay documentos para mostrar.</div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Tipo Documento</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Prefijo</th>
                            <th scope="col">Número</th>
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
                                <td>{!! $document->prefix !!}</td>
                                <td>{!! $document->number !!}</td>
                                @php
                                    $allow_public_downloads = env("ALLOW_PUBLIC_DOWNLOAD", true)
                                @endphp
                                @if($allow_public_downloads)
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/'.$document->xml) }}" title="Descargar XML"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/'.$document->pdf) }}" title="Descargar PDF"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/Attachment-'.$document->prefix.$document->number.'.xml') }}" title="Descargar AttachedDocument"><i class="fa fa-download"></i></a></td>
                                    <td><a href="{{ url('/api/download/'.$company_idnumber.'/ZipAttachm-'.$document->prefix.$document->number.'.xml') }}" title="Descargar ZipAtt"><i class="fa fa-download"></i></a></td>
                                    <td>
                                        <form action="{{ route('send-email-customer') }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                                            <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                            <input type="hidden" name="number" value="{{$document->number}}">
                                            <button type="submit" class="btn btn-link p-0" title="Enviar por correo">
                                                <i class="fa fa-envelope"></i>
                                            </button>
                                        </form>
                                    </td>
                                @else
                                    <td>
                                        <form action="{{ route('downloadfile') }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                            <input type="hidden" name="file" value="{{$document->xml}}">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="btn btn-link p-0" title="Descargar XML">
                                                <i class="fa fa-download"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="{{ route('downloadfile') }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                            <input type="hidden" name="file" value="{{$document->pdf}}">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="btn btn-link p-0" title="Descargar PDF">
                                                <i class="fa fa-download"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="{{ route('downloadfile') }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                            <input type="hidden" name="file" value="Attachment-{{$document->prefix}}{{$document->number}}.xml">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="btn btn-link p-0" title="Descargar AttachedDocument">
                                                <i class="fa fa-download"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="{{ route('downloadfile') }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="identification" value="{{$company_idnumber}}">
                                            <input type="hidden" name="file" value="ZipAttachm-{{$document->prefix}}{{$document->number}}.xml">
                                            <input type="hidden" name="type_response" value="false">
                                            <button type="submit" class="btn btn-link p-0" title="Descargar ZipAtt">
                                                <i class="fa fa-download"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="{{ route('send-email-customer') }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="company_idnumber" value="{{$company_idnumber}}">
                                            <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                            <input type="hidden" name="number" value="{{$document->number}}">
                                            <button type="submit" class="btn btn-link p-0" title="Enviar por correo">
                                                <i class="fa fa-envelope"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
