@extends('layouts.app', ['is_seller' => true])
@section('title', 'Contact')
@section('content')
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>Eventos RADIAN - Documentos recibidos empresa - {{$company_idnumber}}.</h2>
            </div>
            @include('partials.events.table')
        </div>
    </div>
@endsection
