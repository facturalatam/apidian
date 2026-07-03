@php
    $resolutionsHab = $resolutionsHab ?? collect();
    $resolutionsProd = $resolutionsProd ?? collect();
    $resolutionTypeDocuments = $resolutionTypeDocuments ?? collect();
    $companyEnvId = (int) ($company->type_environment_id ?? 2);
    $companyEnvLabel = $companyEnvId === 1 ? 'Producción' : 'Habilitación';
@endphp

<div class="p-3">
    <ul class="nav nav-pills mb-3" id="resEnvTabs_{{ $type }}" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="res-hab-tab-{{ $type }}" data-bs-toggle="tab"
                data-bs-target="#res-hab-{{ $type }}" type="button" role="tab"
                aria-controls="res-hab-{{ $type }}" aria-selected="true">
                <i class="fas fa-flask me-1"></i> Habilitación
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="res-prod-tab-{{ $type }}" data-bs-toggle="tab"
                data-bs-target="#res-prod-{{ $type }}" type="button" role="tab"
                aria-controls="res-prod-{{ $type }}" aria-selected="false">
                <i class="fas fa-rocket me-1"></i> Producción
            </button>
        </li>
    </ul>

    <div class="tab-content">
        @foreach (['hab' => ['env_id' => 2, 'data' => $resolutionsHab, 'label' => 'Habilitación'],
                   'prod' => ['env_id' => 1, 'data' => $resolutionsProd, 'label' => 'Producción']] as $key => $info)
            <div class="tab-pane fade {{ $key === 'hab' ? 'show active' : '' }}" id="res-{{ $key }}-{{ $type }}"
                role="tabpanel" aria-labelledby="res-{{ $key }}-tab-{{ $type }}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="m-0">Resoluciones en {{ $info['label'] }}</h5>
                    @if ($info['env_id'] === $companyEnvId)
                        <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#newResolutionModal_{{ $type }}">
                            <i class="fas fa-plus"></i> Nueva resolución
                        </button>
                    @else
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Solo se pueden crear en el ambiente actual de la empresa ({{ $companyEnvLabel }}).
                        </small>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Prefijo</th>
                                <th>Número</th>
                                <th>Tipo de Documento</th>
                                <th>Fecha</th>
                                <th>Rango</th>
                                <th>Vigencia</th>
                                <th>Clave Técnica</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($info['data'] as $resolution)
                                <tr>
                                    <td>{{ $resolution->prefix }}</td>
                                    <td>{{ $resolution->resolution }}</td>
                                    <td>{{ optional($resolution->type_document)->name }}</td>
                                    <td>{{ $resolution->resolution_date }}</td>
                                    <td>{{ $resolution->from }} - {{ $resolution->to }}</td>
                                    <td>{{ $resolution->date_from }} → {{ $resolution->date_to }}</td>
                                    <td>{{ $resolution->technical_key }}</td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-sm btn-primary edit-resolution-btn-{{ $type }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editResolutionModal_{{ $type }}"
                                            data-id="{{ $resolution->id }}"
                                            data-type-document-id="{{ $resolution->type_document_id }}"
                                            data-prefix="{{ $resolution->prefix }}"
                                            data-resolution="{{ $resolution->resolution }}"
                                            data-resolution-date="{{ $resolution->resolution_date }}"
                                            data-technical-key="{{ $resolution->technical_key }}"
                                            data-from="{{ $resolution->from }}"
                                            data-to="{{ $resolution->to }}"
                                            data-date-from="{{ $resolution->date_from }}"
                                            data-date-to="{{ $resolution->date_to }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        No hay resoluciones en {{ $info['label'] }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Modal: Nueva Resolución --}}
<div class="modal fade" id="newResolutionModal_{{ $type }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Resolución ({{ $companyEnvLabel }})</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="newResolutionForm_{{ $type }}"
                  action="{{ route('company.resolutions.store', ['company' => $company->identification_number]) }}"
                  method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Documento</label>
                                <select class="form-control" name="type_document_id">
                                    <option value="">Seleccionar tipo de documento</option>
                                    @foreach ($resolutionTypeDocuments as $td)
                                        <option value="{{ $td->id }}">{{ $td->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prefijo</label>
                                <input type="text" class="form-control" name="prefix" maxlength="10">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Número de Resolución</label>
                                <input type="text" class="form-control" name="resolution">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Resolución</label>
                                <input type="date" class="form-control" name="resolution_date">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Clave Técnica @if($type === 'support')<small class="text-muted">— no aplica para Documento Soporte</small>@endif</label>
                                <input type="text" class="form-control" name="technical_key"
                                    @if($type === 'support') readonly placeholder="No aplica para Documento Soporte" style="background:#f8f9fa;" @endif>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rango Inicial</label>
                                <input type="number" class="form-control" name="from" min="1">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rango Final</label>
                                <input type="number" class="form-control" name="to" min="1">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vigencia Desde</label>
                                <input type="date" class="form-control" name="date_from">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vigencia Hasta</label>
                                <input type="date" class="form-control" name="date_to">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary saveResolutionBtn_{{ $type }}">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Editar Resolución --}}
<div class="modal fade" id="editResolutionModal_{{ $type }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Resolución</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="editResolutionForm_{{ $type }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="resolution_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Documento</label>
                                <select class="form-control" name="type_document_id">
                                    <option value="">Seleccionar tipo de documento</option>
                                    @foreach ($resolutionTypeDocuments as $td)
                                        <option value="{{ $td->id }}">{{ $td->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prefijo</label>
                                <input type="text" class="form-control" name="prefix" readonly style="background:#f8f9fa;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Número de Resolución</label>
                                <input type="text" class="form-control" name="resolution">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Resolución</label>
                                <input type="date" class="form-control" name="resolution_date">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Clave Técnica @if($type === 'support')<small class="text-muted">— no aplica para Documento Soporte</small>@endif</label>
                                <input type="text" class="form-control" name="technical_key"
                                    @if($type === 'support') readonly placeholder="No aplica para Documento Soporte" style="background:#f8f9fa;" @endif>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rango Inicial</label>
                                <input type="number" class="form-control" name="from" min="1">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rango Final</label>
                                <input type="number" class="form-control" name="to" min="1">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vigencia Desde</label>
                                <input type="date" class="form-control" name="date_from">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vigencia Hasta</label>
                                <input type="date" class="form-control" name="date_to">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary updateResolutionBtn_{{ $type }}">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const TYPE = @json($type);
    const COMPANY = @json($company->identification_number);
    const STORE_URL = "{{ route('company.resolutions.store', ['company' => $company->identification_number]) }}";

    const newModalEl = document.getElementById('newResolutionModal_' + TYPE);
    const editModalEl = document.getElementById('editResolutionModal_' + TYPE);

    const hideModal = (el) => {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).hide();
        } else if (window.jQuery && jQuery(el).modal) {
            jQuery(el).modal('hide');
        }
    };

    function clearErrors($form) {
        $form.find('.form-control').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('').hide();
    }

    function displayErrors($form, errors) {
        $.each(errors, function (field, messages) {
            const $input = $form.find('[name="' + field + '"]');
            $input.addClass('is-invalid');
            $input.siblings('.invalid-feedback').text(messages[0]).show();
        });
    }

    function notify(text, type) {
        if (typeof PNotify !== 'undefined') {
            new PNotify({ text: text, type: type || 'info', delay: 3000 });
        } else {
            alert(text);
        }
    }

    // Reset form al abrir
    $(newModalEl).on('show.bs.modal', function () {
        const form = document.querySelector('.newResolutionForm_' + TYPE);
        if (form) { form.reset(); clearErrors($(form)); }
    });

    // Submit crear
    $(document).on('submit', '.newResolutionForm_' + TYPE, function (e) {
        e.preventDefault();
        const $newForm = $(this);
        clearErrors($newForm);
        const $btn = $newForm.find('.saveResolutionBtn_' + TYPE);
        const original = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: STORE_URL,
            method: 'POST',
            data: $newForm.serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    notify(response.message, 'success');
                    hideModal(newModalEl);
                    setTimeout(() => location.reload(), 700);
                } else {
                    notify(response.message || 'Error al crear la resolución', 'error');
                }
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    displayErrors($newForm, xhr.responseJSON.errors);
                    notify('Corrige los errores del formulario', 'error');
                } else {
                    notify((xhr.responseJSON && xhr.responseJSON.message) || 'Error del servidor', 'error');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html(original);
            }
        });
    });

    // Abrir modal editar — cargar datos
    $(document).on('click', '.edit-resolution-btn-' + TYPE, function () {
        const $b = $(this);
        const $editForm = $('.editResolutionForm_' + TYPE);
        clearErrors($editForm);
        $editForm.find('[name="resolution_id"]').val($b.data('id'));
        $editForm.find('[name="type_document_id"]').val($b.data('type-document-id'));
        $editForm.find('[name="prefix"]').val($b.data('prefix'));
        $editForm.find('[name="resolution"]').val($b.data('resolution'));
        $editForm.find('[name="resolution_date"]').val($b.data('resolution-date'));
        $editForm.find('[name="technical_key"]').val($b.data('technical-key'));
        $editForm.find('[name="from"]').val($b.data('from'));
        $editForm.find('[name="to"]').val($b.data('to'));
        $editForm.find('[name="date_from"]').val($b.data('date-from'));
        $editForm.find('[name="date_to"]').val($b.data('date-to'));
    });

    // Submit editar
    $(document).on('submit', '.editResolutionForm_' + TYPE, function (e) {
        e.preventDefault();
        const $editForm = $(this);
        clearErrors($editForm);
        const $btn = $editForm.find('.updateResolutionBtn_' + TYPE);
        const original = $btn.html();
        const resolutionId = $editForm.find('[name="resolution_id"]').val();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        $.ajax({
            url: '/companies/' + COMPANY + '/configuration/resolutions/' + resolutionId,
            method: 'PUT',
            data: $editForm.serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    notify(response.message, 'success');
                    hideModal(editModalEl);
                    setTimeout(() => location.reload(), 700);
                } else {
                    notify(response.message || 'Error al actualizar la resolución', 'error');
                }
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    displayErrors($editForm, xhr.responseJSON.errors);
                    notify('Corrige los errores del formulario', 'error');
                } else {
                    notify((xhr.responseJSON && xhr.responseJSON.message) || 'Error del servidor', 'error');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html(original);
            }
        });
    });
})();
</script>
@endpush
