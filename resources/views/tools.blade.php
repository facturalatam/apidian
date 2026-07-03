@extends('layouts.app')
@section('content')
<header class="page-header">
    <div class="d-flex align-items-center">
        <h2 class="h4 mb-0 me-2">Documentación</h2>
        <a href="{{ env('APP_DOCUMENTATION_URL', 'https://manual.facturalatam.com/apidian/instalacion') }}"
           target="_blank"
           rel="noopener noreferrer"
           class="d-inline-flex align-items-center p-0"
           style="color: #0d6efd; background: transparent; border: none; padding: 0; margin-left: .35rem;"
           title="Abrir documentación en nueva pestaña"
           aria-label="Abrir documentación en nueva pestaña">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:block;">
                <path d="M18 13v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
        </a>
    </div>
</header>
<div class="row">

    {{-- <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <section class="card mb-4">
            <div class="card-body bg-secondary">
                <div class="widget-summary">
                    <div class="widget-summary-col widget-summary-col-icon">
                        <div class="summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-message-chatbot"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z" /><path d="M9.5 9h.01" /><path d="M14.5 9h.01" /><path d="M9.5 13a3.5 3.5 0 0 0 5 0" /></svg>
                        </div>
                    </div>
                    <div class="widget-summary-col">
                        <div class="summary">
                            <h4 class="title" style="word-break: normal;">ChatGPT BOT FacturaLatam</h4>
                            <div class="info">
                                <strong class="amount">1281</strong>
                            </div>
                        </div>
                        <div class="summary-footer">
                            <a href="https://chatgpt.com/g/g-6757cbff7bf08191a45c4ee5ff55bc22-facturacion-electronica-dian-colombia" target="_blank" class="text-uppercase">Ir</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div> --}}
    
    {{--
    <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <section class="card mb-4">
            <div class="card-body bg-secondary">
                <div class="widget-summary">
                    <div class="widget-summary-col widget-summary-col-icon">
                        <div class="summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-server"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M3 12m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M7 8l0 .01" /><path d="M7 16l0 .01" /></svg>
                        </div>
                    </div>
                    <div class="widget-summary-col">
                        <div class="summary">
                            <h4 class="title" style="word-break: normal;">Test API Swagger</h4>
                            {{-- <div class="info">
                                <strong class="amount">1281</strong>
                            </div>
                        </div>
                        <div class="summary-footer">
                            <a href="{{route('documentation')}}" target="_blank" class="text-uppercase">Ir</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    --}}
    
    {{-- <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <section class="card mb-4">
            <div class="card-body bg-secondary">
                <div class="widget-summary">
                    <div class="widget-summary-col widget-summary-col-icon">
                        <div class="summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-rocket"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 13a8 8 0 0 1 7 7a6 6 0 0 0 3 -5a9 9 0 0 0 6 -8a3 3 0 0 0 -3 -3a9 9 0 0 0 -8 6a6 6 0 0 0 -5 3" /><path d="M7 14a6 6 0 0 0 -3 6a6 6 0 0 0 6 -3" /><path d="M15 9m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /></svg>
                        </div>
                    </div>
                    <div class="widget-summary-col">
                        <div class="summary">
                            <h4 class="title" style="word-break: normal;">Documentación Postman</h4>
                            <div class="info">
                                <strong class="amount">1281</strong>
                            </div>
                        </div>
                        <div class="summary-footer">
                            <a href="https://documenter.getpostman.com/view/1431398/2sAY4uCido#intro" target="_blank" class="text-uppercase">Ir</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div> --}}
   
    {{--
    <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <section class="card mb-4">
            <div class="card-body bg-secondary">
                <div class="widget-summary">
                    <div class="widget-summary-col widget-summary-col-icon">
                        <div class="summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-brand-android"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 10l0 6" /><path d="M20 10l0 6" /><path d="M7 9h10v8a1 1 0 0 1 -1 1h-8a1 1 0 0 1 -1 -1v-8a5 5 0 0 1 10 0" /><path d="M8 3l1 2" /><path d="M16 3l-1 2" /><path d="M9 18l0 3" /><path d="M15 18l0 3" /></svg>
                        </div>
                    </div>
                    <div class="widget-summary-col">
                        <div class="summary">
                            <h4 class="title" style="word-break: normal;">APP Android</h4>
                            <div class="info">
                                <strong class="amount">1281</strong>
                            </div>
                        </div>
                        <div class="summary-footer">
                            <a href="https://facturalatam.com/apk/apidian.apk" class="text-uppercase">Descargar</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    --}}

    @php
        // URL embebida para la documentación.
        // Configura APP_DOCUMENTATION_URL en tu .env para apuntar a tu página.
        $documentationUrl = env('APP_DOCUMENTATION_URL', 'https://manual.facturalatam.com/apidian/instalacion');
    @endphp

    <div class="col-12">
        <section class="card mb-4">
            <div class="card-body">
                @if(empty($documentationUrl))
                    <div class="alert alert-warning mb-3">
                        Falta configurar <strong>APP_DOCUMENTATION_URL</strong> en el archivo <strong>.env</strong> para cargar la documentación aquí.
                    </div>
                @endif

                <iframe
                    src="{{ $documentationUrl ?: 'about:blank' }}"
                    title="Documentación"
                    style="width: 100%; height: 80vh; border: 0;"
                    allow="clipboard-write; clipboard-read"
                    loading="lazy"
                    referrerpolicy="no-referrer"
                ></iframe>
            </div>
        </section>
    </div>
</div>
@endsection
