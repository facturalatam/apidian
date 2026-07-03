@php
$path = explode('/', request()->path());
$path[1] = (array_key_exists(1, $path)> 0)?$path[1]:'';
$path[2] = (array_key_exists(2, $path)> 0)?$path[2]:'';
$path[0] = ($path[0] === '')?'documents':$path[0];
$comp_id = $path[1];
$cust_id = $path[2];
@endphp

<aside id="sidebar-left" class="sidebar-left">
    <div class="sidebar-header">
        <div class="sidebar-title">
            Menu
        </div>
        <div class="sidebar-toggle d-none d-md-block" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
        </div>
    </div>
    <div class="nano">
        <div class="nano-content">
            <nav id="menu" class="nav-main" role="navigation">
                <ul class="nav nav-main">
                    @if(isset(Auth::user()->email))
                        <li class="{{ ($path[0] === 'dashboard')?'nav-active':'' }}">
                            <a class="nav-link" href="{{route('documents_index')}}">
                                <i class="fas fa-receipt" aria-hidden="true"></i>
                                <span>Documentos</span>
                            </a>
                        </li>
                        <li class="{{ ($path[0] === 'dashboard')?'nav-active':'' }}">
                                <a class="nav-link" href="{{route('tax_index')}}">
                                    <i class="fas fa-receipt" aria-hidden="true"></i>
                                    <span>Impuestos</span>
                                </a>
                            </li>
                        <li class="nav-parent {{ in_array($path[0], ['items_ecommerce', 'tags', 'promotions'])?'nav-active nav-expanded':'' }}">
                            <a class="nav-link" href="#">
                                <i class="fas fa-receipt" aria-hidden="true"></i>
                                <span>Empresa</span>
                            </a>
                            <ul class="nav nav-children">
                                <li class="{{ ($path[0] === 'items_ecommerce')?'nav-active':'' }}">
                                    <a class="nav-link" href="{{route('configuration_index')}}">
                                        Lista
                                    </a>
                                </li>
                                <li class="{{ ($path[0] === 'tags')?'nav-active':'' }}">
                                    <a class="nav-link" href="{{route('configuration_admin')}}">
                                        Nuevo
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="{{ ($path[0] === 'dashboard')?'nav-active':'nav-active' }}">
                            <form action="{{ url('/oksellerlogin/'.$comp_id) }}" method="GET">
                                @csrf
                                <a class="nav-link" href="javascript:;" onclick="$('#action-button').click();">
                                    <input type="hidden" name="verificar" value="FALSE"/>
                                    <i class="fas fa-receipt" aria-hidden="true"></i>
                                    <span>Documentos Emitidos</span>
                                </a>
                                <input type="submit" id="action-button" style="display: none;" >
                            </form>
                        </li>
                        <li class="{{ ($path[0] === 'dashboard')?'nav-active':'nav-active' }}">
                            <form action="{{ url('/oksellersdocumentsreception/'.$comp_id) }}" method="POST">
                                @csrf
                                <a class="nav-link" href="javascript:;" onclick="$('#action-button3').click();">
                                    <i class="fas fa-receipt" aria-hidden="true"></i>
                                    <span>Recepcion de documentos</span>
                                </a>
                                <input type="submit" id="action-button3" style="display: none;" >
                            </form>
                        </li>
                        <li class="{{ ($path[0] === 'dashboard')?'nav-active':'nav-active' }}">
                            <form action="{{ url('/oksellersradianevents/'.$comp_id) }}" method="GET">
                                @csrf
                                <a class="nav-link" href="javascript:;" onclick="$('#action-button4').click();">
                                    <i class="fas fa-receipt" aria-hidden="true"></i>
                                    <span>Eventos RADIAN</span>
                                </a>
                                <input type="submit" id="action-button4" style="display: none;" >
                            </form>
                        </li>
                        <li class="{{ ($path[0] === 'dashboard')?'nav-active':'nav-active' }}">
                            <form action="{{ url('/seller-password/'.$comp_id) }}" method="GET">
                                @csrf
                                <a class="nav-link" href="javascript:;" onclick="$('#action-button5').click();">
                                    <i class="fas fa-receipt" aria-hidden="true"></i>
                                    <span>Cambiar Password Empresa</span>
                                </a>
                                <input type="submit" id="action-button5" style="display: none;" >
                            </form>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
        <script>
            // Maintain Scroll Position
            if (typeof localStorage !== 'undefined') {
                if (localStorage.getItem('sidebar-left-position') !== null) {
                    var initialPosition = localStorage.getItem('sidebar-left-position'),
                        sidebarLeft = document.querySelector('#sidebar-left .nano-content');
                    sidebarLeft.scrollTop = initialPosition;
                }
            }
        </script>
    </div>
</aside>
