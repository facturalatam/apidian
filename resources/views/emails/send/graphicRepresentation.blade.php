@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{$company->name}}
        @endcomponent
    @endslot
    
    # {{$document->type_document->name}} {{$document->prefix}}{{$document->number}}
    
    Hola, {{$document->client->name}} representación grafica de tu {{$document->type_document->name}} {{$document->prefix}}{{$document->number}}.
    
    @slot('subcopy')
        {{-- <h4>Acuse de recibido:</h4>
        <a class="button button-success" href="{{route('document.received', ['cufe' => encrypt($document->cufe), 'state' => encrypt(1)])}}">Aceptar</a>
        <a class="button button-red" href="{{route('document.received', ['cufe' => encrypt($document->cufe), 'state' => encrypt(0)])}}">Rechazar</a>
        
        <br>
        <br> --}}
        Gracias,<br>
        {{$company->name}}
    @endslot
    
    @slot('footer')
        @component('mail::footer')
            © {{date('Y')}} {{$company->name}}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent

