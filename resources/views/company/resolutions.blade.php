@extends('layouts.app')

@push('styles')
<style>
.modal-lg {
    max-width: 800px;
}

.text-danger {
    color: #dc3545 !important;
}

.form-group {
    margin-bottom: 1rem;
}

.invalid-feedback {
    display: none;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.form-control.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.is-invalid .invalid-feedback {
    display: block;
}

#saveResolutionBtn:disabled {
    opacity: 0.6;
}
</style>
@endpush

@section('content')
<header class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2>
            Listado de Resoluciones
        </h2>
        <br>
        <span class="text-muted">{{ $company->user->name }} - {{ $company->user->email }} - {{ $company->identification_number }}-{{ $company->dv }}</span>
    </div>
    <div class="right-wrapper text-right mt-auto pb-1">
        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
        <button class="btn btn-primary btn-sm text-white ml-2" data-toggle="modal" data-target="#newResolutionModal">
            <i class="fas fa-plus"></i> Nueva resolución
        </button>
    </div>
</header>

<div class="card">
    <!-- Users Table -->
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Entorno</th>
                    <th>Prefijo</th>
                    <th>Número</th>
                    <th>Tipo de Documento</th>
                    <th>Fecha</th>
                    <th>Rango</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Clave Técnica</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resolutions as $resolution)
                @php
                    $environtment = 'N/A';
                    $has_environtment = true;
                    switch ($resolution->type_environment_id) {
                        case '2':
                            $environtment = 'Habilitación';
                            break;
                        case '1':
                            $environtment = 'Producción';
                            break;
                        default:
                            $environtment = 'N/A';
                            $has_environtment = false;
                            break;
                    }
                @endphp
                <tr class="table-light">
                    <td>
                        @if($has_environtment)
                            {{ $environtment }}
                        @else
                            <form action="{{ route('company.resolutions.update', ['resolution' => $resolution->id, 'company' => $company->identification_number]) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-xs btn-warning">Actualizar</button>
                            </form>
                        @endif
                    </td>
                    <td>{{ $resolution->prefix }}</td>
                    <td>{{ $resolution->resolution }}</td>
                    <td>{{ $resolution->type_document->name }}</td>
                    <td>{{ $resolution->resolution_date }}</td>
                    <td>{{ $resolution->from }} - {{ $resolution->to }}</td>
                    <td>{{ $resolution->date_from }}</td>
                    <td>{{ $resolution->date_to }}</td>
                    <td>{{ $resolution->technical_key }}</td>
                    <td class="text-right">
                        <button class="btn btn-sm btn-primary edit-resolution-btn"
                                data-id="{{ $resolution->id }}"
                                data-type-document-id="{{ $resolution->type_document_id }}"
                                data-type-document-code="{{ $resolution->type_document->code }}"
                                data-prefix="{{ $resolution->prefix }}"
                                data-resolution="{{ $resolution->resolution }}"
                                data-resolution-date="{{ $resolution->resolution_date }}"
                                data-technical-key="{{ $resolution->technical_key }}"
                                data-from="{{ $resolution->from }}"
                                data-to="{{ $resolution->to }}"
                                data-date-from="{{ $resolution->date_from }}"
                                data-date-to="{{ $resolution->date_to }}"
                                data-toggle="modal"
                                data-target="#editResolutionModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row">
        <div class="col-12 d-flex justify-content-end pr-4">
            {{ $resolutions->links() }}
        </div>
    </div>
</div>

