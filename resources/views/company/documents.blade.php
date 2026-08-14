<header class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2>{{ $company->user->name }} - {{ $company->identification_number }}</h2>
        <br>
        <span class="text-muted">Seleccione el tipo de documento</span>
        @if(isset($company))
            @if(isset($type))
                @include('company.production._environment_badge', ['company' => $company, 'type' => $type])
            @else
                {{-- Listado mixto (todos los tipos): un badge por tipo, porque cada uno
                     tiene su propia columna de ambiente y su propia URL de envio. --}}
                @foreach(['invoice' => 'Facturas', 'support' => 'Soporte'] as $envTabType => $envTabLabel)
                    @include('company.production._environment_badge', ['company' => $company, 'type' => $envTabType, 'label' => $envTabLabel])
                @endforeach
            @endif
        @endif
    </div>
    <div class="mt-auto pb-1">
        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
        @if(isset($type) && $type == 'invoice')
            <button class="btn btn-primary btn-sm text-white ml-2" data-toggle="modal" data-target="#excelModal">
                <i class="fas fa-upload mr-2"></i>Subida Masiva
            </button>
        @endif        
    </div>
</header>



@php
    // dd($resolution_credit_notes);

    // Filtro de documentos (Factura / Soporte comparten esta vista). La barra de
    // búsqueda reutilizable vive en partials/search/bar. Acá solo se arma la lista de
    // campos (los tipos de documento dependen de la vista) y se calculan un par de
    // variables para el mensaje del estado vacío.
    $sp = (isset($type) && $type) ? $type.'_' : '';
    $searchField = request($sp.'search_field', 'number');
    $searchTerm  = request($sp.'search');
    $searchTo    = request($sp.'search_to');
    $hasSearch   = filled($searchTerm) || ($searchField === 'date' && filled($searchTo));
    $totalDocs   = method_exists($documents, 'total') ? $documents->total() : $documents->count();

    $typeIdsByView = ['invoice' => [1,2,3,4,5], 'support' => [11,13]];
    $tIds  = (isset($type) && isset($typeIdsByView[$type])) ? $typeIdsByView[$type] : null;
    $tDocs = $tIds ? \App\TypeDocument::whereIn('id', $tIds)->orderBy('id')->get() : \App\TypeDocument::orderBy('id')->get();

    $documentFields = [
        ['key' => 'number', 'label' => 'Número', 'control' => 'text'],
        ['key' => 'prefix', 'label' => 'Prefijo', 'control' => 'text'],
        ['key' => 'client', 'label' => 'Cliente', 'control' => 'text'],
        ['key' => 'date',   'label' => 'Fecha',  'control' => 'date'],
    ];
    if ($tDocs->isNotEmpty()) {
        $documentFields[] = ['key' => 'type', 'label' => 'Tipo de documento', 'control' => 'select', 'options' => $tDocs->pluck('name', 'id')->toArray()];
    }
    $documentFields[] = ['key' => 'state',   'label' => 'Válido',   'control' => 'select', 'options' => ['si' => 'Sí', 'no' => 'No']];
    $documentFields[] = ['key' => 'ambient', 'label' => 'Ambiente', 'control' => 'select', 'options' => ['hab' => 'Habilitación', 'prod' => 'Producción']];

    // Para el mensaje del estado vacío.
    $fieldLabels = collect($documentFields)->pluck('label', 'key')->toArray();
    if ($searchField === 'type') {
        $searchDisplay = $tDocs->pluck('name', 'id')->toArray()[$searchTerm] ?? $searchTerm;
    } elseif (in_array($searchField, ['state', 'ambient'], true)) {
        $searchDisplay = ['si' => 'Sí', 'no' => 'No', 'hab' => 'Habilitación', 'prod' => 'Producción'][$searchTerm] ?? $searchTerm;
    } elseif ($searchField === 'date') {
        $searchDisplay = (filled($searchTerm) && filled($searchTo)) ? ($searchTerm.' a '.$searchTo) : ($searchTerm ?: $searchTo);
    } else {
        $searchDisplay = $searchTerm;
    }
@endphp

@include('partials.search.bar', [
    'searchPrefix' => $sp,
    'fields'       => $documentFields,
    'default'      => 'number',
    'total'        => $totalDocs,
])

@if($documents->count() == 0)
<div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
    <div class="text-center">
        <div class="alert alert-info d-inline-block px-4 py-3 mb-0" style="font-size: 1.1rem;">
            @if($hasSearch)
                No se encontraron documentos que coincidan con "<strong>{{ $searchDisplay }}</strong>"{{ $searchField ? ' en el campo '.($fieldLabels[$searchField] ?? $searchField) : '' }}.
            @elseif(isset($type))
                @if($type == 'invoice')
                    No hay facturas electrónicas generadas para esta empresa.
                    Configura y empieza a crear facturas.
                @elseif($type == 'support')
                    No hay documentos soporte generados para esta empresa.
                    Configura y empieza a crear documentos soporte.
                @elseif($type == 'pos')
                    No hay documentos equivalentes generados para esta empresa.
                    Configura y empieza a crear documentos equivalentes.
                @else
                    Usted no tiene documentos generados para este tipo.
                @endif
            @else
                Usted no tiene documentos generados para este tipo.
            @endif
        </div>
    </div>
