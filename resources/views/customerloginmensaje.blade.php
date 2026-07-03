@extends('layouts.backtemplate')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{$titulo}}</div>

                <div class="panel-body">
                    <form class="form-horizontal">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-2">
                                <label class="center-block">{!! $mensaje !!}</label>
                            </div>
                            <div class="col-md-6 col-md-offset-5">
                                <input type="button" value="Página anterior" class="btn btn-primary" onClick="history.go(-1);">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