<!-- Modal Nueva Resolución -->
<div class="modal fade" id="newResolutionModal" tabindex="-1" role="dialog" aria-labelledby="newResolutionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newResolutionModalLabel">Nueva Resolución</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="newResolutionForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type_document_id">Tipo de Documento <span class="text-danger">*</span></label>
                                <select class="form-control" id="type_document_id" name="type_document_id">
                                    <option value="">Seleccionar tipo de documento</option>
                                    @foreach($typeDocuments as $typeDocument)
                                        <option value="{{ $typeDocument->id }}" data-code="{{ $typeDocument->code }}">{{ $typeDocument->name }}</option>
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

                    <!-- Mensaje informativo para tipos simples -->
                    <div id="simpleTypeInfo" class="alert alert-info" style="display: none;">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tipo de resolución simplificada:</strong> Solo se requieren Tipo de documento, Prefijo y Rangos. Los demás campos son opcionales.
                    </div>

                    <!-- Campos adicionales que se ocultan para tipos simples -->
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

                    <!-- Campos de fechas de vigencia y entorno (solo para tipos completos) -->
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveResolutionBtn">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Resolución -->
<div class="modal fade" id="editResolutionModal" tabindex="-1" role="dialog" aria-labelledby="editResolutionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editResolutionModalLabel">Editar Resolución</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editResolutionForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_resolution_id" name="resolution_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_type_document_id">Tipo de Documento <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_type_document_id" name="type_document_id">
                                    <option value="">Seleccionar tipo de documento</option>
                                    @foreach($typeDocuments as $typeDocument)
                                        <option value="{{ $typeDocument->id }}" data-code="{{ $typeDocument->code }}">{{ $typeDocument->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_prefix">Prefijo</label>
                                <input type="text" class="form-control" id="edit_prefix" name="prefix" readonly style="background-color: #f8f9fa;">
                                <small class="form-text text-muted">El prefijo no puede ser modificado</small>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje informativo para tipos simples -->
                    <div id="editSimpleTypeInfo" class="alert alert-info" style="display: none;">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tipo de resolución simplificada:</strong> Solo se requieren Tipo de documento, Prefijo y Rangos. Los demás campos son opcionales.
                    </div>

                    <!-- Campos adicionales que se ocultan para tipos simples -->
                    <div id="editAdditionalFields">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_resolution">Número de Resolución <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_resolution" name="resolution" value="{{ old('resolution', $prefill['resolution'] ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_resolution_date">Fecha de Resolución <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="edit_resolution_date" name="resolution_date" value="{{ old('resolution_date', $prefill['resolution_date'] ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_technical_key">Clave Técnica <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_technical_key" name="technical_key" value="{{ old('technical_key', $prefill['technical_key'] ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_from">Rango Inicial <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_from" name="from" min="1" value="{{ old('from', $prefill['from'] ?? '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_to">Rango Final <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_to" name="to" min="1" value="{{ old('to', $prefill['to'] ?? '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos de fechas de vigencia (solo para tipos completos) -->
                    <div id="editDatesAndEnvironment">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_date_from">Fecha Inicio Vigencia <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="edit_date_from" name="date_from" value="{{ old('date_from', $prefill['date_from'] ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_date_to">Fecha Fin Vigencia <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="edit_date_to" name="date_to" value="{{ old('date_to', $prefill['date_to'] ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="updateResolutionBtn">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Mostrar mensaje de éxito
    @if (session('success'))
        new PNotify({
            text: '{{ session('success') }}',
            type: 'success',
            addclass: 'notification-success',
            delay: 3000
        });
    @endif

    // Mostrar mensaje de error
    @if (session('error'))
        new PNotify({
            text: '{{ session('error') }}',
            type: 'error',
            addclass: 'notification-danger',
            delay: 3000
        });
    @endif

    // Limpiar formulario cuando se abre el modal
    $('#newResolutionModal').on('show.bs.modal', function () {
        clearFormErrors();
        $('#newResolutionForm')[0].reset();
        $('#simpleTypeInfo').hide();
    });

    // Manejar cambio en tipo de documento
    $('#type_document_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const code = selectedOption.data('code');
        const simpleCodes = ['91', '92', '93', '94'];

        if (simpleCodes.includes(code)) {
            $('#simpleTypeInfo').slideDown();
        } else {
            $('#simpleTypeInfo').slideUp();
        }
    });

    // Manejar envío del formulario
    $('#newResolutionForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $('#saveResolutionBtn');
        const originalText = submitBtn.html();

        // Deshabilitar botón y mostrar estado de carga
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        // Limpiar errores previos
        clearFormErrors();

        // Preparar datos del formulario
        const formData = $(this).serialize();
        const company = '{{ $company->identification_number }}';

        // Realizar petición AJAX
        $.ajax({
            url: '{{ route('company.resolutions.store', ['company' => $company->identification_number]) }}',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Mostrar notificación de éxito
                    new PNotify({
                        text: response.message,
                        type: 'success',
                        addclass: 'notification-success',
                        delay: 3000
                    });

                    // Cerrar modal
                    $('#newResolutionModal').modal('hide');

                    // Recargar página para mostrar la nueva resolución
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    // Mostrar notificación de error
                    new PNotify({
                        text: response.message || 'Error al crear la resolución',
                        type: 'error',
                        addclass: 'notification-danger',
                        delay: 5000
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Errores de validación
                    const errors = xhr.responseJSON.errors;
                    displayFormErrors(errors);

                    new PNotify({
                        text: 'Por favor corrige los errores en el formulario',
                        type: 'error',
                        addclass: 'notification-danger',
                        delay: 5000
                    });
                } else {
                    // Otros errores
                    const message = xhr.responseJSON?.message || 'Error interno del servidor';
                    new PNotify({
                        text: message,
                        type: 'error',
                        addclass: 'notification-danger',
                        delay: 5000
                    });
                }
            },
            complete: function() {
                // Restaurar botón
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Manejar clic en botón editar (usando event delegation para elementos dinámicos)
    $(document).on('click', '.edit-resolution-btn', function() {
        const button = $(this);

        // Llenar el formulario con los datos de la resolución
        $('#edit_resolution_id').val(button.data('id'));
        $('#edit_type_document_id').val(button.data('type-document-id'));
        $('#edit_prefix').val(button.data('prefix'));
        $('#edit_resolution').val(button.data('resolution'));
        $('#edit_resolution_date').val(button.data('resolution-date'));
        $('#edit_technical_key').val(button.data('technical-key'));
        $('#edit_from').val(button.data('from'));
        $('#edit_to').val(button.data('to'));
        $('#edit_date_from').val(button.data('date-from'));
        $('#edit_date_to').val(button.data('date-to'));

        // Verificar si es un tipo simple y mostrar/ocultar campos
        const code = button.data('type-document-code');
        const simpleCodes = ['91', '92', '93', '94'];

        if (simpleCodes.includes(code)) {
            $('#editSimpleTypeInfo').show();
        } else {
            $('#editSimpleTypeInfo').hide();
        }

        // Limpiar errores previos
        clearEditFormErrors();
    });

    // Limpiar formulario cuando se abre el modal de edición
    $('#editResolutionModal').on('show.bs.modal', function () {
        clearEditFormErrors();
    });

    // Manejar cambio en tipo de documento en edición
    $('#edit_type_document_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const code = selectedOption.data('code');
        const simpleCodes = ['91', '92', '93', '94'];

        if (simpleCodes.includes(code)) {
            $('#editSimpleTypeInfo').slideDown();
        } else {
            $('#editSimpleTypeInfo').slideUp();
        }
    });

    // Manejar envío del formulario de edición
    $('#editResolutionForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $('#updateResolutionBtn');
        const originalText = submitBtn.html();
        const resolutionId = $('#edit_resolution_id').val();

        // Deshabilitar botón y mostrar estado de carga
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        // Limpiar errores previos
        clearEditFormErrors();

        // Preparar datos del formulario
        const formData = $(this).serialize();
        const company = '{{ $company->identification_number }}';

        // Realizar petición AJAX
        $.ajax({
            url: `/companies/${company}/configuration/resolutions/${resolutionId}`,
            method: 'PUT',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Mostrar notificación de éxito
                    new PNotify({
                        text: response.message,
                        type: 'success',
                        addclass: 'notification-success',
                        delay: 3000
                    });

                    // Cerrar modal
                    $('#editResolutionModal').modal('hide');

                    // Recargar página para mostrar los cambios
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    // Mostrar notificación de error
                    new PNotify({
                        text: response.message || 'Error al actualizar la resolución',
                        type: 'error',
                        addclass: 'notification-danger',
                        delay: 5000
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Errores de validación
                    const errors = xhr.responseJSON.errors;
                    displayEditFormErrors(errors);

                    new PNotify({
                        text: 'Por favor corrige los errores en el formulario',
                        type: 'error',
                        addclass: 'notification-danger',
                        delay: 5000
                    });
                } else {
                    // Otros errores
                    const message = xhr.responseJSON?.message || 'Error interno del servidor';
                    new PNotify({
                        text: message,
                        type: 'error',
                        addclass: 'notification-danger',
                        delay: 5000
                    });
                }
            },
            complete: function() {
                // Restaurar botón
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Función para limpiar errores del formulario de edición
    function clearEditFormErrors() {
        $('#editResolutionModal .form-control').removeClass('is-invalid');
        $('#editResolutionModal .invalid-feedback').text('').hide();
    }

    // Función para mostrar errores de validación en formulario de edición
    function displayEditFormErrors(errors) {
        $.each(errors, function(field, messages) {
            const input = $(`#edit_${field}`);
            const feedback = input.siblings('.invalid-feedback');

            input.addClass('is-invalid');
            feedback.text(messages[0]).show();
        });
    }

    // Función para limpiar errores del formulario
    function clearFormErrors() {
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();
    }

    // Función para mostrar errores de validación
    function displayFormErrors(errors) {
        $.each(errors, function(field, messages) {
            const input = $(`#${field}`);
            const feedback = input.siblings('.invalid-feedback');

            input.addClass('is-invalid');
            feedback.text(messages[0]).show();
        });
    }
});
</script>
@endpush
@push('scripts')
<script>
$(document).ready(function() {
    @if(isset($prefill) && count($prefill))
        $('#newResolutionModal').modal('show');
    @endif
});
</script>
@endpush