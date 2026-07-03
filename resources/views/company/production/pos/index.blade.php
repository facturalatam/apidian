@if(session('success'))
<div class="alert alert-primary alert-dismissible fade show" role="alert">
    {{ session('primary') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert">
        <span aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg></span>
    </button>
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
    </button>
</div>
@endif

<div class="row mt-3">
    <div class="col-md-12 mb-4">
        <div class="card card-config">
            <div class="card-header">
                <h5 class="mb-0 m-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings" style="margin-top: -3px"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                    Proceso de Configuración y Consulta POS
                </h5>
            </div>
            <div class="card-body">
                <div id="wizard-steps" class="mb-4 d-flex justify-content-center align-items-center">
                    <div class="wizard-stepper">
                        <div class="stepper-item" id="stepper-1">
                            <div class="stepper-circle">1</div>
                            <div class="stepper-label">Configuración de Software</div>
                        </div>
                        <div class="stepper-line"></div>
                        <div class="stepper-item" id="stepper-2">
                            <div class="stepper-circle">2</div>
                            <div class="stepper-label">Resolución de Habilitación</div>
                        </div>
                        <div class="stepper-line"></div>
                        <div class="stepper-item" id="stepper-3">
                            <div class="stepper-circle">3</div>
                            <div class="stepper-label">Paso a Producción</div>
                        </div>
                        <div class="stepper-line"></div>
                        <div class="stepper-item" id="stepper-4">
                            <div class="stepper-circle">4</div>
                            <div class="stepper-label">Resoluciones de Producción</div>
                        </div>
                    </div>
                </div>
                <div id="wizard-content">
                    <!-- Paso 1 -->
                    <div class="wizard-step" id="wizard-step-1">
                        <form action="{{ route('company.production.software.store', [$company->identification_number, 'pos']) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="software_id" class="form-label">
                                            <strong>ID Software <span class="text-danger">*</span></strong>
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               id="software_id"
                                               name="id"
                                               value="{{ $company->software->identifier_eqdocs ?? '' }}"
                                               required
                                               placeholder="Ej: 12345678-1234-1234-1234-123456789012">
                                        <small class="form-text text-muted">ID único del software proporcionado por la DIAN</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="software_pin" class="form-label">
                                            <strong>PIN Software <span class="text-danger">*</span></strong>
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               id="software_pin"
                                               name="pin"
                                               value="{{ $environmentStatus['has_software'] ? $environmentStatus['software_info']['pin'] : '' }}"
                                               required
                                               placeholder="Ej: 12345">
                                        <small class="form-text text-muted">PIN del software proporcionado por la DIAN</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-device-floppy" style="margin-top: -3px"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                                    {{ $environmentStatus['has_software'] ? 'Actualizar' : 'Crear' }} Software
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- Paso 2 -->
                    <div class="wizard-step d-none" id="wizard-step-2">
                        <div class="alert alert-dismissible d-none" role="alert" data-resolution-alert>
                            <span data-resolution-alert-text></span>
                            <button type="button" class="close" aria-label="Cerrar" style="background: transparent; border: 0; float: right; font-size: 1.5rem; line-height: 1; padding: 0;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="newResolutionForm" data-resolution-scope="pos" method="POST" action="{{ route('company.resolutions.store', ['company' => $company->identification_number]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type_document_id">Tipo de Documento <span class="text-danger">*</span></label>
                                        <select class="form-control" id="type_document_id" name="type_document_id">
                                            <option value="">Seleccionar tipo de documento</option>
                                            @foreach($typeDocuments as $typeDocument)
                                            <option value="{{ $typeDocument->id }}" data-code="{{ $typeDocument->code }}" {{ old('type_document_id', 15) == $typeDocument->id ? 'selected' : '' }}>{{ $typeDocument->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="prefix">Prefijo <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="prefix" name="prefix" maxlength="10" value="{{ old('prefix', $prefill['prefix'] ?? '') }}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="simpleTypeInfo" class="alert alert-info" style="display: none;">
                                <i class="fas fa-info-circle"></i>
                                <strong>Tipo de resolución simplificada:</strong> Solo se requieren Tipo de documento, Prefijo y Rangos. Los demás campos son opcionales.
                            </div>
                            <div id="additionalFields">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="resolution">Número de Resolución <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="resolution" name="resolution" value="{{ old('resolution', $prefill['resolution'] ?? '') }}">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="resolution_date">Fecha de Resolución <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="resolution_date" name="resolution_date" value="{{ old('resolution_date', $prefill['resolution_date'] ?? '') }}">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="technical_key">Clave Técnica <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="technical_key" name="technical_key" value="{{ old('technical_key', $prefill['technical_key'] ?? '') }}">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="from">Rango Inicial <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="from" name="from" min="1" value="{{ old('from', $prefill['from'] ?? '') }}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="to">Rango Final <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="to" name="to" min="1" value="{{ old('to', $prefill['to'] ?? '') }}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="datesAndEnvironment">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_from">Fecha Inicio Vigencia <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ old('date_from', $prefill['date_from'] ?? '') }}">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_to">Fecha Fin Vigencia <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ old('date_to', $prefill['date_to'] ?? '') }}">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-primary" id="saveResolutionBtn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-device-floppy" style="margin-top: -3px"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg> 
                                    Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- Paso 3 -->
                    <div class="wizard-step d-none" id="wizard-step-3">
                        <form id="productionForm" method="POST" action="#" autocomplete="off">
                            @csrf
                            <div class="form-group">
                                <label for="test_set_id">Set de Pruebas DIAN</label>
                                <input type="text" class="form-control" id="test_set_id" name="test_set_id" required placeholder="Ingrese el TestSetId entregado por la DIAN">
                            </div>
                            <button type="submit" class="btn btn-primary mt-2" id="btnIniciar">Iniciar Paso a Producción</button>
                        </form>
                        <div id="production-steps" style="display:none;">
                            <div id="step1" class="mb-3">
                                <strong>1. Enviar Documento POS de Prueba</strong>
                                <div class="status"></div>
                            </div>
                            <div id="step2" class="mb-3">
                                <strong>2. Consultar ZipKey</strong>
                                <div class="status"></div>
                            </div>
                            <div id="step3" class="mb-3">
                                <strong>3. Cambiar Ambiente a Producción</strong>
                                <div class="status"></div>
                            </div>
                        </div>
                        <div id="finalMessage" class="mt-4" style="display:none;"></div>
                    </div>
                    <!-- Paso 4 -->
                    <div class="wizard-step d-none" id="wizard-step-4">
                        <h3 class="mb-2">
                            Consultar Resoluciones Asociadas
                            <span
                                data-toggle="tooltip"
                                data-placement="right"
                                title="Aquí puedes consultar las resoluciones asociadas a tu empresa.">
                                <i class="fas fa-info-circle text-info" style="cursor:pointer;"></i>
                            </span>
                            <a href="#" id="btnVistaPreviaPos" class="ml-2" data-toggle="modal" data-target="#modalVistaPreviaPos">
                                <i class="fas fa-image"></i> Vista Previa
                            </a>
                        </h3>
                        <button id="btnConsultarResolucionesPos" type="button" class="btn btn-primary">Consultar</button>
                        <div id="resolucionesResultPos" class="mt-3"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <div id="btnPrevStepContainer" style="flex:1;">
                        <button id="btnPrevStep" class="btn btn-secondary" style="display: none;">Volver</button>
                    </div>
                    <div style="flex:1; text-align: right;">
                        <button id="btnNextStep" class="btn btn-primary">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Vista Previa POS -->
<div class="modal fade" id="modalVistaPreviaPos" tabindex="-1" role="dialog" aria-labelledby="modalVistaPreviaPosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-custom-width" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVistaPreviaPosLabel">Vista previa de Resoluciones POS</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="carouselPasosPos" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <li data-target="#carouselPasosPos" data-slide-to="0" class="active"></li>
                        <li data-target="#carouselPasosPos" data-slide-to="1"></li>
                        <li data-target="#carouselPasosPos" data-slide-to="2"></li>
                    </ol>
                    <div class="carousel-inner text-center">
                        <div class="carousel-item active">
                            <img src="/resolutions/PASO1.png" class="d-block mx-auto mb-2" style="width: 100%; height: auto;" alt="Paso 1">
                            <div><strong>Paso 1</strong></div>
                        </div>
                        <div class="carousel-item">
                            <img src="/resolutions/PASO2.png" class="d-block mx-auto mb-2" style="width: 100%; height: auto;" alt="Paso 2">
                            <div><strong>Paso 2</strong></div>
                        </div>
                        <div class="carousel-item">
                            <img src="/resolutions/PASO3.png" class="d-block mx-auto mb-2" style="width: 100%; height: auto;" alt="Paso 3">
                            <div><strong>Paso 3</strong></div>
                        </div>
                    </div>
                    <a class="carousel-control-prev" href="#carouselPasosPos" role="button" data-slide="prev" style="width: 5%; background: rgba(0,0,0,0.2);">
                        <span class="carousel-control-prev-icon" aria-hidden="true" style="height: 48px; width: 48px;"></span>
                        <span class="sr-only">Anterior</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselPasosPos" role="button" data-slide="next" style="width: 5%; background: rgba(0,0,0,0.2);">
                        <span class="carousel-control-next-icon" aria-hidden="true" style="height: 48px; width: 48px;"></span>
                        <span class="sr-only">Siguiente</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .modal-custom-width {
        max-width: 90vw !important;
    }
    .wizard-step {
        display: none;
    }
    .wizard-step.active {
        display: block;
    }
    .wizard-stepper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
    }
    .stepper-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 120px;
        position: relative;
    }
    .stepper-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #e0e7ef;
        color: #4170d7ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 20px;
        border: 2px solid #e0e7ef;
        transition: all 0.3s;
        margin-bottom: 6px;
    }
    .stepper-label {
        font-size: 15px;
        color: #888;
        font-weight: 500;
        text-align: center;
        min-width: 90px;
    }
    .stepper-item.completed .stepper-circle {
        background: #4170d7ff;
        color: #fff;
        border-color: #4170d7ff;
    }
    .stepper-item.completed .stepper-label {
        color: #4170d7ff;
    }
    .stepper-line {
        flex: 1 1 0;
        height: 3px;
        background: #e0e7ef;
        margin: 0 8px;
        border-radius: 2px;
        min-width: 30px;
        max-width: 60px;
        position: relative;
    }
    @media (max-width: 700px) {
        .wizard-stepper {
            flex-direction: column;
            gap: 10px;
        }
        .stepper-line {
            width: 3px;
            height: 30px;
            margin: 8px 0;
            min-width: unset;
            max-width: unset;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 4;

    function updateWizard() {
        for (let i = 1; i <= totalSteps; i++) {
            document.getElementById('wizard-step-' + i).classList.toggle('active', i === currentStep);
            document.getElementById('wizard-step-' + i).classList.toggle('d-none', i !== currentStep);
        }
        document.getElementById('btnPrevStep').style.display = currentStep === 1 ? 'none' : '';
        if (currentStep === totalSteps) {
            document.getElementById('btnNextStep').innerText = 'Finalizar';
        } else {
            document.getElementById('btnNextStep').innerText = 'Siguiente';
        }
    }

    function updateStepper() {
        for (let i = 1; i <= 4; i++) {
            const item = document.getElementById('stepper-' + i);
            item.classList.remove('active', 'completed');
            if (i < currentStep) {
                item.classList.add('completed');
            } else if (i === currentStep) {
                item.classList.add('active');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateWizard();
        updateStepper();

        document.getElementById('btnPrevStep').addEventListener('click', function() {
            if (currentStep > 1) {
                currentStep--;
                updateWizard();
                updateStepper();
            }
        });
        document.getElementById('btnNextStep').addEventListener('click', function() {
            if (currentStep < totalSteps) {
                currentStep++;
                updateWizard();
                updateStepper();
            } else if (currentStep === totalSteps) {
                window.location.href = "{{ route('company.production.tabs', [$company->identification_number, 'pos']) }}";
            }
        });
    });
</script>
@endpush

@push('scripts')
<script>
    function setStepStatus(step, status, message = '') {
        const el = document.querySelector('#' + step + ' .status');
        if (status === 'loading') {
            el.innerHTML = '<span class="text-info"><i class="fa fa-spinner fa-spin"></i> Procesando...</span>';
        } else if (status === 'primary') {
            el.innerHTML = '<span class="text-primary"><i class="fa fa-check-circle"></i> ' + message + '</span>';
        } else if (status === 'error') {
            el.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> ' + message + '</span>';
        } else {
            el.innerHTML = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('productionForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                document.getElementById('production-steps').style.display = 'block';
                document.getElementById('finalMessage').style.display = 'block';
                document.getElementById('finalMessage').innerHTML = '';
                setStepStatus('step1', 'loading');
                setStepStatus('step2', '');
                setStepStatus('step3', '');

                const testSetId = document.getElementById('test_set_id').value;
                const url = "{{ route('company.production.process', $company->identification_number) }}";
                const token = '{{ csrf_token() }}';

                // Paso 1: Enviar documento POS de prueba
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            test_set_id: testSetId,
                            step: 1,
                            type: 'pos'
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            setStepStatus('step1', 'error', data.error);
                            document.getElementById('finalMessage').innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                            return;
                        }
                        setStepStatus('step1', 'primary', 'Documento POS enviado correctamente');
                        // Paso 2: Consultar ZipKey
                        setStepStatus('step2', 'loading');
                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    test_set_id: testSetId,
                                    step: 2,
                                    zipkey: data.zipkey,
                                    type: 'pos'
                                })
                            })
                            .then(res2 => res2.json())
                            .then(data2 => {
                                if (data2.error) {
                                    setStepStatus('step2', 'error', data2.error);
                                    document.getElementById('finalMessage').innerHTML = '<div class="alert alert-danger">' + data2.error + '</div>';
                                    return;
                                }
                                setStepStatus('step2', 'primary', 'ZipKey consultado correctamente');
                                // Paso 3: Cambiar ambiente
                                setStepStatus('step3', 'loading');
                                fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token,
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            test_set_id: testSetId,
                                            step: 3,
                                            type: 'pos'
                                        })
                                    })
                                    .then(res3 => res3.json())
                                    .then(data3 => {
                                        if (data3.error) {
                                            setStepStatus('step3', 'error', data3.error);
                                            document.getElementById('finalMessage').innerHTML = '<div class="alert alert-danger">' + data3.error + '</div>';
                                            return;
                                        }
                                        setStepStatus('step3', 'primary', 'Ambiente cambiado a producción correctamente');
                                        document.getElementById('finalMessage').innerHTML = '<div class="alert alert-primary">¡Proceso completado correctamente!</div>';
                                    });
                            });
                    })
                    .catch(err => {
                        setStepStatus('step1', 'error', 'Error inesperado');
                        document.getElementById('finalMessage').innerHTML = '<div class="alert alert-danger">Error inesperado</div>';
                        console.error('Error en el proceso de paso a producción:', err);
                    });
            });
        }
    });
