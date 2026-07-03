<!-- Modal Ver CUFE -->
<div class="modal fade" id="cufeModal" tabindex="-1" role="dialog" aria-labelledby="cufeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cufeModalLabel">CUFE del documento</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-1">Código Único de Factura Electrónica:</p>
                <div class="input-group">
                    <input type="text" id="cufeValue" class="form-control form-control-sm" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="copyCufeBtn">
                            <i class="fas fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
                <small id="cufeCopiedMsg" class="text-success d-none">&#10003; Copiado al portapapeles</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultModalLabel">Consulta de CUFE</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="modalBodyContent"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="modalBodyResponse"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">Esto cambiará el estado del documento en este listado del API, es importante que se verifique el <strong>CUFE</strong> en la DIAN donde se muestre como ACEPTADO para continuar con este procedimiento.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
                <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="excelTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="sales-tab" data-toggle="tab" href="#sales" role="tab" aria-controls="sales" aria-selected="true">Facturas de Ventas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="health-tab" data-toggle="tab" href="#health" role="tab" aria-controls="health" aria-selected="false">Facturas de Salud</a>
                    </li>
                </ul>
                <div class="tab-content mt-3" id="excelTabsContent">
                    <div class="tab-pane fade show active" id="sales" role="tabpanel" aria-labelledby="sales-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0">Plantilla: Facturas de Ventas</h6>
                            </div>
                            <a href="{{ asset('xlsx/co-documents-batch.xlsx') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download mr-1"></i>Descargar Plantilla
                            </a>
                        </div>
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
                    <div class="tab-pane fade" id="health" role="tabpanel" aria-labelledby="health-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0">Plantilla: Facturas de Salud</h6>
                            </div>
                            <a href="{{ asset('xlsx/co-documents-batch-health.xlsx') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download mr-1"></i>Descargar Plantilla Salud
                            </a>
                        </div>
                        <div class="form-group">
                            <label for="excelFileHealth" class="font-weight-bold d-block">
                                <i class="fas fa-upload mr-2"></i>Archivo Excel
                            </label>
                            <input type="file" class="form-control-file" id="excelFileHealth" accept=".xls,.xlsx">
                        </div>
                        <div class="progress mt-3 d-none" id="progressBarHealth">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"></div>
                        </div>
                        <div class="card border mt-4">
                            <div class="card-header">
                                <i class="fas fa-list-alt mr-2"></i>Resultado del Procesamiento
                            </div>
                            <div id="apiResultsHealth" class="card-body bg-light" style="max-height: 300px; overflow-y: auto; font-family: monospace;"></div>
                        </div>
                    </div>
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
                <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
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
</div>
