<header class="header">
    {{-- <div class="logo-container">
        <a href="{{route('home')}}" class="logo">
            APIDIAN
        </a>
        <div class="d-md-none toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
        </div>
    </div> --}}
    @if(Request::is('company*'))
                <span class="separator"></span>
                <div id="userbox" class="userbox mx-0 px-0">
                    <div class="profile-info">
                        @inject('model_company', 'App\Company')
                        @php
                            $current = $model_company->where('identification_number', request()->segment(2))->first();
                        @endphp
                        <span class="name text-uppercase">{{ $current->user->name }}</span>
                        <span class="role">{{ request()->segment(2) }}</span>
                    </div>
                </div>
                {{-- <span>
                    {{request()->segment(2)}}
                </span> --}}
            @endif
    @if(isset(Auth::user()->email))
        <div class="header-right d-flex align-items-center mr-4">
            <div id="userbox" class="userbox m-0">
                <a href="#" data-toggle="dropdown">
                    @php
                        $userName = Auth::user()->name;
                        $nameParts = explode(' ', trim($userName));
                        $initials = '';
                        
                        if (count($nameParts) >= 2) {
                            // Si tiene dos o más nombres, tomar la inicial del primero y segundo
                            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
                        } else {
                            // Si tiene un solo nombre, tomar las dos primeras letras
                            $initials = strtoupper(substr($nameParts[0], 0, 2));
                        }
                    @endphp
                    <figure class="profile-picture d-flex">
                        <div class="d-none user-info-dropdown mr-2">
                            <li class="p-2 profile-info m-0 w-100">
                                <span class="name text-left font-weight-bold">{{ Auth::user()->name }}</span>
                                <span class="role text-left">{{ Auth::user()->email }}</span>
                            </li>
                        </div>
                        <div class="border rounded-circle text-center d-flex align-items-center justify-content-center name-initials-container">                            
                            <span class="name-initials">
                                {{ $initials }}
                            </span>
                        </div>
                    </figure>
                </a>
                <div class="dropdown-menu">
                    <ul class="list-unstyled mb-2">                        
                        @if(Auth::user()->id != 1)
                        <li>
                            {{--<a role="menuitem" href="#"><i class="fas fa-user"></i> Perfil</a>--}}
                            <a role="menuitem" href="#" id="default-primary">
                                <input type="text" id="copyToken" value="{{ Auth::user()->api_token }}" style="position:absolute;left:-9999px;">
                                Copiar Token
                            </a>
                        </li>
                        <li class="divider"></li>
                        @endif
                        <li>
                            {{--<a role="menuitem" href="#"><i class="fas fa-user"></i> Perfil</a>--}}
                            <a  class="mt-2" role="menuitem" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-logout"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                                Cerrar Sesión
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    @else
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                <!-- Branding Image -->
                    <span class="navbar-brand guest-brand">
                        {{ config('app.name', 'APIDIAN') }}
                    </span>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>
                </div>
            </div>
        </nav>
    @endif
</header>