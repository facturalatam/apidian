<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>FACTURADOR DIAN PRO</title>

    <!-- Styles -->
    <link href="{{ asset('low/app2.css') }}" rel="stylesheet">
    {{-- theme black --}}
    <link rel="stylesheet" href="{{ asset('porto-light/css/skins/theme-black.css')}}" />
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top" style="display: none">
            <div class="container">
                <div class="navbar-header">

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @guest
                            <li><a href="{{ route('login') }}">Ingresar</a></li>
                            {{-- @if(env('ALLOW_PUBLIC_REGISTER', true))
                                <li><a href="{{ route('register') }}">Registrarse</a></li>
                            @endif --}}
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        <section role="main" class="content-body content-body-auth" id="main-wrapper">
          @yield('content')
        </section>
    </div>
    <!-- Scripts -->
    <script src="{{ asset('low/app2.js') }}"></script>
</body>
</html>
