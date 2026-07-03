@extends('layouts.guest')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 auth-login">
            <p class="card-title text-center mb-3">APIDIAN</p>
            <div class="panel panel-default content-login">
                <div class="panel-body w-100">
                    <div>
                        <p class="h4"><strong>Iniciar Sesión</strong></p>
                        <p class="text-muted">Acceso solo para administradores de cuentas</p>
                    </div>
                    <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">Dirección de Correo</label>

                            <div class="col-md-12">
                                <input id="email" type="email" class="form-control" name="email" placeholder="correo@gmail.com" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}" style="margin-bottom: 0;">
                            <label for="password" class="col-md-4 control-label">Contraseña</label>

                            <div class="col-md-12">
                                <input id="password" type="password" class="form-control" name="password" placeholder="********" required>

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6">
                                <div class="checkbox">
                                    <label style="display: flex; align-items: center;">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} style="margin-top: 0px">Recordarme
                                    </label>
                                </div>
                            </div>
                        </div>
                        @if(env('ALLOW_PUBLIC_REGISTER', true))
                            <div class="form-group">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary btn-login">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-login-2" style="margin-right: 5px"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 8v-2a2 2 0 0 1 2 -2h7a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2" /><path d="M3 12h13l-3 -3" /><path d="M13 15l3 -3" /></svg>
                                        Iniciar Sesión
                                    </button>

                                    {{-- <a class="btn btn-link" href="{{ route('password.request') }}">
                                        Olvido su Password?
                                    </a> --}}
                                </div>
                            </div>
                        @endif
                        <p class="text-muted text-center" style="margin: 10px 0 0 0; font-size: 13px"><a href="https://facturalatam.com" target="_blank" rel="noopener">Facturalatam.com</a> Community Edition</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
