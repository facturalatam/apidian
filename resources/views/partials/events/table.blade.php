{{-- Toast de notificación para eventos --}}
<div id="event-toast" style="display:none; position:fixed; top:20px; right:20px; z-index:9999; min-width:320px; max-width:500px;">
    <div id="event-toast-alert" class="alert alert-dismissible fade show mb-0" role="alert" style="box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <strong><i id="event-toast-icon" class="fa fa-exclamation-triangle"></i> <span id="event-toast-title">Aviso:</span></strong>
        <div id="event-toast-msg" style="margin-top:5px;"></div>
        <button type="button" class="close" onclick="document.getElementById('event-toast').style.display='none'">
            <span>&times;</span>
        </button>
    </div>
</div>

{{-- Overlay de carga --}}
<div id="event-loading" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.3); z-index:9998; justify-content:center; align-items:center;">
    <div style="background:white; padding:30px 40px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.3); text-align:center;">
        <i class="fa fa-spinner fa-spin fa-2x"></i>
        <div style="margin-top:10px;">Enviando evento a la DIAN...</div>
    </div>
</div>

@if ($documents->isEmpty())
    <div class="text-muted text-center">No hay documentos para mostrar.</div>
@else
    @if(!(Request::is('company*') || Request::is('companies*')))
    <form method="GET" action="{{ url('/oksellersradiansearch/'.$company_idnumber) }}">
        <table class="table">
            <tr>
                <td>
                    <div>
                        <select id="searchfield" name="searchfield" class="browser-default custom-select">
                            <option selected="">Seleccione campo para filtrar.</option>
                            <option value="1">Factura electrónica de Venta: Numero</option>
                            <option value="2">Nit Emisor</option>
                            <option value="3">Nombre Emisor</option>
                            <option value="4">Acusadas</option>
                            <option value="5">Recibidas</option>
                            <option value="6">Aceptadas</option>
                            <option value="7">Rechazadas</option>
                            <option value="8">Prefijo</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div>
                        <input id="searchvalue" type="text" class="form-control" name="searchvalue" autofocus>
                    </div>
                </td>
                <td>
                    <div>
                        <button type="submit" id="btnsearch" name="btnsearch" class="btn btn-primary">
                            Buscar
                        </button>
                    </div>
                </td>
            </tr>
        </table>
    </form>
    @endif
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th scope="col">Estado Actual</th>
                    <th scope="col">Tipo Documento</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Nit Empresa</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Prefijo</th>
                    <th scope="col">Numero</th>
                    <th scope="col">Impuestos</th>
                    <th scope="col">Vr. Documento</th>
                    <th scope="col">Attached Document</th>
                    <th scope="col">PDF</th>
                    <th scope="col">Acuse Recibo</th>
                    <th scope="col">Recepcion Bienes</th>
                    <th scope="col">Aceptacion Expresa</th>
                    <th scope="col">Rechazo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $document)
                    <tr class="table-light">
                        <td>
                            <form>
                                @if($document->aceptacion == 1)
                                    <i class="fa fa-circle" style="color: green"></i>
                                @else
                                    @if($document->rechazo == 1)
                                        <i class="fa fa-circle" style="color: red"></i>
                                    @else
                                        @if($document->rec_bienes == 1)
                                            <i class="fa fa-circle" style="color: yellow"></i>
                                        @else
                                            @if($document->acu_recibo == 1)
                                                <i class="fa fa-circle" style="color: blue"></i>
                                            @else
                                                <i class="fa fa-circle" style="color: black"></i>
                                            @endif
                                        @endif
                                    @endif
                                @endif
                            </form>
                        </td>
                        <td>{!! $document->type_document->name !!}</td>
                        <td>{!! $document->date_issue !!}</td>
                        <td>{!! $document->identification_number !!}</td>
                        <td>{!! $document->name_seller !!}</td>
                        <td>{!! $document->prefix !!}</td>
                        <td>{!! $document->number !!}</td>
                        <td align="right">{!! number_format($document->total_tax, 2) !!}</td>
                        <td align="right">{!! number_format($document->total, 2) !!}</td>
                        <td><a href="#" onclick="downloadEventFile('{{ url('/api/receivedfile/'.$company_idnumber.'/'.$document->xml) }}'); return false;"><i class="fa fa-download"></i></a></td>
                        <td><a href="#" onclick="downloadEventFile('{{ url('/api/receivedfile/'.$company_idnumber.'/'.$document->pdf) }}'); return false;"><i class="fa fa-download"></i></a></td>
                        <td>
                            @if($document->acu_recibo == 0)
                                @if($document->type_document_id == 1 || $document->type_document_id == 2 || $document->type_document_id == 3)
                                    <form class="event-form" method="POST" action="{{ route('acceptrejectdocument') }}">
                                        <input type="hidden" name="company_idnumber" value="{{$document->identification_number}}">
                                        <input type="hidden" name="company_dv" value="{{$document->dv}}">
                                        <input type="hidden" name="company_name" value="{{$document->name_seller}}">
                                        <input type="hidden" name="customer_idnumber" value="{{$company_idnumber}}">
                                        <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                        <input type="hidden" name="docnumber" value="{{$document->number}}">
                                        <input type="hidden" name="issuedate" value="{{$document->date_issue}}">
                                        <input type="hidden" name="cufe" value="{{$document->cufe}}">
                                        <input type="hidden" name="eventcode" value="1">
                                        <button type="submit" class="fa fa-rss" title="Documento electrónico por el cual el Adquiriente manifiesta que ha recibido la factura electrónica, de conformidad con el artículo 774 del Código de Comercio." class="btn btn-primary"></button>
                                    </form>
                                @endif
                            @else
                                <i class="fa fa-rss" style="color: blue"></i>
                            @endif
                        </td>
                        <td>
                            @if($document->rec_bienes == 0)
                                @if($document->type_document_id == 1 || $document->type_document_id == 2 || $document->type_document_id == 3)
                                    <form class="event-form" method="POST" action="{{ route('acceptrejectdocument') }}">
                                        <input type="hidden" name="company_idnumber" value="{{$document->identification_number}}">
                                        <input type="hidden" name="company_dv" value="{{$document->dv}}">
                                        <input type="hidden" name="company_name" value="{{$document->name_seller}}">
                                        <input type="hidden" name="customer_idnumber" value="{{$company_idnumber}}">
                                        <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                        <input type="hidden" name="docnumber" value="{{$document->number}}">
                                        <input type="hidden" name="issuedate" value="{{$document->date_issue}}">
                                        <input type="hidden" name="cufe" value="{{$document->cufe}}">
                                        <input type="hidden" name="eventcode" value="3">
                                        <button type="submit" class="fa fa-rss" title="Documento electrónico por el cual el Adquiriente informa del recibo de los bienes o servicios adquiridos, de conformidad con el artículo 773 del Código de Comercio y en concordancia con el parágrafo 1 del artículo 2.2.2.53.4. del Decreto 1074 de 2015 Único Reglamentario del Sector Comercio, Industria y Turismo" class="btn btn-primary"></button>
                                    </form>
                                @endif
                            @else
                                <i class="fa fa-rss" style="color: yellow"></i>
                            @endif
                        </td>
                        <td>
                            @if($document->aceptacion == 0)
                                @if($document->type_document_id == 1 || $document->type_document_id == 2 || $document->type_document_id == 3)
                                    <form class="event-form" method="POST" action="{{ route('acceptrejectdocument') }}">
                                        <input type="hidden" name="company_idnumber" value="{{$document->identification_number}}">
                                        <input type="hidden" name="company_dv" value="{{$document->dv}}">
                                        <input type="hidden" name="company_name" value="{{$document->name_seller}}">
                                        <input type="hidden" name="customer_idnumber" value="{{$company_idnumber}}">
                                        <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                        <input type="hidden" name="docnumber" value="{{$document->number}}">
                                        <input type="hidden" name="issuedate" value="{{$document->date_issue}}">
                                        <input type="hidden" name="cufe" value="{{$document->cufe}}">
                                        <input type="hidden" name="eventcode" value="4">
                                        <button type="submit" class="fa fa-rss" title="Documento electrónico por el cual el Adquiriente informa al Emisor que acepta expresamente el Documento Electrónico que origina este tipo de ApplicationResponse de conformidad con el artículo 773 del Código de Comercio y en concordancia con el numeral 1 del artículo 2.2.2.53.4. del Decreto 1074 de 2015, Único Reglamentario del Sector Comercio, Industria y Turismo." class="btn btn-primary"></button>
                                    </form>
                                @endif
                            @else
                                <i class="fa fa-rss" style="color: green"></i>
                            @endif
                        </td>
                        <td>
                            @if($document->rechazo == 0)
                                @if($document->type_document_id == 1 || $document->type_document_id == 2 || $document->type_document_id == 3)
                                    <button type="button" href="#MotivoRechazo{{$document->cufe}}" role="button" data-toggle="modal" class="fa fa-rss" title="Documento electrónico mediante el cual el Adquiriente manifiesta que no acepta el documento de conformidad con el artículo 773 del Código de Comercio y en concordancia con el artículo 2.2.2.53.4. del Decreto 1074 de 2015, Único Reglamentario del Sector Comercio, Industria y Turismo. Este documento es para desaveniencias de tipo comercial, dado que el documento sobre el cual manifiesta el desacuerdo fue efectivamente Validado por la DIAN, en el sistema de Validación Previa, Nota: Se debe solicitar una nota contable al emisor." class="btn btn-primary"></button>

                                    <!-- Modal Motivo de Rechazo -->
                                    <div class="modal" tabindex="-1" role="dialog" id="MotivoRechazo{{$document->cufe}}">
                                        <div class="modal-dialog" role="document">
                                            <form class="event-form" method="POST" action="{{ route('acceptrejectdocument') }}">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Motivo de Rechazo Factura {{$document->number}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="col-md-10 col-md-offset-2">
                                                            <label class="center-block">Documento electrónico mediante el cual el Adquiriente manifiesta que no acepta el documento de conformidad con el artículo 773 del Código de Comercio y en concordancia con el artículo 2.2.2.53.4. del Decreto 1074 de 2015, Único Reglamentario del Sector Comercio, Industria y Turismo. Este documento es para desaveniencias de tipo comercial, dado que el documento sobre el cual manifiesta el desacuerdo fue efectivamente Validado por la DIAN, en el sistema de Validación Previa.</label>
                                                        </div>
                                                        <div class="col-md-10 col-md-offset-2">
                                                            <input type="hidden" name="company_idnumber" value="{{$document->identification_number}}">
                                                            <input type="hidden" name="company_dv" value="{{$document->dv}}">
                                                            <input type="hidden" name="company_name" value="{{$document->name_seller}}">
                                                            <input type="hidden" name="customer_idnumber" value="{{$company_idnumber}}">
                                                            <input type="hidden" name="prefix" value="{{$document->prefix}}">
                                                            <input type="hidden" name="docnumber" value="{{$document->number}}">
                                                            <input type="hidden" name="issuedate" value="{{$document->date_issue}}">
                                                            <input type="hidden" name="cufe" value="{{$document->cufe}}">
                                                            <input type="hidden" name="eventcode" value="2">
                                                            <div>
                                                                <label class="center-block">Motivo de Rechazo.</label>
                                                            </div>
                                                            <div>
                                                                <input type="radio" name="rejection_id" value="1" checked> Documento con inconsistencias.
                                                            </div>
                                                            <div>
                                                                <input type="radio" name="rejection_id" value="2"> Mercancía no entregada totalmente.<br>
                                                            </div>
                                                            <div>
                                                                <input type="radio" name="rejection_id" value="3"> Mercancía no entregada parcialmente.
                                                            </div>
                                                            <div>
                                                                <input type="radio" name="rejection_id" value="4"> Servicio no prestado.<br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Enviar Rechazo</button>
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <i class="fa fa-rss" style="color: red"></i>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <div>
                {!! $documents->appends(request()->query())->links() !!}
            </div>
        </table>
    </div>
@endif

<script>
function showEventToast(message, success) {
    var toast = document.getElementById('event-toast');
    var alert = document.getElementById('event-toast-alert');
    var icon = document.getElementById('event-toast-icon');
    var title = document.getElementById('event-toast-title');
    alert.className = 'alert alert-dismissible fade show mb-0 ' + (success ? 'alert-success' : 'alert-danger');
    icon.className = success ? 'fa fa-check-circle' : 'fa fa-exclamation-triangle';
    title.textContent = success ? 'Éxito:' : 'Aviso:';
    document.getElementById('event-toast-msg').textContent = message;
    toast.style.display = 'block';
    setTimeout(function() { toast.style.display = 'none'; }, success ? 5000 : 10000);
}

function showEventLoading(show) {
    var el = document.getElementById('event-loading');
    el.style.display = show ? 'flex' : 'none';
}

function submitEventForm(form) {
    showEventLoading(true);
    var formData = new FormData(form);
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        showEventLoading(false);
        if(data.success) {
            showEventToast(data.message, true);
            setTimeout(function() { location.reload(); }, 2000);
        } else {
            var msg = data.message || 'Error al procesar el evento.';
            if(data.detail) msg = msg + ' - ' + data.detail;
            showEventToast(msg, false);
        }
    }).catch(function(err) {
        showEventLoading(false);
        showEventToast('Error de conexión al enviar el evento: ' + err.message, false);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var forms = document.querySelectorAll('.event-form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitEventForm(form);
        });
    });
});

function downloadEventFile(url) {
    fetch(url).then(function(response) {
        var ct = response.headers.get('content-type') || '';
        if (ct.indexOf('application/json') !== -1) {
            return response.json().then(function(data) {
                showEventToast(data.message || 'El archivo no fue encontrado.', false);
            });
        }
        if (!response.ok) {
            showEventToast('El archivo solicitado no fue encontrado.', false);
            return;
        }
        var disposition = response.headers.get('content-disposition');
        var filename = 'archivo';
        if (disposition) {
            var match = disposition.match(/filename="?([^"]+)"?/);
            if (match) filename = match[1];
        }
        return response.blob().then(function(blob) {
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(a.href);
        });
    }).catch(function() {
        showEventToast('Error al intentar descargar el archivo.', false);
    });
}
</script>