</div>
@else
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>DIAN</th>
                    <th>Descargas</th>
                    <th>Ambiente</th>
                    <th>Válido</th>
                    <th>Fecha</th>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Tipo de Documento</th>
                    <th class="text-right">Impuesto</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documents as $row)
                    <tr class="table-light">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if($row->response_dian)
                                <button type="button" class="btn btn-primary btn-xs modalApiResponse"
                                    data-content="{{ $row->response_dian }}">
                                    Respuesta DIAN
                                </button>
                                <br>
                            @endif
                            @if($row->cufe)
                                <button type="button" class="btn btn-primary btn-xs btn-ver-cufe mt-1"
                                    data-cufe="{{ $row->cufe }}">
                                    Ver CUFE
                                </button>
                                <br>
                                <button type="button" class="btn btn-primary btn-xs makeApiRequest mt-1"
                                    data-id="{{ $row->cufe }}">
                                    Consultar Xml
                                </button>
                                <br>
                            @endif
                            @if(!$row->state_document_id)
                                <button type="button" class="btn btn-primary btn-xs modalChangeState mt-1"
                                    data-id="{{ $row->id }}">
                                    ESTADO
                                </button>
                            @endif
                        </td>
                        <td>
                            <a class="btn btn-success btn-xs text-white"
                                role="button"
                                href="{{ url('/api/view/'.$row->identification_number.'/'.$row->xml) }}" target="_BLANK">
                                XML
                            </a>
                            <a class="btn btn-success btn-xs text-white mt-1"
                                role="button"
                                href="{{ url('/api/view/'.$row->identification_number.'/'.$row->pdf) }}" target="_BLANK">
                                PDF
                            </a>
                        </td>
                        <td>{{ $row->ambient_id === 2 ? 'Habilitación' : 'Producción' }}</td>
                        <td class="text-center">{{ $row->state_document_id ? 'Si' : 'No' }}</td>
                        <td>{{ $row->date_issue }}</td>
                        <td>{{ $row->prefix }}{{ $row->number }}</td>
                        <td>
                            @inject('typeDocuments', 'App\TypeDocumentIdentification')
                            @php
                                $doc_id = $row->client->type_document_identification_id ?? null;
                                $document_type = $typeDocuments->where('id', $doc_id)->first() ?? null;
                                // dd($document_type);
                            @endphp
                            {{-- @if(!$document_type)
                                {{dd($row->client)}}
                            @endif --}}
                            {{ $row->client->name ?? 'Sin nombre' }}<br>
                            {{ $document_type != null ? $document_type->name : '' }} {{ $row->client->identification_number ?? 'sin identificación' }}-{{ $row->client->dv ?? ""}}</td>
                        <td>{{ $row->type_document->name }}</td>
                        <td class="text-right">{{ round($row->total_tax, 2) }}</td>
                        <td class="text-right">{{ round($row->subtotal, 2) }}</td>
                        <td class="text-right">{{ round($row->total, 2) }}</td>
                        <td>
                            @if($row->type_document_id == 1 && $row->response_dian && $resolution_credit_notes && count($resolution_credit_notes) > 0)
                                @php
                                    $isValidResponse = false;
                                    if ($row->response_dian) {
                                        $decodedResponse = json_decode($row->response_dian, true);
                                        $isValidResponse = isset($decodedResponse['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['IsValid'])
                                            && $decodedResponse['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['IsValid'] === 'true';
                                    }
                                @endphp
                                @if($isValidResponse)
                                    <button type="button" class="btn btn-info btn-xs btn-credit-note mt-0"
                                        data-id="{{ $row->id }}"
                                        data-cufe="{{ $row->cufe }}"
                                        data-request-api="{{ $row->request_api }}">
                                        Nota de crédito
                                    </button>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{-- {{ dd($documents) }} --}}
    </div>
    <div class="card-footer d-flex justify-content-center mt-2">
        {{ $documents->appends(request()->query())->links() }}
    </div>
</div>
@endif
<!-- Modal -->
{{-- <div class="modal fade" id="resultModal" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultModalLabel">Consulta de CUFE</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="modalBodyContent"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Respuesta dada por el API</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="modalBodyResponse"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="changeStateModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Cambio de Estado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">Esto cambiará el estado del documento en este listado del API, es importante que se verifique el <strong>CUFE</strong> en la DIAN donde se muestre como ACEPTADO para continuar con este procedimiento.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <form action="{{ route('document.change-state') }}" method="POST">
                    @csrf
                    <input type="hidden" name="document_id" id="verificarInput" value=""/>
                    <button type="submit" class="btn btn-success">Confirmar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excel a JSON -->
<div class="modal fade" id="excelModal" tabindex="-1" role="dialog" aria-labelledby="excelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h5 class="modal-title mr-3" id="excelModalLabel">
                    <i class="fas fa-upload mr-2"></i>Subida Masiva de Facturas
                </h5>
                <a href="{{ asset('xlsx/co-documents-batch.xlsx') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-download mr-1"></i>Descargar Plantilla
                </a>
                <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="excelFile" class="font-weight-bold d-block">
                        <i class="fas fa-upload mr-2"></i>Archivo Excel
                    </label>
                    <input type="file" class="form-control-file" id="excelFile" accept=".xls,.xlsx">
                </div>
                <div class="progress mt-3 d-none" id="progressBar">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"></div>
                </div>
                <div class="card border mt-4">
                    <div class="card-header">
                        <i class="fas fa-list-alt mr-2"></i>Resultado del Procesamiento
                    </div>
                    <div id="apiResults" class="card-body bg-light" style="max-height: 300px; overflow-y: auto; font-family: monospace;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cerrar
                </button>
                <button type="button" class="btn btn-success d-none" id="finishProcess" onclick="location.reload()">
                    <i class="fas fa-check mr-2"></i>Finalizar
                </button>
                <button type="button" class="btn btn-primary" id="processInvoices">
                    <i class="fas fa-cogs mr-2"></i>Procesar Facturas
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Selección de Resolución para Nota de Crédito -->
<div class="modal fade" id="resolutionModal" tabindex="-1" role="dialog" aria-labelledby="resolutionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resolutionModalLabel">
                    <i class="fas fa-file-invoice mr-2"></i>Seleccionar Resolución para Nota de Crédito
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Seleccione la resolución que desea utilizar para generar la nota de crédito.
                </div>
                <div class="list-group" id="resolutionList">
                    @if($resolution_credit_notes)
                        @foreach($resolution_credit_notes as $resolution)
                            <button type="button" class="list-group-item list-group-item-action resolution-item"
                                data-resolution-id="{{ $resolution->id }}"
                                data-resolution-prefix="{{ $resolution->prefix }}"
                                data-resolution-number="{{ $resolution->resolution_number ?? $resolution->resolution ?? '' }}"
                                data-resolution-has-number="{{ ($resolution->resolution_number ?? $resolution->resolution) ? 'true' : 'false' }}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $resolution->prefix }}</h6>
                                    <small>{{ $resolution->type_document->name ?? 'Nota de Crédito' }}</small>
                                </div>
                                <p class="mb-1">
                                    <strong>Resolución:</strong>
                                    @if($resolution->resolution_number ?? $resolution->resolution)
                                        {{ $resolution->resolution_number ?? $resolution->resolution }}
                                    @else
                                        <span class="text-danger">Sin número de resolución</span>
                                    @endif
                                </p>
                                <small>
                                    <strong>Rango:</strong> {{ $resolution->from }} - {{ $resolution->to }}
                                    @if($resolution->date_from && $resolution->date_to)
                                        | <strong>Vigencia:</strong> {{ $resolution->date_from }} - {{ $resolution->date_to }}
                                    @endif
                                </small>
                            </button>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
            </div>
        </div>
    </div>
</div> --}}


@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
$(document).ready(function() {
    // Añadir función para copiar token
    window.copyToken = function() {
        const tokenText = document.getElementById('apiToken').textContent;
        navigator.clipboard.writeText(tokenText).then(() => {
            alert('Token copiado al portapapeles');
        }).catch(err => {
            // error al copiar token
            alert('Error al copiar token');
        });
    };

    // Variable global para almacenar los datos
    window.transformedData = []; // Cambiamos a window.transformedData para acceso global
    window.transformedHealthData = [];

    function tryParseJSON(value) {
        if (!value) return null;
        if (typeof value === 'object') return value;
        try {
            return JSON.parse(value);
        } catch (e) {
            return null;
        }
    }

    function formatDateHealth(dateValue) {
        if (!dateValue) return null;
        if (typeof dateValue === 'string' && dateValue.includes('/')) {
            const parts = dateValue.split('/');
            if (parts.length === 3) return parts[2]+'-'+parts[1].padStart(2,'0')+'-'+parts[0].padStart(2,'0');
        }
        if (!isNaN(dateValue) && typeof dateValue === 'number') {
            const date = new Date((dateValue - 25569) * 86400 * 1000);
            return date.toISOString().split('T')[0];
        }
        if (typeof dateValue === 'string' && dateValue.match(/^\d{4}-\d{2}-\d{2}$/)) return dateValue;
        if (dateValue instanceof Date) return dateValue.toISOString().split('T')[0];
        return dateValue;
    }

    function formatTime(timeValue) {
        if (!timeValue) return null;
        // Strings like '12:32' -> '12:32:00' or '12:32:21' -> same
        if (typeof timeValue === 'string') {
            const t = timeValue.trim();
            if (t.includes(' ')) {
                const parts = t.split(' ');
                const last = parts[parts.length - 1];
                if (/^\d{1,2}:\d{2}(:\d{2})?$/.test(last)) return last.length === 5 ? (last + ':00') : last;
            }
            if (/^\d{1,2}:\d{2}(:\d{2})?$/.test(t)) return t.length === 5 ? (t + ':00') : t;
            if (/^\d{2}:\d{2}:\d{2}\.\d+$/.test(t)) return t.split('.')[0];
            return t;
        }
        // Excel time stored as fraction (number)
        if (!isNaN(timeValue) && typeof timeValue === 'number') {
            const seconds = Math.round((timeValue % 1) * 86400);
            const hh = String(Math.floor(seconds / 3600)).padStart(2, '0');
            const mm = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
            const ss = String(seconds % 60).padStart(2, '0');
            return `${hh}:${mm}:${ss}`;
        }
        if (timeValue instanceof Date) return timeValue.toTimeString().split(' ')[0];
        return String(timeValue);
    }

    function computeLegalMonetaryTotals(invoice) {
        // Suma de line_extension_amount
        let lineExtension = 0;
        let allowanceTotal = 0;
        let chargeTotal = 0;
        let taxTotal = 0;

        if (Array.isArray(invoice.invoice_lines)) {
            invoice.invoice_lines.forEach(line => {
                const lam = Number(line.line_extension_amount || line.lineExtensionAmount || 0);
                lineExtension += lam;

                // line-level allowance_charges
                if (Array.isArray(line.allowance_charges)) {
                    line.allowance_charges.forEach(ac => {
                        const amt = Number(ac.amount || ac.Amount || 0);
                        if (ac.charge_indicator || ac.chargeIndicator) chargeTotal += amt; else allowanceTotal += amt;
                    });
                }

                // line-level taxes
                if (Array.isArray(line.tax_totals)) {
                    line.tax_totals.forEach(t => {
                        taxTotal += Number(t.tax_amount || t.taxAmount || 0);
                    });
                }
            });
        }

        // invoice-level allowance_charges
        if (Array.isArray(invoice.allowance_charges)) {
            invoice.allowance_charges.forEach(ac => {
                const amt = Number(ac.amount || ac.Amount || 0);
                if (ac.charge_indicator || ac.chargeIndicator) chargeTotal += amt; else allowanceTotal += amt;
            });
        }

        // invoice-level tax_totals
        if (Array.isArray(invoice.tax_totals)) {
            invoice.tax_totals.forEach(t => {
                taxTotal += Number(t.tax_amount || t.taxAmount || 0);
            });
        }

        // Preferir la suma de bases imponibles reportadas en las líneas si existen
        let sumTaxableFromLines = 0;
        if (Array.isArray(invoice.invoice_lines)) {
            invoice.invoice_lines.forEach(line => {
                if (Array.isArray(line.tax_totals)) {
                    line.tax_totals.forEach(t => {
                        const ta = Number(t.taxable_amount || t.taxableAmount || 0);
                        sumTaxableFromLines += ta;
                    });
                }
            });
        }

        // taxExclusive: prefer bases reportadas por línea, si no usar lineExtension adjusted por allowances/charges
        const taxExclusive = sumTaxableFromLines > 0 ? sumTaxableFromLines : (lineExtension - allowanceTotal + chargeTotal);
        const taxInclusive = taxExclusive + taxTotal;
        const prePaid = Number((invoice.legal_monetary_totals && invoice.legal_monetary_totals.pre_paid_amount) || invoice.legal_monetary_totals_pre_paid_amount || 0);
        // Payable: DIAN expects total a pagar restando prepagos y descuentos (allowances), y sumando cargos
        const payable = taxInclusive - prePaid - allowanceTotal + chargeTotal;

        return {
            line_extension_amount: Number(lineExtension.toFixed(2)),
            tax_exclusive_amount: Number(Number(taxExclusive).toFixed(2)),
            tax_inclusive_amount: Number(Number(taxInclusive).toFixed(2)),
            allowance_total_amount: Number(allowanceTotal.toFixed(2)),
            charge_total_amount: Number(chargeTotal.toFixed(2)),
            pre_paid_amount: Number(prePaid.toFixed(2)),
            payable_amount: Number(Number(payable).toFixed(2))
        };
    }

    function computeInvoiceTaxTotals(invoice) {
        const map = {};
        if (Array.isArray(invoice.invoice_lines)) {
            invoice.invoice_lines.forEach(line => {
                if (Array.isArray(line.tax_totals)) {
                    line.tax_totals.forEach(t => {
                        const taxId = t.tax_id || t.taxId || t.tax_id || 0;
                        const taxAmount = Number(t.tax_amount || t.taxAmount || 0);
                        const taxableAmount = Number(t.taxable_amount || t.taxableAmount || 0);
                        if (!map[taxId]) map[taxId] = { tax_id: taxId, tax_amount: 0, taxable_amount: 0, percent: t.percent || t.Percent || 0 };
                        map[taxId].tax_amount += taxAmount;
                        map[taxId].taxable_amount += taxableAmount;
                    });
                }
            });
        }
        return Object.values(map).map(t => ({ tax_id: t.tax_id, tax_amount: Number(t.tax_amount.toFixed(2)), taxable_amount: Number(t.taxable_amount.toFixed(2)), percent: String(t.percent || '0') }));
    }

    // Nota: la verificación y corrección se realiza ahora en `normalizeAndFixInvoice`.

    function normalizeAndFixInvoice(invoice) {
        // Asegura consistencia en líneas, tax_totals y totales de la factura
        const round = v => Number(Number(v || 0).toFixed(2));

        // 1) Normalizar cada línea: asegurar line_extension_amount numérico y tax_totals taxable_amount
        let sumLineExtension = 0;
        invoice.invoice_lines = (invoice.invoice_lines || []).map(line => {
            const ln = Object.assign({}, line);
            ln.line_extension_amount = round(Number(ln.line_extension_amount || 0));
            sumLineExtension += ln.line_extension_amount;
            if (Array.isArray(ln.tax_totals) && ln.tax_totals.length > 0) {
                ln.tax_totals = ln.tax_totals.map(t => ({
                    tax_id: t.tax_id || t.taxId || t.tax_id,
                    percent: Number(t.percent || t.Percent || 0),
                    taxable_amount: round(Number(t.taxable_amount || t.taxableAmount || ln.line_extension_amount || 0)),
                    tax_amount: round(Number(t.tax_amount || t.taxAmount || ( (Number(t.percent || 0) / 100) * (Number(t.taxable_amount || ln.line_extension_amount || 0)) ) ))
                }));
            }
            return ln;
        });

        // 2) Agregar/normalizar tax_totals a nivel factura sumando por tax_id
        const taxMap = {};
        (invoice.invoice_lines || []).forEach(line => {
            if (Array.isArray(line.tax_totals)) {
                line.tax_totals.forEach(t => {
                    const id = t.tax_id || 0;
                    if (!taxMap[id]) taxMap[id] = { tax_id: id, tax_amount: 0, taxable_amount: 0, percent: t.percent || 0 };
                    taxMap[id].tax_amount += Number(t.tax_amount || 0);
                    taxMap[id].taxable_amount += Number(t.taxable_amount || 0);
                });
            }
        });
        const aggregatedTaxes = Object.values(taxMap).map(t => ({ tax_id: t.tax_id, tax_amount: round(t.tax_amount), taxable_amount: round(t.taxable_amount), percent: String(t.percent || '0') }));
        invoice.tax_totals = aggregatedTaxes;

        // 3) Calcular allowances y charges a nivel factura
        let allowanceTotal = 0, chargeTotal = 0;
        if (Array.isArray(invoice.allowance_charges)) {
            invoice.allowance_charges.forEach(ac => {
                const amt = Number(ac.amount || 0);
                if (ac.charge_indicator) chargeTotal += amt; else allowanceTotal += amt;
            });
        }
        // También sumar allowances/charges a nivel línea si existen (ya influye en line_extension_amount)
        (invoice.invoice_lines || []).forEach(line => {
            if (Array.isArray(line.allowance_charges)) {
                line.allowance_charges.forEach(ac => {
                    const amt = Number(ac.amount || 0);
                    if (ac.charge_indicator) chargeTotal += amt; else allowanceTotal += amt;
                });
            }
        });

        // 4) TaxExclusive y TaxTotal
        const taxSumFromTaxTotals = round((invoice.tax_totals || []).reduce((s,t)=> s + Number(t.taxable_amount || 0), 0));
        // Si no hay tax_totals reportados, DIAN espera tax_exclusive_amount = 0
        let taxExclusive;
        if (!Array.isArray(invoice.tax_totals) || invoice.tax_totals.length === 0) {
            taxExclusive = 0;
        } else {
            taxExclusive = taxSumFromTaxTotals;
        }
        const taxTotal = round((invoice.tax_totals || []).reduce((s,t)=> s + Number(t.tax_amount || 0), 0));

        // 5) Prepaid
        const prePaid = round(Number((invoice.legal_monetary_totals && invoice.legal_monetary_totals.pre_paid_amount) || invoice.legal_monetary_totals_pre_paid_amount || 0));

        // 6) Construir legal_monetary_totals consistente
        // Si no hay tax_totals, DIAN espera que tax_inclusive refleje la suma de líneas
        let taxInclusive;
        if (!Array.isArray(invoice.tax_totals) || invoice.tax_totals.length === 0) {
            taxInclusive = round(sumLineExtension);
        } else {
            taxInclusive = round(taxExclusive + taxTotal);
        }

        invoice.legal_monetary_totals = {
            line_extension_amount: round(sumLineExtension),
            tax_exclusive_amount: taxExclusive,
            tax_inclusive_amount: taxInclusive,
            allowance_total_amount: round(allowanceTotal),
            charge_total_amount: round(chargeTotal),
            pre_paid_amount: prePaid,
            payable_amount: round(taxInclusive - prePaid - allowanceTotal + chargeTotal)
        };

        // Retornar notas de corrección para visualización
        return {
            corrected: true,
            notes: [`line_extension_sum=${invoice.legal_monetary_totals.line_extension_amount}`, `tax_exclusive=${invoice.legal_monetary_totals.tax_exclusive_amount}`, `tax_total=${taxTotal}`, `payable=${invoice.legal_monetary_totals.payable_amount}`]
        };
    }

    $(document).off('click', '.btn-ver-cufe');
    $(document).on('click', '.btn-ver-cufe', function() {
        var cufe = $(this).data('cufe');
        $('#cufeValue').val(cufe);
        $('#cufeCopiedMsg').addClass('d-none');
        $('#cufeModal').modal('show');
    });

    $('#copyCufeBtn').on('click', function() {
        var cufe = $('#cufeValue').val();
        navigator.clipboard.writeText(cufe).then(function() {
            $('#cufeCopiedMsg').removeClass('d-none');
        }).catch(function() {
            $('#cufeValue').select();
            document.execCommand('copy');
            $('#cufeCopiedMsg').removeClass('d-none');
        });
    });

    $(document).off('click', '.makeApiRequest');
    $(document).on('click', '.makeApiRequest', function() {
        var cufe = $(this).data('id');
        var $button = $(this);
        $button.prop('disabled', true);

        $.ajax({
            url: '{{ url('/company/'.$company->identification_number.'/document/') }}/' + cufe,
            method: 'GET',
            success: function(response) {
                // Mostrar la respuesta en el modal
                $('#modalBodyContent').html(JSON.stringify(response, null, 2));
                $('#resultModal').modal('show');
            },
            error: function(xhr) {
                // Manejar errores
                $('#modalBodyContent').html('Ocurrió un error: ' + xhr.status + ' ' + xhr.statusText);
                $('#resultModal').modal('show');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    $(document).off('click', '.modalApiResponse');
    $(document).on('click', '.modalApiResponse', function() {
        var content = $(this).data('content');
        $('#modalBodyResponse').html(JSON.stringify(content, null, 2));
        $('#responseModal').modal('show');
    });
    $(document).off('click', '.modalChangeState');
    $(document).on('click', '.modalChangeState', function() {
        var id = $(this).data('id');
        $('#verificarInput').val(id);
        $('#changeStateModal').modal('show');
    });

    // Variable global para almacenar los datos del documento actual
    window.currentDocumentData = null;
    window.currentButton = null;

    // Manejar clic en botón "Nota de crédito"
    $(document).off('click', '.btn-credit-note');
    $(document).on('click', '.btn-credit-note', function() {
        var documentId = $(this).data('id');
        var $button = $(this);

        // Buscar la fila correspondiente para obtener los datos del documento
        var $row = $button.closest('tr');
        var documentData = {
            id: documentId,
            prefix: $row.find('td:nth-child(8)').text().match(/^([A-Z]+)/)?.[1] || '', // Extraer prefijo del número
            number: $row.find('td:nth-child(8)').text().match(/\d+/)?.[0] || '', // Extraer número
            cufe: $button.data('cufe'),
            date_issue: $row.find('td:nth-child(6)').text().split(' ')[0], // Extraer solo la fecha sin la hora
            request_api: $button.data('request-api')
        };

        // Guardar los datos globalmente
        window.currentDocumentData = documentData;
        window.currentButton = $button;

        // Mostrar el modal de selección de resolución
        $('#resolutionModal').modal('show');
    });

    // Manejar selección de resolución
    $(document).off('click', '.resolution-item');
    $(document).on('click', '.resolution-item', function() {
        var resolutionId = $(this).data('resolution-id');
        var resolutionPrefix = $(this).data('resolution-prefix');
        var resolutionNumber = $(this).data('resolution-number');
        var hasResolutionNumber = $(this).data('resolution-has-number') === 'true';

        // Datos de resolución seleccionada

        // Verificar si la resolución tiene número (más flexible)
        // if (!resolutionNumber || resolutionNumber === 'undefined') {
        //     new PNotify({
        //         text: 'Esta resolución no tiene configurado el número de resolución. Por favor, agregue este campo en el menú de resoluciones.',
        //         type: 'warning',
        //         addclass: 'notification-warning',
        //         delay: 5000
        //     });
        //     return;
        // }

        // Procesar la nota de crédito con la resolución seleccionada
        if (window.currentDocumentData && window.currentButton) {
            // Deshabilitar botón mientras se procesa
            window.currentButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

            // Deshabilitar todos los botones del modal y mostrar estado de procesando
            $('.resolution-item').prop('disabled', true);
            $('#resolutionModal .btn-secondary').prop('disabled', true);
            $('#resolutionModalLabel').html('<i class="fas fa-spinner fa-spin mr-2"></i>Procesando Nota de Crédito...');

            // Cambiar el contenido del modal para mostrar progreso
            $('#resolutionModal .modal-body').html(`
                <div class="alert alert-info">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Procesando la nota de crédito con la resolución <strong>${resolutionPrefix}</strong>...
                </div>
                <div class="progress mt-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%">
                        Enviando a la DIAN...
                    </div>
                </div>
            `);

            processCreditNote(window.currentDocumentData, window.currentButton, {
                id: resolutionId,
                prefix: resolutionPrefix,
                resolution_number: resolutionNumber
            });
        }
    });

    // Limpiar variables globales cuando se cierre el modal
    $('#resolutionModal').on('hidden.bs.modal', function () {
        window.currentDocumentData = null;
        window.currentButton = null;
    });

    // Función para procesar la nota de crédito
    async function processCreditNote(documentData, $button, selectedResolution) {
        try {
            const token = '{{ $company->user->api_token }}';

            if (!selectedResolution) {
                throw new Error('No se encontró resolución seleccionada para notas de crédito');
            }

            const payloadConsecutive = {
                type_document_id: 4,
                prefix: selectedResolution.prefix
            };

            // 1. Consultar next-consecutive
            const consecutiveResponse = await fetch('/api/ubl2.1/next-consecutive', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(payloadConsecutive)
            });

            if (!consecutiveResponse.ok) {
                throw new Error('Error al obtener el consecutivo');
            }

            const consecutiveData = await consecutiveResponse.json();
            const nextNumber = consecutiveData.number; // Convertir a string para prueba

            // 2. Armar el JSON para la nota de crédito
            // datos request_api

            const originalData = documentData.request_api; // Ya viene como objeto, no parsear
            const now = new Date();
            // Ajustar a zona horaria de Colombia (GMT-5)
            const colombiaTime = new Date(now.getTime() - (5 * 60 * 60 * 1000));
            const currentDate = colombiaTime.toISOString().split('T')[0];
            const currentTime = colombiaTime.toTimeString().split(' ')[0];

            const creditNoteData = {
                billing_reference: {
                    number: documentData.prefix + documentData.number,
                    uuid: documentData.cufe,
                    issue_date: documentData.date_issue
                },
                resolution_number: selectedResolution.resolution_number,
                discrepancyresponsecode: 2,
                discrepancyresponsedescription: "NOTA DE CREDITO GENERADA AUTOMATICAMENTE",
                notes: "NOTA DE CREDITO",
                prefix: selectedResolution.prefix,
                number: nextNumber,
                type_document_id: 4,
                date: currentDate,
                time: currentTime,
                sendmail: originalData.sendmail || false,
                sendmailtome: originalData.sendmailtome || false,
                seze: "2021-2017",
                head_note: originalData.head_note || '',
                foot_note: originalData.foot_note || '',
                customer: originalData.customer,
                allowance_charges: originalData.allowance_charges,
                tax_totals: originalData.tax_totals,
                legal_monetary_totals: originalData.legal_monetary_totals,
                credit_note_lines: originalData.invoice_lines.map(line => ({
                    unit_measure_id: line.unit_measure_id,
                    invoiced_quantity: line.invoiced_quantity,
                    line_extension_amount: line.line_extension_amount,
                    free_of_charge_indicator: line.free_of_charge_indicator,
                    tax_totals: line.tax_totals,
                    description: line.description,
                    notes: line.notes || '',
                    code: line.code,
                    type_item_identification_id: line.type_item_identification_id,
                    price_amount: line.price_amount,
                    base_quantity: line.base_quantity
                }))
            };
            // creditNoteData preparado
            // 3. Enviar la nota de crédito
            const creditNoteResponse = await fetch('/api/ubl2.1/credit-note', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(creditNoteData)
            });

            const creditNoteResult = await creditNoteResponse.json();

            // 4. Verificar el resultado y mostrar notificación
            const statusCode = creditNoteResult.ResponseDian?.Envelope?.Body?.SendBillSyncResponse?.SendBillSyncResult?.StatusCode;

            if (statusCode === "00") {
                // Mostrar resultado exitoso en el modal
                $('#resolutionModal .modal-body').html(`
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>¡Nota de crédito creada exitosamente!</strong>
                    </div>
                    <p>La nota de crédito ha sido enviada correctamente a la DIAN.</p>
                `);
                $('#resolutionModalLabel').html('<i class="fas fa-check-circle mr-2"></i>Nota de Crédito Creada');

                // Cambiar botón de cancelar por cerrar
                $('#resolutionModal .btn-secondary').removeClass('disabled').prop('disabled', false)
                    .html('<i class="fas fa-times mr-2"></i>Cerrar').off('click').on('click', function() {
                        location.reload();
                    });

                new PNotify({
                    text: 'Nota de crédito creada exitosamente',
                    type: 'success',
                    addclass: 'notification-success',
                    delay: 3000
                });
            } else {
                const errorMessage = creditNoteResult.ResponseDian?.Envelope?.Body?.SendBillSyncResponse?.SendBillSyncResult?.ErrorMessage?.string || 'Error desconocido';

                // Mostrar error en el modal
                $('#resolutionModal .modal-body').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle mr-2"></i>
                        <strong>Error al crear la nota de crédito</strong>
                    </div>
                    <p><strong>Mensaje de error:</strong></p>
                    <div class="bg-light p-3 rounded">
                        <small>${errorMessage}</small>
                    </div>
                `);
                $('#resolutionModalLabel').html('<i class="fas fa-times-circle mr-2"></i>Error en Nota de Crédito');

                // Habilitar botón de cerrar
                $('#resolutionModal .btn-secondary').removeClass('disabled').prop('disabled', false)
                    .html('<i class="fas fa-times mr-2"></i>Cerrar');

                new PNotify({
                    text: 'Error al crear la nota de crédito: ' + errorMessage,
                    type: 'error',
                    addclass: 'notification-danger',
                    delay: 5000
                });
            }

        } catch (error) {
            // error procesando nota de crédito

            // Mostrar error en el modal
            $('#resolutionModal .modal-body').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Error al procesar la nota de crédito</strong>
                </div>
                <p><strong>Mensaje de error:</strong></p>
                <div class="bg-light p-3 rounded">
                    <small>${error.message}</small>
                </div>
            `);
            $('#resolutionModalLabel').html('<i class="fas fa-exclamation-triangle mr-2"></i>Error de Conexión');

            // Habilitar botón de cerrar
            $('#resolutionModal .btn-secondary').removeClass('disabled').prop('disabled', false)
                .html('<i class="fas fa-times mr-2"></i>Cerrar');

            new PNotify({
                text: 'Error al procesar la nota de crédito: ' + error.message,
                type: 'error',
                addclass: 'notification-danger',
                delay: 5000
            });
        } finally {
            // Restaurar el botón
            $button.prop('disabled', false).html('Nota de crédito');
        }
    }

    // Manejo de Excel a JSON
    $('#excelFile').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {
                    type: 'array',
                    cellDates: true,
                    dateNF: 'yyyy-mm-dd'
                });
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet, {
                    raw: false,
                    dateNF: 'yyyy-mm-dd'
                });

                if (jsonData.length === 0) {
                    alert('El archivo Excel está vacío');
                    return;
                }

                const transformedData = [];
                const invoiceGroups = {};

                // Normalizar claves de encabezado: trim + lowercase
                const normalizedRows = jsonData.map(r => {
                    const nr = {};
                    Object.keys(r).forEach(k => {
                        const key = k ? k.toString().trim().toLowerCase() : k;
                        nr[key] = r[k];
                    });
                    return nr;
                });

                // Primero agrupamos por número de factura (asegurando que sea string)
                normalizedRows.forEach(row => {
                    const invoiceKey = row.number !== undefined && row.number !== null ? String(row.number).trim() : '';
                    if (!invoiceKey) {
                        console.warn('Fila sin número detectada al procesar Excel:', row);
                        return;
                    }

                    if (!invoiceGroups[invoiceKey]) {
                            invoiceGroups[invoiceKey] = {
                                header: row,
                                lines: [],
                                totalDiscount: 0
                            };
                        }
                        invoiceGroups[invoiceKey].lines.push({
                        unit_measure_id: parseInt(row.line_unit_measure_id),
                        invoiced_quantity: row.line_invoiced_quantity.toString(),
                        line_extension_amount: formatDecimal(row.line_extension_amount),
                        free_of_charge_indicator: false,
                        allowance_charges: [{
                            charge_indicator: false,
                            allowance_charge_reason: "DESCUENTO GENERAL",
                            amount: formatDecimal(row.discount_amount),
                            base_amount: formatDecimal(row.line_extension_amount)
                        }],
                        tax_totals: [{
                            tax_id: parseInt(row.line_tax_tax_id),
                            tax_amount: formatDecimal(row.line_tax_tax_amount),
                            taxable_amount: formatDecimal(row.line_tax_taxable_amount),
                            percent: row.line_tax_percent.toString()
                        }],
                        description: row.line_description,
                        notes: row.line_notes || "",
                        code: row.line_code !== undefined && row.line_code !== null ? String(row.line_code) : null,
                        type_item_identification_id: parseInt(row.line_type_item_identification_id),
                        price_amount: formatDecimal(row.line_price_amount),
                        base_quantity: row.line_base_quantity.toString()
                    });
                });

                // Luego creamos las facturas con todas sus líneas
                Object.entries(invoiceGroups).forEach(([number, data]) => {
                    const row = data.header;
                    const now = new Date();
                    const currentTime = now.toTimeString().split(' ')[0];

                    // Calcular totales sumando todas las líneas
                    const totals = data.lines.reduce((acc, line) => {
                        const lineAmount = parseFloat(line.line_extension_amount);
                        const taxAmount = parseFloat(line.tax_totals[0].tax_amount);

                        return {
                            line_extension_amount: acc.line_extension_amount + lineAmount,
                            tax_amount: acc.tax_amount + taxAmount,
                            payable_amount: acc.payable_amount + lineAmount + taxAmount
                        };
                    }, { line_extension_amount: 0, tax_amount: 0, payable_amount: 0 });

                    // Aplicar el descuento general
                    const totalDiscount = data.totalDiscount || 0;
                    const finalPayableAmount = totals.payable_amount - totalDiscount;

                    transformedData.push({
                        number: parseInt(row.number),
                        type_document_id: parseInt(row.type_document_id),
                        date: formatDate(row.date),
                        time: currentTime,
                        resolution_number: row.resolution_number,
                        prefix: row.prefix,
                        notes: row.notes || "",
                        disable_confirmation_text: true,
                        establishment_name: row.establishment_name,
                        establishment_address: row.establishment_address,
                        establishment_phone: row.establishment_phone ? row.establishment_phone.toString() : "",
                        establishment_municipality: parseInt(row.establishment_municipality),
                        establishment_email: row.establishment_email,
                        sendmail: true,
                        sendmailtome: true,
                        seze: "2021-2017",
                        head_note: row.head_note || "",
                        foot_note: row.foot_note || "",
                        customer: {
                            identification_number: parseInt(row.customer_identification_number),
                            dv: parseInt(row.customer_dv),
                            name: row.customer_name,
                            phone: row.customer_phone ? row.customer_phone.toString() : "",
                            address: row.customer_address,
                            email: row.customer_email,
                            merchant_registration: row.customer_merchant_registration || "0000000-00",
                            type_document_identification_id: parseInt(row.customer_type_document_identification_id),
                            type_organization_id: parseInt(row.customer_type_organization_id),
                            type_liability_id: parseInt(row.customer_type_liability_id),
                            municipality_id: parseInt(row.customer_municipality_id),
                            type_regime_id: parseInt(row.customer_type_regime_id)
                        },
                        payment_form: {
                            payment_form_id: parseInt(row.payment_form_id),
                            payment_method_id: 10,
                            payment_due_date: formatDate(row.payment_due_date),
                            duration_measure: row.duration_measure ? row.duration_measure.toString() : "0"
                        },
                        legal_monetary_totals: {
                            line_extension_amount: formatDecimal(totals.line_extension_amount),
                            tax_exclusive_amount: formatDecimal(totals.line_extension_amount),
                            tax_inclusive_amount: formatDecimal(totals.payable_amount),
                            allowance_total_amount: formatDecimal(totalDiscount),
                            payable_amount: formatDecimal(finalPayableAmount)
                        },
                        tax_totals: [{
                            tax_id: 1,
                            tax_amount: formatDecimal(totals.tax_amount),
                            percent: "19",
                            taxable_amount: formatDecimal(totals.line_extension_amount)
                        }],
                        invoice_lines: data.lines
                    });
                });

                // Asignar los datos transformados a la variable
                window.transformedData = transformedData;

                $('#apiResults').text('Datos preparados. ' + window.transformedData.length + ' facturas listas para procesar.');
                // facturas preparadas: window.transformedData.length
            } catch (error) {
                // error procesando el Excel
                $('#apiResults').text('Error: ' + error.message);
                alert('Error procesando el archivo Excel: ' + error.message);
            }
        };

        reader.onerror = function(ex) {
            // error leyendo el archivo
            $('#apiResults').text('Error leyendo el archivo');
            alert('Error leyendo el archivo Excel');
        };

        reader.readAsArrayBuffer(file);
    });

    // Manejo de Excel para Facturas de Salud
    $('#excelFileHealth').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(ev) {
            try {
                const data = new Uint8Array(ev.target.result);
                const workbook = XLSX.read(data, {type: 'array', cellDates: true, dateNF: 'yyyy-mm-dd'});
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet, {defval: null});

                if (jsonData.length === 0) {
                    alert('El archivo Excel está vacío');
                    return;
                }

                // Normalizar claves
                const normalizedRows = jsonData.map(r => {
                    const nr = {};
                    Object.keys(r).forEach(k => {
                        const key = k ? k.toString().trim().toLowerCase() : k;
                        nr[key] = r[k];
                    });
                    return nr;
                });

                // Agrupar filas por número de factura y concatenar sus invoice_lines
                (function(){
                    const invoiceGroups = {};

                    normalizedRows.forEach((row, idx) => {
                        const invoiceKey = row.number !== undefined && row.number !== null ? String(row.number).trim() : '';
                        if (!invoiceKey) return;

                        const invoice_lines_json = tryParseJSON(row.invoice_lines) || null;
                        const linesToAdd = [];

                        if (Array.isArray(invoice_lines_json) && invoice_lines_json.length > 0) {
                            invoice_lines_json.forEach(l => {
                                const ln = Object.assign({}, l);
                                if (ln.code !== undefined && ln.code !== null) ln.code = String(ln.code);
                                linesToAdd.push(ln);
                            });
                        } else if (row.invoice_lines_description || row.invoice_lines_code) {
                            const line = {
                                unit_measure_id: row.invoice_lines_unit_measure_id ? parseInt(row.invoice_lines_unit_measure_id) : null,
                                invoiced_quantity: row.invoice_lines_invoiced_quantity ? Number(row.invoice_lines_invoiced_quantity) : null,
                                line_extension_amount: row.invoice_lines_line_extension_amount ? Number(row.invoice_lines_line_extension_amount) : 0,
                                free_of_charge_indicator: (String(row.invoice_lines_free_of_charge_indicator || '').toLowerCase() === 'true') || false,
                                description: row.invoice_lines_description || null,
                                notes: row.invoice_lines_notes || null,
                                code: row.invoice_lines_code !== undefined && row.invoice_lines_code !== null ? String(row.invoice_lines_code) : null,
                                type_item_identification_id: row.invoice_lines_type_item_identification_id ? parseInt(row.invoice_lines_type_item_identification_id) : 4,
                                price_amount: row.invoice_lines_price_amount ? Number(row.invoice_lines_price_amount) : null,
                                base_quantity: row.invoice_lines_base_quantity ? Number(row.invoice_lines_base_quantity) : null
                            };
                            if (row.invoice_lines_allowance_charges_amount || row.invoice_lines_allowance_charges_base_amount) {
                                line.allowance_charges = [{
                                    charge_indicator: (String(row.invoice_lines_allowance_charges_charge_indicator || '').toLowerCase() === 'true') || false,
                                    allowance_charge_reason: row.invoice_lines_allowance_charges_allowance_charge_reason || null,
                                    amount: row.invoice_lines_allowance_charges_amount ? Number(row.invoice_lines_allowance_charges_amount) : 0,
                                    base_amount: row.invoice_lines_allowance_charges_base_amount ? Number(row.invoice_lines_allowance_charges_base_amount) : 0
                                }];
                            }
                            if (row.invoice_lines_tax_totals_tax_id || row.invoice_lines_tax_totals_tax_amount) {
                                line.tax_totals = [{
                                    tax_id: row.invoice_lines_tax_totals_tax_id || null,
                                    tax_amount: row.invoice_lines_tax_totals_tax_amount ? Number(row.invoice_lines_tax_totals_tax_amount) : 0,
                                    taxable_amount: row.invoice_lines_tax_totals_taxable_amount ? Number(row.invoice_lines_tax_totals_taxable_amount) : Number(row.invoice_lines_line_extension_amount || 0),
                                    percent: row.invoice_lines_tax_totals_percent ? Number(row.invoice_lines_tax_totals_percent) : null
                                }];
                            }
                            linesToAdd.push(line);
                        }

                        if (!invoiceGroups[invoiceKey]) invoiceGroups[invoiceKey] = { header: row, lines: [], rowIndexes: [] };
                        invoiceGroups[invoiceKey].lines = invoiceGroups[invoiceKey].lines.concat(linesToAdd);
                        invoiceGroups[invoiceKey].rowIndexes.push(idx+1);
                    });

                    const transformed = Object.entries(invoiceGroups).map(([number, data]) => {
                        const row = data.header;
                        const totals = data.lines.reduce((acc, line) => {
                            const lineAmt = Number(line.line_extension_amount || 0);
                            const taxAmt = Array.isArray(line.tax_totals) && line.tax_totals.length ? Number(line.tax_totals[0].tax_amount || 0) : 0;
                            acc.line_extension_amount += lineAmt;
                            acc.tax_amount += taxAmt;
                            acc.payable_amount += lineAmt + taxAmt;
                            return acc;
                        }, { line_extension_amount: 0, tax_amount: 0, payable_amount: 0 });

                        const lmt_json = tryParseJSON(row.legal_monetary_totals) || {};
                        const lmt = Object.assign({}, lmt_json);
                        if (!Object.keys(lmt).length) {
                            lmt.line_extension_amount = Number(totals.line_extension_amount.toFixed(2));
                            lmt.tax_exclusive_amount = Number(totals.line_extension_amount.toFixed(2));
                            lmt.tax_inclusive_amount = Number(totals.payable_amount.toFixed(2));
                            lmt.allowance_total_amount = lmt.allowance_total_amount ? Number(lmt.allowance_total_amount) : 0;
                            lmt.payable_amount = Number(totals.payable_amount.toFixed(2));
                        }

                        const health_fields_json = tryParseJSON(row.health_fields) || {};
                        const hf = Object.assign({}, health_fields_json);
                        if (row.health_fields_invoice_period_start_date) hf.invoice_period_start_date = row.health_fields_invoice_period_start_date;
                        if (row.health_fields_invoice_period_end_date) hf.invoice_period_end_date = row.health_fields_invoice_period_end_date;
                        if (row.health_fields_health_type_operation_id) hf.health_type_operation_id = row.health_fields_health_type_operation_id;
                        if (row.health_fields_print_users_info_to_pdf) hf.print_users_info_to_pdf = row.health_fields_print_users_info_to_pdf;

                        let users_info = hf.users_info || [];
                        const hasPrefixedUser = (
                            row.health_fields_users_info_identification_number ||
                            row.health_fields_users_info_surname ||
                            row.health_fields_users_info_first_name
                        );
                        if (hasPrefixedUser) {
                            const userObj = {
                                provider_code: row.health_fields_users_info_provider_code || null,
                                health_type_document_identification_id: row.health_fields_users_info_health_type_document_identification_id ? parseInt(row.health_fields_users_info_health_type_document_identification_id) : null,
                                identification_number: row.health_fields_users_info_identification_number || null,
                                surname: row.health_fields_users_info_surname || null,
                                second_surname: row.health_fields_users_info_second_surname || null,
                                first_name: row.health_fields_users_info_first_name || null,
                                middle_name: row.health_fields_users_info_middle_name || null,
                                health_type_user_id: row.health_fields_users_info_health_type_user_id ? parseInt(row.health_fields_users_info_health_type_user_id) : null,
                                health_contracting_payment_method_id: row.health_fields_users_info_health_contracting_payment_method_id ? parseInt(row.health_fields_users_info_health_contracting_payment_method_id) : null,
                                health_coverage_id: row.health_fields_users_info_health_coverage_id ? parseInt(row.health_fields_users_info_health_coverage_id) : null,
                                autorization_numbers: row.health_fields_users_info_autorization_numbers || null,
                                mipres: row.health_fields_users_info_mipres || null,
                                mipres_delivery: row.health_fields_users_info_mipres_delivery || null,
                                contract_number: row.health_fields_users_info_contract_number || null,
                                policy_number: row.health_fields_users_info_policy_number || null,
                                co_payment: row.health_fields_users_info_co_payment || null,
                                moderating_fee: row.health_fields_users_info_moderating_fee || null,
                                recovery_fee: row.health_fields_users_info_recovery_fee || null,
                                shared_payment: row.health_fields_users_info_shared_payment || null
                            };
                            if (Array.isArray(users_info) && users_info.length > 0) users_info = users_info.concat([userObj]); else users_info = [userObj];
                        }

                        const payment_form_json = tryParseJSON(row.payment_form) || {};
                        const payment_form = Object.assign({}, payment_form_json);
                        if (row.payment_form_payment_form_id) payment_form.payment_form_id = row.payment_form_payment_form_id ? parseInt(row.payment_form_payment_form_id) : null;
                        if (row.payment_form_payment_method_id) payment_form.payment_method_id = row.payment_form_payment_method_id ? parseInt(row.payment_form_payment_method_id) : null;
                        if (row.payment_form_payment_due_date) payment_form.payment_due_date = formatDateHealth(row.payment_form_payment_due_date);
                        if (row.payment_form_duration_measure) payment_form.duration_measure = row.payment_form_duration_measure;

                        const customer = tryParseJSON(row.customer) || {
                            identification_number: row.customer_identification_number ? String(row.customer_identification_number).replace(/\D/g, '') : null,
                            dv: row.customer_dv ? String(row.customer_dv).replace(/\D/g, '') : null,
                            name: row.customer_name ? String(row.customer_name).trim().toUpperCase() : null,
                            phone: row.customer_phone ? String(row.customer_phone).replace(/\D/g, '') : null,
                            address: row.customer_address || null,
                            email: row.customer_email || null,
                            merchant_registration: row.merchant_registration || row.customer_merchant_registration || null,
                            type_document_identification_id: row.customer_type_document_identification_id ? parseInt(row.customer_type_document_identification_id) : null,
                            type_organization_id: row.customer_type_organization_id ? parseInt(row.customer_type_organization_id) : null,
                            type_liability_id: row.customer_type_liability_id ? parseInt(row.customer_type_liability_id) : null,
                            municipality_id: row.customer_municipality_id ? parseInt(row.customer_municipality_id) : null,
                            type_regime_id: row.customer_type_regime_id ? parseInt(row.customer_type_regime_id) : null
                        };

                        return {
                            number: Number(number),
                            type_document_id: row.type_document_id ? parseInt(row.type_document_id) : null,
                            date: formatDateHealth(row.date),
                            time: formatTime(row.time) || (new Date(new Date().getTime() - (5 * 60 * 60 * 1000))).toTimeString().split(' ')[0],
                            resolution_number: row.resolution_number || row.resolution,
                            prefix: row.prefix,
                            establishment_name: row.establishment_name,
                            establishment_address: row.establishment_address,
                            establishment_phone: row.establishment_phone ? String(row.establishment_phone) : '',
                            establishment_municipality: row.establishment_municipality ? parseInt(row.establishment_municipality) : null,
                            establishment_email: row.establishment_email,
                            sendmail: (String(row.sendmail || '').toLowerCase() === 'true') || false,
                            seze: row.seze || null,
                            health_fields: {
                                invoice_period_start_date: formatDateHealth(hf.invoice_period_start_date || hf.invoice_period_start_date),
                                invoice_period_end_date: formatDateHealth(hf.invoice_period_end_date || hf.invoice_period_end_date),
                                health_type_operation_id: hf.health_type_operation_id ? parseInt(hf.health_type_operation_id) : (row.health_type_operation_id ? parseInt(row.health_type_operation_id) : null),
                                print_users_info_to_pdf: (String(hf.print_users_info_to_pdf || '').toLowerCase() === 'true') || false,
                                users_info: users_info
                            },
                            customer: customer,
                            payment_form: payment_form,
                            prepaid_payment: tryParseJSON(row.prepaid_payment) || null,
                            allowance_charges: tryParseJSON(row.allowance_charges) || [],
                            legal_monetary_totals: lmt,
                            tax_totals: tryParseJSON(row.tax_totals) || [{ tax_id: 1, tax_amount: Number(totals.tax_amount.toFixed(2)), percent: '19', taxable_amount: Number(totals.line_extension_amount.toFixed(2)) }],
                            invoice_lines: data.lines,
                            raw: row,
                            rowIndexes: data.rowIndexes
                        };
                    });

                    window.transformedHealthData = transformed;
                })();

                $('#apiResultsHealth').html('<div class="alert alert-info">'+window.transformedHealthData.length+' facturas de salud preparadas para procesar (agrupadas por número).</div>');
                // facturas salud preparadas
            } catch (err) {
                // error procesando Excel salud
                $('#apiResultsHealth').html('<div class="alert alert-danger">Error leyendo el archivo</div>');
            }
        };

        reader.onerror = function(ex) {
            // error leyendo el archivo
            $('#apiResultsHealth').html('<div class="alert alert-danger">Error leyendo el archivo</div>');
        };

        reader.readAsArrayBuffer(file);
    });

    // Procesamiento de facturas
    // Evitar bindings duplicados si el script se inicializa varias veces
    $(document).off('click', '#processInvoices');
    $(document).on('click', '#processInvoices', async function() {
        if (window.processingInvoices) {
            console.warn('Procesamiento ya en curso, clic ignorado.');
            return;
        }
        window.processingInvoices = true;

        // Determinar pestaña activa: si está la pestaña de salud activa, procesar salud
        const isHealthActive = $('#health').hasClass('show') || $('#health').hasClass('active');
        if (isHealthActive) {
            if (!window.transformedHealthData || window.transformedHealthData.length === 0) {
                alert('No hay facturas de salud para procesar');
                window.processingInvoices = false;
                return;
            }
        } else {
            if (!window.transformedData || window.transformedData.length === 0) {
                alert('No hay facturas para procesar');
                window.processingInvoices = false;
                return;
            }
        }

        const $processButton = $(this);
        if (isHealthActive) {
            const $finishButton = $('#finishProcessHealth');
            const progressBar = $('#progressBarHealth');
            const progressBarInner = progressBar.find('.progress-bar');
            const resultsContainer = $('#apiResultsHealth');

            // Deshabilitar botón procesar
            $processButton.prop('disabled', true);
            progressBar.removeClass('d-none');
            let results = [];

            const total = window.transformedHealthData.length;
            let completed = 0;

            const token = '{{ $company->user->api_token }}';
            if (!token) {
                alert('No se encontró el token de autenticación');
                window.processingInvoices = false;
                return;
            }

            const sendHealthInvoice = async (invoice) => {
                try {

                    // Normalizar y corregir invoice antes de enviar
                    const fixResult = normalizeAndFixInvoice(invoice);
                    const localCorrectionNotes = fixResult.notes || [];
                    // Payload final preparado

                    const response = await fetch('/api/ubl2.1/invoice', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token
                        },
                        body: JSON.stringify(invoice)
                    });

                    const responseData = await response.json();
                    const sendResult = responseData.ResponseDian?.Envelope?.Body?.SendBillSyncResponse?.SendBillSyncResult;
                    const statusCode = sendResult?.StatusCode;
                    const isValid = sendResult?.IsValid;
                    let dianErrors = [];
                    if (sendResult?.ErrorMessage) {
                        const s = sendResult.ErrorMessage.string;
                        if (Array.isArray(s)) dianErrors = dianErrors.concat(s);
                        else if (s) dianErrors.push(s);
                    }
                    const isSuccess = (statusCode === "00" && isValid === 'true');

                    // Construir detalles de error para mostrar en la vista
                    let details = [];
                    if (responseData.error) details.push(responseData.error);
                    if (responseData.message) details.push(responseData.message);
                    if (responseData.errors) {
                        if (Array.isArray(responseData.errors)) details = details.concat(responseData.errors);
                        else if (typeof responseData.errors === 'object') details = details.concat(Object.values(responseData.errors).flat());
                        else details.push(responseData.errors);
                    }
                    if (dianErrors.length) details = details.concat(dianErrors);
                    if (localCorrectionNotes.length) details = localCorrectionNotes.concat(details);

                    const elId = 'result-health-' + (invoice.customer?.identification_number || '') + '-' + (invoice.number || invoice.rowIndex || '');
                    const alertClass = isSuccess ? 'alert-success' : 'alert-danger';
                    const icon = isSuccess ? 'check-circle' : 'times-circle';
                    const acceptedText = isSuccess ? ' (ACEPTADO)' : ' (RECHAZADO)';
                    let messageHtml = '';
                    if (isSuccess) {
                        messageHtml = `<div class="text-success"><strong>¡Enviado correctamente!${acceptedText}</strong><br>${responseData.message || ''}</div>`;
                    } else {
                        // mostrar errores y el payload enviado para depuración
                        const payloadHtml = `<details><summary>Mostrar payload enviado</summary><pre style="white-space:pre-wrap; max-height:300px; overflow:auto;">${escapeHtml(JSON.stringify(invoice, null, 2))}</pre></details>`;
                        messageHtml = `<div class="text-danger"><strong>Error en el envío${acceptedText}:</strong><br>${details.join('<br>')}<br>${payloadHtml}</div>`;
                    }
                    const html = `
                        <div id="${elId}" class="alert ${alertClass} mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-${icon} mr-2"></i>
                                <h6 class="font-weight-bold mb-0">Factura Salud ${invoice.number || invoice.rowIndex}</h6>
                            </div>
                            <div class="mt-2">${messageHtml}</div>
                        </div>
                    `;

                    if ($('#' + elId).length) {
                        $('#' + elId).replaceWith(html);
                    } else {
                        resultsContainer.append(html);
                    }

                    completed++;
                    const progress = ((completed) / total * 100).toFixed(2);
                    progressBarInner.css('width', progress + '%').text(progress + '%');

                    return { invoice: invoice.number, status: statusCode || (responseData.success ? 'ok' : 'error'), success: isSuccess };
                } catch (error) {
                    resultsContainer.append(`
                        <div class="alert alert-danger mb-2">
                            <strong>Error en Fila ${invoice.rowIndex}:</strong><br>
                            ${error.message}
                        </div>
                    `);
                    completed++;
                    const progress = ((completed) / total * 100).toFixed(2);
                    progressBarInner.css('width', progress + '%').text(progress + '%');
                    return { invoice: invoice.number, status: 'error', error: error.message, success: false };
                }
            };

            const promises = window.transformedHealthData.map(inv => sendHealthInvoice(inv));
            const settled = await Promise.all(promises);

            settled.forEach(r => results.push(r));

            progressBar.addClass('d-none');
            $finishButton.removeClass('d-none');
            // Ocultar el botón procesar y mostrar el botón finalizar
            $processButton.addClass('d-none');
            $('#finishProcess').removeClass('d-none');
            window.processingInvoices = false;

            resultsContainer.prepend(`
                <div class="alert alert-info">
                    <i class="fas fa-check-circle mr-2"></i>
                    <strong>Proceso Completado:</strong> Se procesaron ${results.length} facturas de salud.
                    <br>
                    <small>Haga clic en "Finalizar" para actualizar la lista de documentos.</small>
                </div>
            `);

            return;
        } else {
            // comportamiento original para facturas de ventas
            const $finishButton = $('#finishProcess');
            const progressBar = $('#progressBar');
            const progressBarInner = progressBar.find('.progress-bar');
            const resultsContainer = $('#apiResults');

            // Deshabilitar botón procesar
            $processButton.prop('disabled', true);
            progressBar.removeClass('d-none');
            let results = [];

            // Envío concurrente: crear promesas por cada factura y ejecutarlas en paralelo.
            const total = window.transformedData.length;
            let completed = 0;

            const token = '{{ $company->user->api_token }}';
            if (!token) {
                alert('No se encontró el token de autenticación');
                window.processingInvoices = false;
                return;
            }

            const sendInvoice = async (invoice) => {
                try {
                    // Normalizar y corregir invoice antes de enviar
                    const fixResult = normalizeAndFixInvoice(invoice);
                    const localCorrectionNotes = fixResult.notes || [];
                    // Payload final preparado
                    const response = await fetch('/api/ubl2.1/invoice', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token
                        },
                        body: JSON.stringify(invoice)
                    });

                    const responseData = await response.json();
                    const sendResult = responseData.ResponseDian?.Envelope?.Body?.SendBillSyncResponse?.SendBillSyncResult;
                    const statusCode = sendResult?.StatusCode;
                    const isValid = sendResult?.IsValid;
                    let dianErrors = [];
                    if (sendResult?.ErrorMessage) {
                        const s = sendResult.ErrorMessage.string;
                        if (Array.isArray(s)) dianErrors = dianErrors.concat(s);
                        else if (s) dianErrors.push(s);
                    }
                    const isSuccess = (statusCode === "00" && isValid === 'true');

                    // Construir detalles de error para mostrar en la vista
                    let details = [];
                    if (responseData.error) details.push(responseData.error);
                    if (responseData.message) details.push(responseData.message);
                    if (responseData.errors) {
                        if (Array.isArray(responseData.errors)) details = details.concat(responseData.errors);
                        else if (typeof responseData.errors === 'object') details = details.concat(Object.values(responseData.errors).flat());
                        else details.push(responseData.errors);
                    }
                    if (dianErrors.length) details = details.concat(dianErrors);
                    if (localCorrectionNotes.length) details = localCorrectionNotes.concat(details);

                    const resultObj = {
                        invoice: invoice.number,
                        status: statusCode,
                        message: responseData.message,
                        error: details.join(' | '),
                        success: isSuccess
                    };

                    // Mostrar resultado individual para evitar mezcla
                    const elId = 'result-invoice-' + (invoice.prefix || '') + '-' + invoice.number;
                    const alertClass = isSuccess ? 'alert-success' : 'alert-danger';
                    const icon = isSuccess ? 'check-circle' : 'times-circle';
                    const acceptedText = isSuccess ? ' (ACEPTADO)' : ' (RECHAZADO)';
                    let messageHtml = '';
                    if (isSuccess) {
                        messageHtml = `<div class="text-success"><strong>¡Enviado correctamente!${acceptedText}</strong><br>${responseData.message || ''}</div>`;
                    } else {
                        const payloadHtml = `<details><summary>Mostrar payload enviado</summary><pre style="white-space:pre-wrap; max-height:300px; overflow:auto;">${escapeHtml(JSON.stringify(invoice, null, 2))}</pre></details>`;
                        messageHtml = `<div class="text-danger"><strong>Error en el envío${acceptedText}:</strong><br>${details.join('<br/>')}<br/>${payloadHtml}</div>`;
                    }
                    const html = `
                        <div id="${elId}" class="alert ${alertClass} mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-${icon} mr-2"></i>
                                <h6 class="font-weight-bold mb-0">Factura ${invoice.number}</h6>
                            </div>
                            <div class="mt-2">${messageHtml}</div>
                        </div>
                    `;

                    // Reemplazar o agregar
                    if ($('#' + elId).length) {
                        $('#' + elId).replaceWith(html);
                    } else {
                        resultsContainer.append(html);
                    }

                    completed++;
                    const progress = ((completed) / total * 100).toFixed(2);
                    progressBarInner.css('width', progress + '%').text(progress + '%');

                    return resultObj;
                } catch (error) {
                    const resultObj = { invoice: invoice.number, status: 'error', message: '', error: error.message, success: false };
                    resultsContainer.append(`
                        <div class="alert alert-danger mb-2">
                            <strong>Error en Factura ${invoice.number}:</strong><br>
                            ${error.message}
                        </div>
                    `);
                    completed++;
                    const progress = ((completed) / total * 100).toFixed(2);
                    progressBarInner.css('width', progress + '%').text(progress + '%');
                    return resultObj;
                }
            };

            // Ejecutar todas las promesas en paralelo
            const promises = window.transformedData.map(inv => sendInvoice(inv));
            const settled = await Promise.all(promises);

            // Consolidar resultados
            settled.forEach(r => results.push(r));

            // Ocultar barra de progreso
            progressBar.addClass('d-none');
            $finishButton.removeClass('d-none'); // Mostrar botón finalizar
            // Ocultar el botón procesar
            $processButton.addClass('d-none');

            // liberar bandera de procesamiento
            window.processingInvoices = false;

            // Agregar mensaje de completado
            resultsContainer.prepend(`
                <div class="alert alert-info">
                    <i class="fas fa-check-circle mr-2"></i>
                    <strong>Proceso Completado:</strong> Se procesaron ${results.length} facturas.
                    <br>
                    <small>Haga clic en "Finalizar" para actualizar la lista de documentos.</small>
                </div>
            `);

            return;
        }
    });

    function formatDate(dateValue) {
        if (!dateValue) return null;

        // Fecha original (sin log)

        // Si la fecha viene como string en formato DD/MM/YYYY
        if (typeof dateValue === 'string' && dateValue.includes('/')) {
            const parts = dateValue.split('/');
            if (parts.length === 3) {
                return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
            }
        }

        // Si es una fecha de Excel (número)
        if (!isNaN(dateValue) && typeof dateValue === 'number') {
            const date = new Date((dateValue - 25569) * 86400 * 1000);
            // Fecha convertida de Excel
            return date.toISOString().split('T')[0];
        }

        // Si es una fecha ya formateada YYYY-MM-DD
        if (typeof dateValue === 'string' && dateValue.match(/^\d{4}-\d{2}-\d{2}$/)) {
            return dateValue;
        }

        // Si es un objeto Date
        if (dateValue instanceof Date) {
            return dateValue.toISOString().split('T')[0];
        }

        // No se pudo procesar la fecha (sin log)
        return dateValue;
    }

    function formatDecimal(number) {
        return number ? Number(number).toFixed(2) : "0.00";
    }

    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endpush