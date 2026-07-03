@extends('layouts.app')

@section('content')
<header class="page-header">
    <h2>Paso a Producción</h2>
</header>

<div class="card border">
    <div class="card-body">
        <h3>Software de Facturación Electrónica</h3>
        @if(!$isProduction)
        <form id="productionForm" method="POST" action="#" autocomplete="off">
            @csrf
            <div class="form-group">
                <label for="test_set_id">Set de Pruebas DIAN</label>
                <input type="text" class="form-control" id="test_set_id" name="test_set_id" required placeholder="Ingrese el TestSetId entregado por la DIAN">
            </div>
            <button type="submit" class="btn btn-success mt-3" id="btnIniciar">Iniciar Paso a Producción</button>
        </form>
        <div id="production-steps" style="display:none;">
            <div id="step1" class="mb-3">
                <strong>1. Enviar Factura de Prueba</strong>
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
        @endif

        {{-- Nuevo div para el botón y resultado de resoluciones --}}
        @if($isProduction)
        <div class="card border mt-4">
            <div class="card-body">
                <h3 class="mb-3">
                    Consultar Resoluciones Asociadas
                    <span
                        data-toggle="tooltip"
                        data-placement="right"
                        title="Aquí puedes consultar las resoluciones asociadas a tu empresa.">
                        <i class="fas fa-info-circle text-info" style="cursor:pointer;"></i>
                    </span>
                    <a href="#" id="btnVistaPrevia" class="ml-2" data-toggle="modal" data-target="#modalVistaPrevia">
                        <i class="fas fa-image"></i> Vista Previa
                    </a>
                </h3>
                <button id="btnConsultarResoluciones" type="button" class="btn btn-primary">Consultar</button>
                <div id="resolucionesResult" class="mt-3"></div>
            </div>
        </div>
        @endif
    </div>
</div>
<div class="modal fade" id="modalVistaPrevia" tabindex="-1" role="dialog" aria-labelledby="modalVistaPreviaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-custom-width" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVistaPreviaLabel">Vista previa de Resoluciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="carouselPasos" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <li data-target="#carouselPasos" data-slide-to="0" class="active"></li>
                        <li data-target="#carouselPasos" data-slide-to="1"></li>
                        <li data-target="#carouselPasos" data-slide-to="2"></li>
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
                    <a class="carousel-control-prev" href="#carouselPasos" role="button" data-slide="prev" style="width: 5%; background: rgba(0,0,0,0.2);">
                        <span class="carousel-control-prev-icon" aria-hidden="true" style="height: 48px; width: 48px;"></span>
                        <span class="sr-only">Anterior</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselPasos" role="button" data-slide="next" style="width: 5%; background: rgba(0,0,0,0.2);">
                        <span class="carousel-control-next-icon" aria-hidden="true" style="height: 48px; width: 48px;"></span>
                        <span class="sr-only">Siguiente</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    function setStepStatus(step, status, message = '') {
        const el = document.querySelector('#' + step + ' .status');
        if (status === 'loading') {
            el.innerHTML = '<span class="text-info"><i class="fa fa-spinner fa-spin"></i> Procesando...</span>';
        } else if (status === 'success') {
            el.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> ' + message + '</span>';
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
                const url = "{{ route('company.production.process', $company) }}";
                const token = '{{ csrf_token() }}';

                // Paso 1: Enviar factura de prueba
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            test_set_id: testSetId,
                            step: 1
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            setStepStatus('step1', 'error', data.error);
                            document.getElementById('finalMessage').innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                            return;
                        }
                        setStepStatus('step1', 'success', 'Factura enviada correctamente');
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
                                    zipkey: data.zipkey
                                })
                            })
                            .then(res2 => res2.json())
                            .then(data2 => {
                                if (data2.error) {
                                    setStepStatus('step2', 'error', data2.error);
                                    document.getElementById('finalMessage').innerHTML = '<div class="alert alert-danger">' + data2.error + '</div>';
                                    return;
                                }
                                setStepStatus('step2', 'success', 'ZipKey consultado correctamente');
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
                                            step: 3
                                        })
                                    })
                                    .then(res3 => res3.json())
                                    .then(data3 => {
                                        if (data3.error) {
                                            setStepStatus('step3', 'error', data3.error);
                                            document.getElementById('finalMessage').innerHTML = '<div class="alert alert-danger">' + data3.error + '</div>';
                                            return;
                                        }
                                        setStepStatus('step3', 'success', 'Ambiente cambiado a producción correctamente');
                                        document.getElementById('finalMessage').innerHTML = '<div class="alert alert-success">¡Proceso completado correctamente!</div>';
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
        const btn = document.getElementById('btnConsultarResoluciones');
        if (btn) {
            btn.addEventListener('click', function() {
                const resultDiv = document.getElementById('resolucionesResult');
                resultDiv.innerHTML = 'Consultando...';
                fetch("{{ route('company.consultar.resoluciones', $company->identification_number) }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Verifica si hay lista de resoluciones
                        let list = [];
                        try {
                            list = data.ResponseDian.Envelope.Body.GetNumberingRangeResponse.GetNumberingRangeResult.ResponseList.NumberRangeResponse;
                            if (!Array.isArray(list)) {
                                list = [list]; // Si solo hay una resolución, lo convierte en array
                            }
                        } catch (e) {
                            resultDiv.innerHTML = '<span class="text-danger">No se encontraron resoluciones válidas</span>';
                            return;
                        }

                        // Construir tabla
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
                            // Construye la URL con parámetros GET para prellenar el formulario
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
                    .catch(error => {
                        resultDiv.innerHTML = '<span class="text-danger">Error consultando resoluciones</span>';
                    });
            });
        }
    });
</script>
@endpush
@push('scripts')
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip()
    });
</script>
@endpush
@push('styles')
<style>
    .modal-custom-width {
        max-width: 90vw !important;
    }
</style>
@endpush
@endsection