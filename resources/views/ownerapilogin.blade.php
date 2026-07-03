@extends('layouts.backtemplate')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Ingreso para propietario API</div>

                <div class="panel-body">
                    <form class="form-horizontal" id="Form1" name="Form1" method="GET" action="{{ url('/okownerlogin') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 control-label">Password</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required autofocus>

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="button" id="button1" name="button1" class="btn btn-primary">
                                    Ingresar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener("load", CargaPagina);
    function CargaPagina() {
        var btn = document.getElementById("button1").addEventListener("click", HashPassword);
    }

    function HashPassword() {
        var inputPassword = document.getElementById("password");
        var myform = document.getElementById("Form1");

        var ArrayPassword = inputPassword.value.split("");
        var ArrayPasswordReversed = ArrayPassword.reverse();

        var i;
        for(i=0;i<inputPassword.value.length;i++){
            ArrayPasswordReversed[i] = ArrayPasswordReversed[i].charCodeAt(0) + "-";
        }
        var Password = ArrayPasswordReversed.join("");
        inputPassword.value = Password;
        myform.submit();
    }
</script>
@endsection