</script>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('btnConsultarResolucionesPos');
        if (btn) {
            btn.addEventListener('click', function() {
                btn.disabled = true;
                btn.innerHTML = 'Consultando...';
                fetch("{{ route('company.production.consult-resolutions', [$company->identification_number, 'pos']) }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ type: 'pos' })
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = 'Consultar';
                    const resultDiv = document.getElementById('resolucionesResultPos');
                    let list = [];
                    let opDesc = '';
                    try {
                        const result = data.ResponseDian.Envelope.Body.GetNumberingRangeResponse.GetNumberingRangeResult;
                        opDesc = result.OperationDescription ?? '';
                        if (
                            result.ResponseList &&
                            result.ResponseList.NumberRangeResponse
                        ) {
                            list = result.ResponseList.NumberRangeResponse;
                            if (!Array.isArray(list)) {
                                list = [list];
                            }
                        }
                    } catch (e) {
                        resultDiv.innerHTML = '<span class="text-danger">No se encontraron resoluciones válidas</span>';
                        return;
                    }
                    if (!list || list.length === 0) {
                        resultDiv.innerHTML = `<span class="text-danger">${opDesc ? opDesc : 'No se encontraron resoluciones válidas.'}</span>`;
                        return;
                    }
                    let html = `
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Prefijo</th>
                                <th>Número Resolución</th>
                                <th>Fecha Resolución</th>
                                <th>Rango</th>
                                <th>Inicio Vigencia</th>
                                <th>Fin Vigencia</th>
                                <th>Clave Técnica</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                    `;
                    list.forEach(item => {
                        const params = new URLSearchParams({
                            prefix: item.Prefix ?? '',
                            resolution: item.ResolutionNumber ?? '',
                            resolution_date: item.ResolutionDate ?? '',
                            from: item.FromNumber ?? '',
                            to: item.ToNumber ?? '',
                            date_from: item.ValidDateFrom ?? '',
                            date_to: item.ValidDateTo ?? '',
                            technical_key: (typeof item.TechnicalKey === 'object' && item.TechnicalKey?._attributes?.nil === 'true') ? '' : (item.TechnicalKey ?? '')
                        }).toString();
                        const createUrl = `/companies/{{ $company->identification_number }}/configuration/resolutions/create?${params}`;
                        html += `
                        <tr>
                            <td>${item.Prefix ?? ''}</td>
                            <td>${item.ResolutionNumber ?? ''}</td>
                            <td>${item.ResolutionDate ?? ''}</td>
                            <td>${item.FromNumber ?? ''} - ${item.ToNumber ?? ''}</td>
                            <td>${item.ValidDateFrom ?? ''}</td>
                            <td>${item.ValidDateTo ?? ''}</td>
                            <td>${typeof item.TechnicalKey === 'object' && item.TechnicalKey?._attributes?.nil === 'true' ? '' : (item.TechnicalKey ?? '')}</td>
                            <td>
                                <a href="${createUrl}" class="btn btn-sm btn-primary text-white" title="Crear resolución">
                                    <i class="fas fa-plus"></i> Crear
                                </a>
                            </td>
                        </tr>
                        `;
                    });
                    html += `
                        </tbody>
                    </table>
                    </div>
                    `;
                    resultDiv.innerHTML = html;
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = 'Consultar';
                    document.getElementById('resolucionesResultPos').innerHTML = '<div class="alert alert-danger">Error consultando resoluciones</div>';
                });
            });
        }
    });
</script>
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip()
    });
</script>

<script>
    $(document).ready(function() {
        function showInlineAlert($form, type, text) {
            const $alert = $form.closest('.wizard-step').find('[data-resolution-alert]').first();
            if ($alert.length === 0) return;

            $alert.removeClass('d-none alert-success alert-danger alert-warning alert-info')
                .addClass(type === 'success' ? 'alert-success' : 'alert-danger');
            $alert.find('[data-resolution-alert-text]').text(text);
        }

        function hideInlineAlert($form) {
            const $alert = $form.closest('.wizard-step').find('[data-resolution-alert]').first();
            if ($alert.length === 0) return;
            $alert.addClass('d-none');
        }

        function notify($form, type, text, delay) {
            if (window.PNotify) {
                new PNotify({
                    text: text,
                    type: type,
                    addclass: type === 'success' ? 'notification-success' : 'notification-danger',
                    delay: delay || (type === 'success' ? 3000 : 5000)
                });
            }
            showInlineAlert($form, type, text);
        }

        function clearFormErrors($form) {
            $form.find('.form-control').removeClass('is-invalid');
            $form.find('.invalid-feedback').text('').hide();
        }

        function displayFormErrors($form, errors) {
            $.each(errors, function(field, messages) {
                const $input = $form.find(`[name="${field}"]`);
                if ($input.length === 0) return;

                const $feedback = $input.siblings('.invalid-feedback');
                $input.addClass('is-invalid');
                $feedback.text(messages[0]).show();
            });
        }

        $(document).on('click', '[data-resolution-alert] .btn-close, [data-resolution-alert] .close', function() {
            $(this).closest('[data-resolution-alert]').addClass('d-none');
        });

        $(document).on('change', 'form[data-resolution-scope="pos"] select[name="type_document_id"]', function() {
            const $form = $(this).closest('form');
            const selectedOption = $(this).find('option:selected');
            const code = selectedOption.data('code');
            const simpleCodes = ['91', '92', '93', '94'];
            const $info = $form.find('#simpleTypeInfo');

            if ($info.length === 0) return;

            if (simpleCodes.includes(code)) {
                $info.slideDown();
            } else {
                $info.slideUp();
            }
        });

        $(document).on('submit', 'form[data-resolution-scope="pos"]', function(e) {
            e.preventDefault();

            const $form = $(this);

            const $submitBtn = $form.find('button[type="submit"]').first();
            const originalHtml = $submitBtn.html();

            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            hideInlineAlert($form);
            clearFormErrors($form);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        notify($form, 'success', response.message || 'Resolución creada exitosamente.', 3000);
                        // Recargar para reflejar la nueva resolución (comportamiento consistente con Factura)
                        setTimeout(function() {
                            location.reload();
                        }, 800);
                    } else {
                        notify($form, 'error', (response && response.message) ? response.message : 'Error al crear la resolución', 5000);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        displayFormErrors($form, xhr.responseJSON.errors);
                        notify($form, 'error', 'Por favor corrige los errores en el formulario', 5000);
                        return;
                    }

                    const message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error interno del servidor';
                    notify($form, 'error', message, 5000);
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>
@endpush